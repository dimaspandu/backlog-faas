package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"

	"github.com/gorilla/mux"

	_ "github.com/go-sql-driver/mysql"
)

// Sprint represents public sprint data for customers.
type Sprint struct {
	Token       string `json:"token"`
	Name        string `json:"name"`
	Description string `json:"description"`
	IsOpen      int8   `json:"isOpen"`
}

func main() {
	db := mustConnectDB()

	r := mux.NewRouter()

	// Public endpoint: list visible sprints
	r.HandleFunc("/sprints", getSprintsHandler(db)).Methods("GET")

	log.Println("customer-transaction service listening on :8889")
	log.Fatal(http.ListenAndServe(":8889", r))
}

// getEnv returns the value of the environment variable or the fallback.
func getEnv(key, fallback string) string {
	if value, ok := os.LookupEnv(key); ok {
		return value
	}
	return fallback
}

// mustConnectDB opens and verifies the MySQL connection using environment variables.
func mustConnectDB() *sql.DB {
	host := getEnv("DB_HOST", "127.0.0.1")
	port := getEnv("DB_PORT", "3306")
	user := getEnv("DB_USER", "root")
	pass := getEnv("DB_PASSWORD", "")
	name := getEnv("DB_NAME", "backlog_faas")

	dsn := fmt.Sprintf("%s:%s@(%s:%s)/%s?parseTime=true", user, pass, host, port, name)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Fatalf("failed to open database: %v", err)
	}

	if err := db.Ping(); err != nil {
		log.Fatalf("failed to ping database: %v", err)
	}

	return db
}

// getSprintsHandler returns an HTTP handler that lists visible sprints.
func getSprintsHandler(db *sql.DB) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		sprints, err := fetchVisibleSprints(db)
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
}

// fetchVisibleSprints queries the database for all visible sprints.
// Uses db.Query (not QueryRow) so it correctly returns multiple rows.
func fetchVisibleSprints(db *sql.DB) ([]Sprint, error) {
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

	var sprints []Sprint

	for rows.Next() {
		var s Sprint
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
