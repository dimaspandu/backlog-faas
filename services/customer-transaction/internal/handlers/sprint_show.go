package handlers

import (
	"bcsaas-customer-transaction-service/internal/model"
	"bcsaas-customer-transaction-service/internal/utils"
	"encoding/json"
	"net/http"
)

func (h *Handler) SprintShow(
	w http.ResponseWriter,
	r *http.Request,
) {

	sprintMeta, ok := sprintMetaFromRequest(r)
	if !ok {
		utils.ResourceNotFoundResponse(w, nil, "Sprint token disappeared")
		return
	}

	rows, err := h.DB.QueryContext(r.Context(), `
		SELECT
			p.id,
			p.sku,
			p.product_slug,
			p.is_available,
			p.name,
			p.description,
			p.image_urls,
			p.selling_price_cents,
			spo.offer_price_cents
		FROM sprint_product_offerings spo
		JOIN products p ON spo.product_id = p.id
		WHERE spo.sprint_token = ?
		ORDER BY spo.offer_price_cents ASC
		LIMIT 100
	`, sprintMeta.Token)
	if err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	defer rows.Close()

	sprintProductOfferings := make(map[string][]*model.SprintProductOffering)
	for rows.Next() {
		var spo model.SprintProductOffering
		var slug string
		if err := rows.Scan(
			&spo.ID,
			&spo.SKU,
			&slug,
			&spo.IsAvailable,
			&spo.Name,
			&spo.Description,
			&spo.ImageUrls,
			&spo.SellingPriceCents,
			&spo.OfferPriceCents,
		); err != nil {
			utils.InternalServerErrorResponse(w, err)
			return
		}
		// initialize group if not exists
		if _, exists := sprintProductOfferings[slug]; !exists {
			sprintProductOfferings[slug] = []*model.SprintProductOffering{}
		}
		// append to group
		sprintProductOfferings[slug] = append(sprintProductOfferings[slug], &spo)
	}

	if err := rows.Err(); err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	if len(sprintProductOfferings) == 0 {
		utils.ResourceNotFoundResponse(w, nil, "No products found for this sprint")
		return
	}

	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(model.SuccessResponse{Data: model.SprintShow{
		Sprint:   *sprintMeta,
		Products: sprintProductOfferings,
	}})
}
