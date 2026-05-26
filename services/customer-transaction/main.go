package main

import (
	"log"
	"net/http"

	"backlog-faas/services/customer-transaction/config"
	"backlog-faas/services/customer-transaction/db"
	"backlog-faas/services/customer-transaction/handler"

	"github.com/gorilla/mux"
)

func main() {
	cfg := config.Load()

	database := db.Connect(cfg)

	h := handler.NewSprintHandler(database)

	r := mux.NewRouter()
	h.RegisterRoutes(r)

	addr := ":" + cfg.ServerPort
	log.Printf("customer-transaction service listening on %s", addr)
	log.Fatal(http.ListenAndServe(addr, r))
}
