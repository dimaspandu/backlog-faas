package main

import (
	"log"
	"net/http"
	"time"

	"bcsaas-customer-transaction-service/internal/config"
	"bcsaas-customer-transaction-service/internal/db"
	"bcsaas-customer-transaction-service/internal/domain/product"
	"bcsaas-customer-transaction-service/internal/domain/sprint"

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

	productRepo := product.NewRepository(db)
	productUsecase := product.NewUsecase(productRepo)
	productHandler := product.NewHandler(productUsecase)

	sprintRepo := sprint.NewRepository(db)
	sprintUsecase := sprint.NewUsecase(sprintRepo)
	sprintHandler := sprint.NewHandler(sprintUsecase)
	sprintMiddleware := sprint.NewSprintTokenValidation(sprintRepo)

	r := mux.NewRouter()
	r.HandleFunc("/products", productHandler.List).Methods(http.MethodGet)
	r.HandleFunc("/products/{id}/active-sprints", productHandler.ActiveSprintList).Methods(http.MethodGet)
	r.HandleFunc("/sprints", sprintHandler.List).Methods(http.MethodGet)
	r.HandleFunc("/sprints/{token}", sprintMiddleware.Validate(sprintHandler.Show)).Methods(http.MethodGet)
	r.HandleFunc("/sprints/{token}/contracts", sprintMiddleware.Validate(sprintHandler.CreateContract)).Methods(http.MethodPost)
	r.HandleFunc("/sprints/{token}/contracts", sprintMiddleware.Validate(sprintHandler.ContractList)).Methods(http.MethodGet)
	r.HandleFunc("/sprints/{token}/contracts/{contractNumber}", sprintMiddleware.Validate(sprintHandler.ContractShow)).Methods(http.MethodGet)

	addr := ":" + env.ServerPort
	log.Println("server running at " + addr)

	handlerWithCORS := corsHandlers.CORS(
		corsHandlers.AllowedOrigins(env.CORSAllowedOrigins),
		corsHandlers.AllowedMethods([]string{http.MethodGet, http.MethodPost, http.MethodPut, http.MethodDelete}),
		corsHandlers.AllowedHeaders([]string{"Content-Type", "Authorization"}),
	)

	srv := &http.Server{
		Addr:         addr,
		Handler:      handlerWithCORS(r),
		ReadTimeout:  5 * time.Second,
		WriteTimeout: 10 * time.Second,
		IdleTimeout:  60 * time.Second,
	}

	log.Fatal(srv.ListenAndServe())
}