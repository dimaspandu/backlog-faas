package db

import (
	"database/sql"
	"fmt"
	"log"

	"backlog-faas/services/customer-transaction/config"

	_ "github.com/go-sql-driver/mysql"
)

func Connect(cfg config.Config) *sql.DB {
	dsn := fmt.Sprintf("%s:%s@(%s:%s)/%s?parseTime=true",
		cfg.DBUser, cfg.DBPassword, cfg.DBHost, cfg.DBPort, cfg.DBName)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Fatalf("failed to open database: %v", err)
	}

	if err := db.Ping(); err != nil {
		log.Fatalf("failed to ping database: %v", err)
	}

	return db
}
