package handler

import (
	"backlog-faas/services/customer-transaction/db"
	"backlog-faas/services/customer-transaction/model"
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
	"strconv"

	"github.com/gorilla/mux"
)

type SprintHandler struct {
	DB *sql.DB
}

func NewSprintHandler(db *sql.DB) *SprintHandler {
	return &SprintHandler{DB: db}
}

func (h *SprintHandler) RegisterRoutes(r *mux.Router) {
	r.HandleFunc("/sprints", h.ListVisibleSprints).Methods("GET")
	r.HandleFunc("/sprints/{token}", h.GetSprintDetail).Methods("GET")
}

	type PaginatedSprintsResponse struct {
		Data       []model.Sprint `json:"data"`
		Pagination Pagination     `json:"pagination"`
	}

type Pagination struct {
	Page       int `json:"page"`
	PerPage    int `json:"per_page"`
	Total      int `json:"total"`
	TotalPages int `json:"total_pages"`
}

func (h *SprintHandler) ListVisibleSprints(w http.ResponseWriter, r *http.Request) {
	page := parsePositiveInt(r.URL.Query().Get("page"), 1)
	perPage := parsePositiveInt(r.URL.Query().Get("per_page"), 20)

	if perPage > 100 {
		perPage = 100
	}

	sprints, total, err := db.FetchVisibleSprints(h.DB, page, perPage)
	if err != nil {
		log.Printf("error fetching sprints: %v", err)
		http.Error(w, "Internal Server Error", http.StatusInternalServerError)
		return
	}

	totalPages := (total + perPage - 1) / perPage

	response := PaginatedSprintsResponse{
		Data: sprints,
		Pagination: Pagination{
			Page:       page,
			PerPage:    perPage,
			Total:      total,
			TotalPages: totalPages,
		},
	}

	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(response); err != nil {
		log.Printf("error encoding response: %v", err)
	}
}

func (h *SprintHandler) GetSprintDetail(w http.ResponseWriter, r *http.Request) {
	token := mux.Vars(r)["token"]

	sprint, err := db.GetOpenSprintByToken(h.DB, token)
	if err != nil {
		http.Error(w, "Internal Server Error", http.StatusInternalServerError)
		return
	}

	if sprint == nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusNotFound)
		json.NewEncoder(w).Encode(map[string]string{"error": "sprint_not_available"})
		return
	}

	products, err := db.GetSprintProductsByToken(h.DB, token)
	if err != nil {
		http.Error(w, "Internal Server Error", http.StatusInternalServerError)
		return
	}

	response := model.SprintDetailResponse{
		Sprint:   *sprint,
		Products: products,
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(response)
}

	func parsePositiveInt(value string, defaultValue int) int {
	if value == "" {
		return defaultValue
	}
	n, err := strconv.Atoi(value)
	if err != nil || n < 1 {
		return defaultValue
	}
	return n
}
