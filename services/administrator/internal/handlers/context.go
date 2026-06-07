package handlers

import (
	"bcsaas-administrator-service/internal/model"
	"context"
	"database/sql"
)

type Handler struct {
	DB *sql.DB
}

func New(db *sql.DB) *Handler {
	return &Handler{DB: db}
}

type adminSessionContextKey struct{}

func AdminSessionFromContext(
	ctx context.Context,
) (*model.AdminSession, bool) {

	adminSession, ok := ctx.Value(
		adminSessionContextKey{},
	).(*model.AdminSession)

	return adminSession, ok
}
