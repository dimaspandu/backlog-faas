package handlers

import (
	"bcsaas-customer-transaction-service/internal/model"
	"bcsaas-customer-transaction-service/internal/utils"
	"encoding/json"
	"math"
	"net/http"
)

func (h *Handler) SprintContractList(
	w http.ResponseWriter,
	r *http.Request,
) {

	sprintMeta, ok := sprintMetaFromRequest(r)

	if !ok {
		utils.ResourceNotFoundResponse(
			w,
			nil,
			"Sprint token disappeared",
		)
		return
	}

	total := 0
	if err := h.DB.QueryRowContext(r.Context(), `
		SELECT COUNT(*)
		FROM sprint_contracts
		WHERE sprint_token = ?
	`, sprintMeta.Token).Scan(&total); err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	if total == 0 {
		utils.ResourceNotFoundResponse(w, nil, "No sprint contracts found")
		return
	}

	page, perPage := utils.ParsePagination(r)
	rows, err := h.DB.QueryContext(r.Context(), `
		SELECT
			sc.contract_number,
			sc.customer_name,
			sc.customer_contact,
			sc.notes,
			(
				SELECT COUNT(*)
				FROM sprint_contract_orders sco
				WHERE sco.sprint_contract_id = sc.id
			) AS total_items,
			sc.total_price_cents,
			sc.request_status,
			sc.payment_status,
			sc.created_at
		FROM sprint_contracts sc
		WHERE
			sc.sprint_token = ?
		ORDER BY
			sc.created_at DESC
		LIMIT ?
		OFFSET ?
	`, sprintMeta.Token, perPage, (page-1)*perPage)
	if err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	defer rows.Close()

	var sprintContracts []model.SprintContract
	for rows.Next() {
		var sc model.SprintContract
		if err := rows.Scan(
			&sc.ContractNumber,
			&sc.CustomerName,
			&sc.CustomerContact,
			&sc.Notes,
			&sc.TotalItems,
			&sc.TotalPriceCents,
			&sc.RequestStatus,
			&sc.PaymentStatus,
			&sc.CreatedAt,
		); err != nil {
			utils.InternalServerErrorResponse(w, err)
			return
		}
		sc.CustomerContact = utils.MaskContact(sc.CustomerContact)
		sprintContracts = append(sprintContracts, sc)
	}

	if err := rows.Err(); err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}

	totalPages := int(math.Ceil(
		float64(total) / float64(perPage),
	))

	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(model.SuccessResponseWithMeta{
		Data: sprintContracts,
		Meta: &model.MetaResponse{
			Page:       page,
			PerPage:    perPage,
			Total:      total,
			TotalPages: totalPages,
		},
	})
}
