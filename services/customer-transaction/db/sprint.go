package db

import (
	"database/sql"

	"backlog-faas/services/customer-transaction/model"
)

func FetchVisibleSprints(db *sql.DB, page, perPage int) ([]model.Sprint, int, error) {
	if perPage > 100 {
		perPage = 100
	}
	if page < 1 {
		page = 1
	}

	offset := (page - 1) * perPage

	var total int
	countQuery := `SELECT COUNT(*) FROM sprints WHERE is_visible = 1`
	if err := db.QueryRow(countQuery).Scan(&total); err != nil {
		return nil, 0, err
	}

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

func GetOpenSprintByToken(db *sql.DB, token string) (*model.SprintPublic, error) {
	var s model.SprintPublic

	query := `
	  SELECT name, description
		FROM sprints
		WHERE token = ? AND is_open = 1 AND is_visible = 1
		LIMIT 1
	`

	err := db.QueryRow(query, token).Scan(&s.Name, &s.Description)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	return &s, nil
}

func GetSprintProductsByToken(db *sql.DB, token string) ([]model.SprintProduct, error) {
	query := `
		SELECT 
			sp.id,
			p.name as product_name,
			p.description as product_description,
			pv.attributes as variant_attributes,
			sp.price_cents,
			sp.list_price_cents
		FROM sprint_products sp
		JOIN sprints s ON s.id = sp.sprint_id
		LEFT JOIN products p ON p.id = sp.product_id
		LEFT JOIN product_variants pv ON pv.id = sp.product_variant_id
		WHERE s.token = ? 
		  AND s.is_open = 1 
		  AND sp.status = 'ACTIVE'
			AND pv.status = 'ACTIVE'
		ORDER BY sp.created_at ASC
	`

	rows, err := db.Query(query, token)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var products []model.SprintProduct
	for rows.Next() {
		var p model.SprintProduct
		if err := rows.Scan(
			&p.ID,
			&p.ProductName,
			&p.ProductDescription,
			&p.VariantAttributes,
			&p.PriceCents,
			&p.ListPriceCents,
		); err != nil {
			return nil, err
		}
		products = append(products, p)
	}

	return products, nil
}
