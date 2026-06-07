package handlers

import (
	"bcsaas-customer-transaction-service/internal/model"
	"bcsaas-customer-transaction-service/internal/utils"
	"database/sql"
	"encoding/json"
	"net/http"

	"github.com/gorilla/mux"
)

func (h *Handler) SprintContractShow(
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

	vars := mux.Vars(r)
	contractNumber := vars["contractNumber"]

	var sprintContract model.SprintContract
	if err := h.DB.QueryRowContext(r.Context(), `
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
			sc.contract_number = ?
			AND sc.sprint_token = ?
		LIMIT 1
	`, contractNumber, sprintMeta.Token).Scan(
		&sprintContract.ContractNumber,
		&sprintContract.CustomerName,
		&sprintContract.CustomerContact,
		&sprintContract.Notes,
		&sprintContract.TotalItems,
		&sprintContract.TotalPriceCents,
		&sprintContract.RequestStatus,
		&sprintContract.PaymentStatus,
		&sprintContract.CreatedAt,
	); err != nil {
		if err == sql.ErrNoRows {
			utils.ResourceNotFoundResponse(w, err, "No sprint contract found")
			return
		}
		utils.InternalServerErrorResponse(w, err)
		return
	}

	rows, err := h.DB.QueryContext(r.Context(), `
		SELECT
			p.sku,
			p.name,
			p.selling_price_cents,
			spo.offer_price_cents,
			sco.sugar_level
		FROM sprint_contract_orders sco
		JOIN sprint_contracts sc
			ON sco.sprint_contract_id = sc.id
		JOIN sprint_product_offerings spo
				ON spo.product_id = sco.product_id
			AND spo.sprint_token = sc.sprint_token
		JOIN products p
			ON p.id = sco.product_id
		WHERE
			sc.contract_number = ?
			AND sc.sprint_token = ?
		ORDER BY sco.id ASC
	`, contractNumber, sprintMeta.Token)
	if err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	defer rows.Close()

	var sprintContractItems []model.SprintContractItem
	for rows.Next() {
		var item model.SprintContractItem
		if err := rows.Scan(
			&item.ProductSku,
			&item.ProductName,
			&item.SellingPriceCents,
			&item.OfferPriceCents,
			&item.SugarLevel,
		); err != nil {
			utils.InternalServerErrorResponse(w, err)
			return
		}
		sprintContractItems = append(sprintContractItems, item)
	}

	if err := rows.Err(); err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}

	sprintContract.CustomerContact = utils.MaskContact(sprintContract.CustomerContact)

	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(model.SuccessResponse{
		Data: &model.SprintContractShow{
			Contract: sprintContract,
			Items:    sprintContractItems,
		},
	})
}
