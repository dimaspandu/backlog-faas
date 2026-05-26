package db

import (
	"database/sql"

	"backlog-faas/services/customer-transaction/model"
)

// FetchVisibleSprints mengambil daftar sprint yang visible dengan pagination.
// Hard limit maksimal 100 row untuk mencegah abuse.
func FetchVisibleSprints(db *sql.DB, page, perPage int) ([]model.Sprint, int, error) {
	if perPage > 100 {
		perPage = 100
	}
	if page < 1 {
		page = 1
	}

	offset := (page - 1) * perPage

	// Hitung total data
	var total int
	countQuery := `SELECT COUNT(*) FROM sprints WHERE is_visible = 1`
	if err := db.QueryRow(countQuery).Scan(&total); err != nil {
		return nil, 0, err
	}

	// Ambil data dengan pagination
	query := `
		SELECT token, name, description, is_open 
		FROM sprints 
		WHERE is_visible = 1 
		ORDER BY created_at DESC
		LIMIT ? OFFSET ?
	`

	rows, err := db.Query(query, perPage, offset)
	if err != nil {
		return nil, 0, err
	}
	defer rows.Close()

	var sprints []model.Sprint
	for rows.Next() {
		var s model.Sprint
		if err := rows.Scan(&s.Token, &s.Name, &s.Description, &s.IsOpen); err != nil {
			return nil, 0, err
		}
		sprints = append(sprints, s)
	}

	if err := rows.Err(); err != nil {
		return nil, 0, err
	}

	return sprints, total, nil
}
