package handlers

import (
	"bcsaas-backoffice-service/internal/model"
	"bcsaas-backoffice-service/internal/utils"
	"context"
	"database/sql"
	"net/http"
	"strings"
)

func (h *Handler) SessionValidation(f http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {

		w.Header().Set("Content-Type", "application/json")

		authHeader := r.Header.Get("Authorization")

		if authHeader == "" {
			utils.UnauthorizedResponse(w, nil, "Missing authorization header")
			return
		}

		if !strings.HasPrefix(authHeader, "Bearer ") {
			utils.UnauthorizedResponse(w, nil, "Invalid authorization header")
			return
		}

		token := strings.TrimPrefix(authHeader, "Bearer ")

		adminSession := &model.AdminSession{
			Token: token,
		}

		if err := h.DB.QueryRowContext(r.Context(), `
			SELECT
				admin_id,
				username,
				role,
				expires_at
			FROM admin_sessions
			WHERE token = ?
			AND expires_at > NOW()
			LIMIT 1
		`, token).Scan(
			&adminSession.ID,
			&adminSession.Username,
			&adminSession.Role,
			&adminSession.ExpiresAt,
		); err != nil {
			if err == sql.ErrNoRows {
				utils.UnauthorizedResponse(w, err, "Invalid or expired session")
				return
			}
			utils.InternalServerErrorResponse(w, err)
			return
		}

		if adminSession.Role != "backoffice" {
			utils.UnauthorizedResponse(w, nil)
			return
		}

		ctx := context.WithValue(
			r.Context(),
			adminSessionContextKey{},
			adminSession,
		)

		f(w, r.WithContext(ctx))
	}
}
