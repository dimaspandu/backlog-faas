package handler

import (
	"database/sql"
	"encoding/json"
	"log"
	"net/http"

	"backlog-faas/services/customer-transaction/model"

	"github.com/gorilla/mux"
)

// SprintHandler holds dependencies for sprint-related handlers.
type SprintHandler struct {
	DB *sql.DB
}

// NewSprintHandler creates a new SprintHandler.
func NewSprintHandler(db *sql.DB) *SprintHandler {
	return &SprintHandler{DB: db}
}

// RegisterRoutes registers all sprint routes.
func (h *SprintHandler) RegisterRoutes(r *mux.Router) {
	r.HandleFunc("/sprints", h.ListVisibleSprints).Methods("GET")
}

// ListVisibleSprints returns all visible sprints.
func (h *SprintHandler) ListVisibleSprints(w http.ResponseWriter, r *http.Request) {
	sprints, err := fetchVisibleSprints(h.DB)
	if err != nil {
		log.Printf("error fetching sprints: %v", err)
		http.Error(w, "Internal Server Error", http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	if err := json.NewEncoder(w).Encode(sprints); err != nil {
		log.Printf("error encoding response: %v", err)
	}
}

// fetchVisibleSprints queries visible sprints from the database.
func fetchVisibleSprints(db *sql.DB) ([]model.Sprint, error) {
	query := `
		SELECT token, name, description, is_open 
		FROM sprints 
		WHERE is_visible = 1 
		ORDER BY created_at DESC
	`

	rows, err := db.Query(query)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var sprints []model.Sprint

	for rows.Next() {
		var s model.Sprint
		if err := rows.Scan(&s.Token, &s.Name, &s.Description, &s.IsOpen); err != nil {
			return nil, err
		}
		sprints = append(sprints, s)
	}

	if err := rows.Err(); err != nil {
		return nil, err
	}

	return sprints, nil
}
