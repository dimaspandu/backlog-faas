package main

import (
	"bcsaas-administrator-service/internal/config"
	"bcsaas-administrator-service/internal/db"
	"bcsaas-administrator-service/internal/handlers"
	"log"
	"net/http"
	"time"

	corsHandlers "github.com/gorilla/handlers"
	"github.com/gorilla/mux"
)

func main() {

	env := config.Load()

	db, err := db.NewMySQL(env)
	if err != nil {
		log.Fatal(err)
	}

	defer db.Close()

	h := handlers.New(db)

	r := mux.NewRouter()
	r.HandleFunc("/products", h.ProductList).Methods(http.MethodGet)
	r.HandleFunc("/sessions", h.CreateSession).Methods(http.MethodPost)
	r.HandleFunc("/sprints", h.SessionValidation(h.SprintList)).Methods(http.MethodGet)

	addr := ":" + env.ServerPort

	handlerWithCORS := corsHandlers.CORS(
		corsHandlers.AllowedOrigins(env.CORSAllowedOrigins),
		corsHandlers.AllowedMethods([]string{http.MethodGet, http.MethodPost, http.MethodPut, http.MethodDelete}),
		corsHandlers.AllowedHeaders([]string{"Content-Type", "Authorization"}),
	)

	log.Println("server running at " + addr)

	srv := &http.Server{
		Addr:         addr,
		Handler:      handlerWithCORS(r),
		ReadTimeout:  5 * time.Second,
		WriteTimeout: 10 * time.Second,
		IdleTimeout:  60 * time.Second,
	}

	log.Fatal(srv.ListenAndServe())
}
