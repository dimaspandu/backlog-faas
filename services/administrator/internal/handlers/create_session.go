package handlers

import (
	"bcsaas-administrator-service/internal/model"
	"bcsaas-administrator-service/internal/utils"
	"crypto/rand"
	"database/sql"
	"encoding/hex"
	"encoding/json"
	"net"
	"net/http"
	"time"

	"golang.org/x/crypto/bcrypt"
)

func (h *Handler) CreateSession(
	w http.ResponseWriter,
	r *http.Request,
) {

	w.Header().Set("Content-Type", "application/json")

	var req model.SessionRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.BadRequestResponse(
			w,
			err,
			"Invalid request body",
		)
		return
	}

	if req.Username == "" || req.Password == "" {
		utils.BadRequestResponse(
			w,
			nil,
			"Username and password are required",
		)
		return
	}

	var (
		admin        model.AdminSession
		passwordHash string
	)

	if err := h.DB.QueryRowContext(r.Context(), `
		SELECT
			id,
			username,
			role,
			password_hash
		FROM admins
		WHERE username = ?
		AND role = 'administrator'
		AND is_active = 1
		LIMIT 1
	`, req.Username).Scan(
		&admin.ID,
		&admin.Username,
		&admin.Role,
		&passwordHash,
	); err != nil {
		if err == sql.ErrNoRows {
			utils.ForbiddenResponse(
				w,
				err,
				"Invalid username or password",
			)
			return
		}

		utils.InternalServerErrorResponse(w, err)
		return
	}

	if err := bcrypt.CompareHashAndPassword(
		[]byte(passwordHash),
		[]byte(req.Password),
	); err != nil {
		utils.ForbiddenResponse(
			w,
			err,
			"Invalid username or password",
		)
		return
	}

	token, err := newSessionToken()
	if err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}

	expiresAt := time.Now().Add(24 * time.Hour)

	if _, err := h.DB.ExecContext(r.Context(), `
		INSERT INTO admin_sessions (
			token,
			admin_id,
			username,
			role,
			ip_address,
			user_agent,
			expires_at
		)
		VALUES (?, ?, ?, ?, ?, ?, ?)
	`,
		token,
		admin.ID,
		admin.Username,
		admin.Role,
		clientIP(r),
		r.UserAgent(),
		expiresAt,
	); err != nil {
		utils.InternalServerErrorResponse(
			w,
			err,
			"Failed to create session",
		)
		return
	}

	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(model.SuccessResponse{
		Data: &model.AdminSession{
			ID:        admin.ID,
			Username:  admin.Username,
			Role:      admin.Role,
			Token:     token,
			ExpiresAt: expiresAt,
		},
	})
}

func newSessionToken() (string, error) {
	b := make([]byte, 32)
	if _, err := rand.Read(b); err != nil {
		return "", err
	}

	return hex.EncodeToString(b), nil
}

func clientIP(r *http.Request) string {
	host, _, err := net.SplitHostPort(r.RemoteAddr)
	if err != nil {
		return r.RemoteAddr
	}

	return host
}
