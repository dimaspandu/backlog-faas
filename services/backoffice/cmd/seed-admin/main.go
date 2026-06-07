package main

import (
	"bcsaas-backoffice-service/internal/config"
	"bcsaas-backoffice-service/internal/db"
	"log"

	"golang.org/x/crypto/bcrypt"

	_ "github.com/go-sql-driver/mysql"
)

func main() {

	env := config.Load()

	db, err := db.NewMySQL(env)
	if err != nil {
		log.Fatal(err)
	}

	defer db.Close()

	var exists bool

	err = db.QueryRow(`
		SELECT EXISTS(
			SELECT 1
			FROM admins
			WHERE role = 'backoffice'
			LIMIT 1
		)
	`).Scan(&exists)

	if err != nil {
		log.Fatal(err)
	}

	if exists {
		log.Println("admins already seeded")
		return
	}

	passwordHash, err := bcrypt.GenerateFromPassword(
		[]byte("P@ssw0rd!"),
		bcrypt.DefaultCost,
	)
	if err != nil {
		log.Fatal(err)
	}

	tx, err := db.Begin()
	if err != nil {
		log.Fatal(err)
	}

	defer tx.Rollback()

	_, err = tx.Exec(`
		INSERT INTO admins (
			username,
			password_hash,
			role
		)
		VALUES (?, ?, ?)
	`,
		"cloud",
		string(passwordHash),
		"backoffice",
	)

	if err != nil {
		log.Fatal(err)
	}

	if err := tx.Commit(); err != nil {
		log.Fatal(err)
	}

	log.Println("default admins created")
	log.Println("username : cloud")
	log.Println("password : P@ssw0rd!")
}
