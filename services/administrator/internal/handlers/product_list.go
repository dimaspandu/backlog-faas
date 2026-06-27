package handlers

import (
	"bcsaas-administrator-service/internal/model"
	"bcsaas-administrator-service/internal/utils"
	"encoding/json"
	"math"
	"net/http"
)

func (h *Handler) ProductList(
	w http.ResponseWriter,
	r *http.Request,
) {

	w.Header().Set("Content-Type", "application/json")

	total := 0
	if err := h.DB.QueryRowContext(r.Context(), `
		SELECT COUNT(*)
		FROM products
		WHERE status = 'ACTIVE'
	`).Scan(&total); err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	if total == 0 {
		utils.ResourceNotFoundResponse(w, nil, "No products found")
		return
	}

	page, perPage := utils.ParsePagination(r)
	rows, err := h.DB.QueryContext(r.Context(), `
		SELECT
			id,
			sku,
			product_slug,
			name,
			description,
			category,
			image_urls,
			selling_price_cents
		FROM products
		WHERE
			is_available = 1
			AND
			status = 'ACTIVE'
		LIMIT ?
		OFFSET ?
	`, perPage, (page-1)*perPage)
	if err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	defer rows.Close()

	var products []model.Product
	for rows.Next() {
		var p model.Product
		if err := rows.Scan(
			&p.ID,
			&p.Sku,
			&p.Slug,
			&p.Name,
			&p.Description,
			&p.Category,
			&p.Images,
			&p.SellingPriceCents,
		); err != nil {
			utils.InternalServerErrorResponse(w, err)
			return
		}
		products = append(products, p)
	}

	if err := rows.Err(); err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}

	totalPages := int(math.Ceil(
		float64(total) / float64(perPage),
	))

	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(&model.SuccessResponseWithMeta{
		Data: products,
		Meta: &model.MetaResponse{
			Page:       page,
			PerPage:    perPage,
			Total:      total,
			TotalPages: totalPages,
		},
	})
}
