package handlers

import (
	"bcsaas-customer-transaction-service/internal/model"
	"bcsaas-customer-transaction-service/internal/utils"
	"context"
	"database/sql"
	"net/http"

	"github.com/gorilla/mux"
)

func (h *Handler) SprintTokenValidation(f http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {

		w.Header().Set("Content-Type", "application/json")

		vars := mux.Vars(r)

		sprintMeta := &model.SprintMeta{
			Token: vars["token"],
		}

		if err := h.DB.QueryRowContext(r.Context(), `
			SELECT
				name,
				description,
				end_at,
				is_open,
				status
			FROM sprints
			WHERE token = ?
			AND is_visible = 1
			AND
				status IN ('ACTIVE', 'CLOSED')
			LIMIT 1
		`, sprintMeta.Token).Scan(
			&sprintMeta.Name,
			&sprintMeta.Description,
			&sprintMeta.EndAt,
			&sprintMeta.IsOpen,
			&sprintMeta.Status,
		); err != nil {
			if err == sql.ErrNoRows {
				utils.ResourceNotFoundResponse(w, err, "Sprint not found or not open")
				return
			}
			utils.InternalServerErrorResponse(w, err)
			return
		}

		ctx := context.WithValue(
			r.Context(),
			sprintMetaContextKey{},
			sprintMeta,
		)

		f(w, r.WithContext(ctx))
	}
}
