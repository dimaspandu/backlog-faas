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

type Pagination struct {
	Page       int `json:"page"`
	PerPage    int `json:"per_page"`
	Total      int `json:"total"`
	TotalPages int `json:"total_pages"`
}

func NewSprintHandler(db *sql.DB) *SprintHandler {
	return &SprintHandler{DB: db}
}

func (h *SprintHandler) RegisterRoutes(r *mux.Router) {
	r.HandleFunc("/sprints", h.ListVisibleSprints).Methods("GET")
	r.HandleFunc("/sprints/{token}", h.GetSprintDetail).Methods("GET")
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
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]interface{}{
			"error": map[string]string{
				"code":    "INTERNAL_ERROR",
				"message": "Internal server error",
			},
		})
		return
	}

	totalPages := (total + perPage - 1) / perPage

	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(map[string]interface{}{
		"data": sprints,
		"meta": map[string]interface{}{
			"pagination": Pagination{
				Page:       page,
				PerPage:    perPage,
				Total:      total,
				TotalPages: totalPages,
			},
		},
	}); err != nil {
		log.Printf("error encoding response: %v", err)
	}
}

func (h *SprintHandler) GetSprintDetail(w http.ResponseWriter, r *http.Request) {
	token := mux.Vars(r)["token"]

	sprint, err := db.GetOpenSprintByToken(h.DB, token)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]interface{}{
			"error": map[string]string{
				"code":    "INTERNAL_ERROR",
				"message": "Internal server error",
			},
		})
		return
	}

	if sprint == nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusNotFound)
		json.NewEncoder(w).Encode(map[string]interface{}{
			"error": map[string]string{
				"code":    "SPRINT_NOT_AVAILABLE",
				"message": "Sprint is not open or does not exist",
			},
		})
		return
	}

	products, err := db.GetSprintProductsByToken(h.DB, token)
	if err != nil {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusInternalServerError)
		json.NewEncoder(w).Encode(map[string]interface{}{
			"error": map[string]string{
				"code":    "INTERNAL_ERROR",
				"message": "Internal server error",
			},
		})
		return
	}

	response := model.SprintDetailResponse{
		Data: model.SprintDetailData{
			Sprint:   *sprint,
			Products: products,
		},
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
