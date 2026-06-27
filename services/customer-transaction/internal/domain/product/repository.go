package product

import (
	"context"
	"database/sql"
)

type Repository struct {
	DB *sql.DB
}

func NewRepository(db *sql.DB) *Repository {
	return &Repository{DB: db}
}

func (r *Repository) CountList(ctx context.Context) (int, error) {
	var total int
	if err := r.DB.QueryRowContext(ctx, `
		SELECT COUNT(*)
		FROM products
		WHERE status = 'ACTIVE'
	`).Scan(&total); err != nil {
		return 0, err
	}
	return total, nil
}

func (r *Repository) List(ctx context.Context, limit, offset int) ([]Product, error) {
	rows, err := r.DB.QueryContext(ctx, `
		SELECT
			id,
			sku,
			product_slug as slug,
			name,
			description,
			category,
			image_urls as images,
			selling_price_cents as sellingPriceCents
		FROM products
		WHERE
			is_available = 1
			AND
			status = 'ACTIVE'
		ORDER BY
			id DESC
		LIMIT ?
		OFFSET ?
	`, limit, offset)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var products []Product
	for rows.Next() {
		var p Product
		if err := rows.Scan(
			&p.ID,
			&p.Sku,
			&p.Slug,
			&p.Name,
			&p.Description,
			&p.Category,
			&p.Images,
			&p.SellingPriceCents,
		); err != nil {
			return nil, err
		}
		products = append(products, p)
	}

	if err := rows.Err(); err != nil {
		return nil, err
	}
	return products, nil
}

func (r *Repository) ListActiveSprints(ctx context.Context, productID int) ([]Sprint, error) {
	rows, err := r.DB.QueryContext(ctx, `
		SELECT
			spo.sprint_token,
			s.name,
			s.description,
			COUNT(spo_all.product_id) AS total_products,
			s.is_open,
			s.status
		FROM sprint_product_offerings AS spo
		JOIN sprints AS s
			ON s.token = spo.sprint_token
		JOIN sprint_product_offerings AS spo_all
			ON spo_all.sprint_token = s.token
			AND spo_all.is_available = 1
		WHERE
			spo.product_id = ?
			AND spo.is_available = 1
			AND s.is_visible = 1
			AND s.is_open = 1
			AND s.status = 'ACTIVE'
			AND s.start_at <= NOW()
			AND (s.end_at IS NULL OR s.end_at >= NOW())
		GROUP BY
			s.id,
			spo.sprint_token,
			s.name,
			s.description,
			s.is_open,
			s.status
		ORDER BY
			s.id DESC
		LIMIT 3
	`, productID)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var sprints []Sprint
	for rows.Next() {
		var s Sprint
		if err := rows.Scan(
			&s.Token,
			&s.Name,
			&s.Description,
			&s.TotalProducts,
			&s.IsOpen,
			&s.Status,
		); err != nil {
			return nil, err
		}
		sprints = append(sprints, s)
	}

	if err := rows.Err(); err != nil {
		return nil, err
	}
	return sprints, nil
}