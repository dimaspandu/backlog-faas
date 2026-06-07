package handlers

import (
	"bcsaas-customer-transaction-service/internal/model"
	"database/sql"
	"net/http"
)

type Handler struct {
	DB *sql.DB
}

func New(db *sql.DB) *Handler {
	return &Handler{DB: db}
}

type sprintMetaContextKey struct{}

func sprintMetaFromRequest(r *http.Request) (*model.SprintMeta, bool) {
	sprintMeta, ok := r.Context().Value(sprintMetaContextKey{}).(*model.SprintMeta)
	return sprintMeta, ok
}
