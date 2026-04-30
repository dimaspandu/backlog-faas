package database

import (
	"database/sql"
	"log"

	_ "github.com/go-sql-driver/mysql"
)

func NewMySQL() *sql.DB {
	dsn := "root:@tcp(127.0.0.1:3306)/backlog_faas?parseTime=true"

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Fatal(err)
	}

	if err := db.Ping(); err != nil {
		log.Fatal(err)
	}

	return db
}