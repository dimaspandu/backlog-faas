package handlers

import (
	"encoding/json"
	"math"
	"net/http"

	"bcsaas-customer-transaction-service/internal/model"
	"bcsaas-customer-transaction-service/internal/utils"
)

func (h *Handler) SprintList(
	w http.ResponseWriter,
	r *http.Request,
) {

	w.Header().Set("Content-Type", "application/json")

	total := 0
	if err := h.DB.QueryRowContext(r.Context(), `
		SELECT COUNT(*)
		FROM sprints
		WHERE
			is_visible = 1
		AND status = 'ACTIVE'
		AND
			(status = 'ACTIVE' OR status = 'CLOSED')
	`).Scan(&total); err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	if total == 0 {
		utils.ResourceNotFoundResponse(w, nil, "No sprints found")
		return
	}

	page, perPage := utils.ParsePagination(r)
	rows, err := h.DB.QueryContext(r.Context(), `
		SELECT
			token,
			name,
			description,
			(
				SELECT COUNT(*)
				FROM sprint_product_offerings spo
				WHERE spo.sprint_token = token
			) AS total_products,
			is_open,
			status
		FROM sprints
		WHERE
			is_visible = 1
			AND
				(status = 'ACTIVE' OR status = 'CLOSED')
		ORDER BY
			created_at DESC
		LIMIT ?
		OFFSET ?
	`, perPage, (page-1)*perPage)
	if err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	defer rows.Close()

	var sprints []model.Sprint
	for rows.Next() {
		var s model.Sprint
		if err := rows.Scan(
			&s.Token,
			&s.Name,
			&s.Description,
			&s.TotalProducts,
			&s.IsOpen,
			&s.Status,
		); err != nil {
			utils.InternalServerErrorResponse(w, err)
			return
		}
		sprints = append(sprints, s)
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
		Data: sprints,
		Meta: &model.MetaResponse{
			Page:       page,
			PerPage:    perPage,
			Total:      total,
			TotalPages: totalPages,
		},
	})
}
