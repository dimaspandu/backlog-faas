package sprint

import (
	"context"
	"database/sql"
	"fmt"
	"math/rand"
	"strings"
	"time"
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
		FROM sprints
		WHERE
			is_visible = 1
				AND
			(status = 'ACTIVE' OR status = 'CLOSED')
	`).Scan(&total); err != nil {
		return 0, err
	}
	return total, nil
}

func (r *Repository) List(ctx context.Context, limit, offset int) ([]Sprint, error) {
	rows, err := r.DB.QueryContext(ctx, `
		SELECT
			token,
			name,
			description,
			(
				SELECT COUNT(*)
				FROM sprint_product_offerings spo
				WHERE spo.sprint_token = token
			) AS total_products,
			is_open,
			status
		FROM sprints
		WHERE
			is_visible = 1
				AND
			(status = 'ACTIVE' OR status = 'CLOSED')
		ORDER BY
			id DESC
		LIMIT ?
		OFFSET ?
	`, limit, offset)
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

func (r *Repository) FindByToken(ctx context.Context, token string) (*SprintMeta, error) {
	sprintMeta := &SprintMeta{Token: token}
	if err := r.DB.QueryRowContext(ctx, `
		SELECT
			name,
			description,
			end_at,
			is_open,
			status
		FROM sprints
		WHERE
			token = ?
				AND is_visible = 1
				AND status IN ('ACTIVE', 'CLOSED')
		LIMIT 1
	`, token).Scan(
		&sprintMeta.Name,
		&sprintMeta.Description,
		&sprintMeta.EndAt,
		&sprintMeta.IsOpen,
		&sprintMeta.Status,
	); err != nil {
		if err == sql.ErrNoRows {
			return nil, ErrSprintNotFound
		}
		return nil, err
	}
	return sprintMeta, nil
}

func (r *Repository) GetProducts(ctx context.Context, token string) (map[string][]*SprintProductOffering, error) {
	rows, err := r.DB.QueryContext(ctx, `
		SELECT
			p.id,
			p.sku,
			p.product_slug,
			spo.is_available,
			p.name,
			p.description,
			p.image_urls,
			p.selling_price_cents,
			spo.offer_price_cents
		FROM sprint_product_offerings spo
		JOIN products p ON spo.product_id = p.id
		WHERE spo.sprint_token = ?
		ORDER BY spo.offer_price_cents ASC
		LIMIT 100
	`, token)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	sprintProductOfferings := make(map[string][]*SprintProductOffering)
	for rows.Next() {
		var spo SprintProductOffering
		var slug string
		if err := rows.Scan(
			&spo.ID,
			&spo.SKU,
			&slug,
			&spo.IsAvailable,
			&spo.Name,
			&spo.Description,
			&spo.Images,
			&spo.SellingPriceCents,
			&spo.OfferPriceCents,
		); err != nil {
			return nil, err
		}
		if _, exists := sprintProductOfferings[slug]; !exists {
			sprintProductOfferings[slug] = []*SprintProductOffering{}
		}
		sprintProductOfferings[slug] = append(sprintProductOfferings[slug], &spo)
	}

	if err := rows.Err(); err != nil {
		return nil, err
	}
	return sprintProductOfferings, nil
}

func (r *Repository) CountContracts(ctx context.Context, token string) (int, error) {
	var total int
	if err := r.DB.QueryRowContext(ctx, `
		SELECT COUNT(*)
		FROM sprint_contracts
		WHERE sprint_token = ?
	`, token).Scan(&total); err != nil {
		return 0, err
	}
	return total, nil
}

func (r *Repository) ListContracts(ctx context.Context, token string, limit, offset int) ([]SprintContract, error) {
	rows, err := r.DB.QueryContext(ctx, `
		SELECT
			sc.contract_number,
			sc.customer_name,
			sc.customer_contact,
			sc.notes,
			(
				SELECT COUNT(*)
				FROM sprint_contract_orders sco
				WHERE sco.sprint_contract_id = sc.id
			) AS total_items,
			sc.total_price_cents,
			sc.request_status,
			sc.payment_status,
			sc.created_at
		FROM sprint_contracts sc
		WHERE
			sc.sprint_token = ?
		ORDER BY
			sc.created_at DESC
		LIMIT ?
		OFFSET ?
	`, token, limit, offset)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var contracts []SprintContract
	for rows.Next() {
		var c SprintContract
		if err := rows.Scan(
			&c.ContractNumber,
			&c.CustomerName,
			&c.CustomerContact,
			&c.Notes,
			&c.TotalItems,
			&c.TotalPriceCents,
			&c.RequestStatus,
			&c.PaymentStatus,
			&c.CreatedAt,
		); err != nil {
			return nil, err
		}
		contracts = append(contracts, c)
	}

	if err := rows.Err(); err != nil {
		return nil, err
	}
	return contracts, nil
}

func (r *Repository) FindContract(ctx context.Context, token, contractNumber string) (*SprintContract, error) {
	var c SprintContract
	if err := r.DB.QueryRowContext(ctx, `
		SELECT
			sc.contract_number,
			sc.customer_name,
			sc.customer_contact,
			sc.notes,
			(
				SELECT COUNT(*)
				FROM sprint_contract_orders sco
				WHERE sco.sprint_contract_id = sc.id
			) AS total_items,
			sc.total_price_cents,
			sc.request_status,
			sc.payment_status,
			sc.created_at
		FROM sprint_contracts sc
		WHERE
			sc.contract_number = ?
			AND sc.sprint_token = ?
		LIMIT 1
	`, contractNumber, token).Scan(
		&c.ContractNumber,
		&c.CustomerName,
		&c.CustomerContact,
		&c.Notes,
		&c.TotalItems,
		&c.TotalPriceCents,
		&c.RequestStatus,
		&c.PaymentStatus,
		&c.CreatedAt,
	); err != nil {
		if err == sql.ErrNoRows {
			return nil, ErrContractNotFound
		}
		return nil, err
	}
	return &c, nil
}

func (r *Repository) ListContractItems(ctx context.Context, contractNumber, token string) ([]SprintContractItem, error) {
	rows, err := r.DB.QueryContext(ctx, `
		SELECT
			p.sku,
			p.name,
			p.selling_price_cents,
			spo.offer_price_cents,
			sco.sugar_level
		FROM sprint_contract_orders sco
		JOIN sprint_contracts sc
			ON sco.sprint_contract_id = sc.id
		JOIN sprint_product_offerings spo
				ON spo.product_id = sco.product_id
			AND spo.sprint_token = sc.sprint_token
		JOIN products p
			ON p.id = sco.product_id
		WHERE
			sc.contract_number = ?
			AND sc.sprint_token = ?
		ORDER BY sco.id ASC
	`, contractNumber, token)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var items []SprintContractItem
	for rows.Next() {
		var item SprintContractItem
		if err := rows.Scan(
			&item.ProductSku,
			&item.ProductName,
			&item.SellingPriceCents,
			&item.OfferPriceCents,
			&item.SugarLevel,
		); err != nil {
			return nil, err
		}
		items = append(items, item)
	}

	if err := rows.Err(); err != nil {
		return nil, err
	}
	return items, nil
}

type CreateContractParams struct {
	SprintToken          string
	CustomerName         string
	CustomerContact      string
	Notes                string
	TotalPriceCents      int
	CustomerAuthProvider string
	CustomerExternalID   string
	CustomerEmail        string
	CustomerPhone        string
	Products             []SprintProductRequest
}

func (r *Repository) ValidateProducts(ctx context.Context, token string, productIDs map[int]struct{}) (int, map[int]int, error) {
	placeholders := make([]string, 0, len(productIDs))
	args := make([]any, 0, len(productIDs)+1)
	args = append(args, token)
	for id := range productIDs {
		placeholders = append(placeholders, "?")
		args = append(args, id)
	}

	query := fmt.Sprintf(`
		SELECT
			product_id,
			offer_price_cents
		FROM sprint_product_offerings
		WHERE
			sprint_token = ?
			AND
			product_id IN (%s)
	`, strings.Join(placeholders, ","))

	rows, err := r.DB.QueryContext(ctx, query, args...)
	if err != nil {
		return 0, nil, err
	}
	defer rows.Close()

	prices := make(map[int]int)
	totalProducts := 0
	for rows.Next() {
		var productID, offerPriceCents int
		if err := rows.Scan(&productID, &offerPriceCents); err != nil {
			return 0, nil, err
		}
		prices[productID] = offerPriceCents
		totalProducts++
	}
	if err := rows.Err(); err != nil {
		return 0, nil, err
	}
	return totalProducts, prices, nil
}

func (r *Repository) CreateContract(ctx context.Context, params *CreateContractParams) (string, error) {
	tx, err := r.DB.BeginTx(ctx, nil)
	if err != nil {
		return "", err
	}
	defer tx.Rollback()

	if _, err := tx.ExecContext(ctx, `
		INSERT INTO customers
			(
				name,
				email,
				phone,
				auth_provider,
				external_id
			)
		SELECT
			?, ?, ?, ?, ?
		WHERE NOT EXISTS (
			SELECT 1
			FROM customers
			WHERE
				email = ?
				AND
				phone = ?
		)
	`,
		params.CustomerName,
		params.CustomerEmail,
		params.CustomerPhone,
		params.CustomerAuthProvider,
		params.CustomerExternalID,
		params.CustomerEmail,
		params.CustomerPhone,
	); err != nil {
		return "", err
	}

	contractNumber := generateContractNumber()

	result, err := tx.ExecContext(ctx, `
		INSERT INTO sprint_contracts
			(
				contract_number,
				sprint_token,
				customer_name,
				customer_contact,
				notes,
				total_price_cents,
				request_status,
				payment_status
			)
		VALUES
			(?, ?, ?, ?, ?, ?, ?, ?)
	`,
		contractNumber,
		params.SprintToken,
		params.CustomerName,
		params.CustomerContact,
		params.Notes,
		params.TotalPriceCents,
		"PENDING",
		"UNPAID",
	)

	if err != nil {
		return "", err
	}

	sprintContractID, err := result.LastInsertId()
	if err != nil {
		return "", err
	}

	for _, product := range params.Products {
		sugarLevel := product.SugarLevel
		if sugarLevel == "" {
			sugarLevel = "NONE"
		}
		sugarLevel = strings.ToUpper(sugarLevel)

		if _, err := tx.ExecContext(ctx, `
			INSERT INTO sprint_contract_orders
				(
					sprint_contract_id,
					product_id,
					sugar_level
				)
			VALUES (?, ?, ?)
		`, sprintContractID, product.ID, sugarLevel); err != nil {
			return "", err
		}
	}

	if err := tx.Commit(); err != nil {
		return "", err
	}

	return contractNumber, nil
}

func generateContractNumber() string {
	return fmt.Sprintf("ORD-%d-%03d", time.Now().UnixMilli(), rand.Intn(900)+100)
}