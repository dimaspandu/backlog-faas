package handlers

import (
	"bcsaas-customer-transaction-service/internal/model"
	"bcsaas-customer-transaction-service/internal/utils"
	"encoding/json"
	"fmt"
	"math/rand"
	"net/http"
	"strings"
	"time"
)

func (h *Handler) CreateSprintContract(
	w http.ResponseWriter,
	r *http.Request,
) {

	// get sprint

	sprintMeta, ok := sprintMetaFromRequest(r)

	if !ok {
		utils.ResourceNotFoundResponse(
			w,
			nil,
			"Sprint token disappeared",
		)
		return
	}

	// validate sprint contract availability.

	if !sprintMeta.IsOpen || sprintMeta.Status != "ACTIVE" {
		utils.ForbiddenResponse(
			w,
			nil,
			"Sprint is closed or inactive",
		)
		return
	}

	if sprintMeta.EndAt != nil && !time.Now().Before(*sprintMeta.EndAt) {
		utils.ForbiddenResponse(
			w,
			nil,
			"Sprint has ended",
		)
		return
	}

	// decode request

	var req model.SprintContractRequest

	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.BadRequestResponse(
			w,
			err,
			"Invalid request body",
		)
		return
	}

	// validate request

	if len(req.Products) == 0 {
		utils.BadRequestResponse(
			w,
			nil,
			"Products cannot be empty",
		)
		return
	}

	productIDs := make(map[int]struct{}, len(req.Products))
	for _, product := range req.Products {
		if product.ID <= 0 {
			utils.BadRequestResponse(
				w,
				nil,
				"Product id must be valid",
			)
			return
		}

		if _, exists := productIDs[product.ID]; exists {
			utils.BadRequestResponse(
				w,
				nil,
				"Duplicate products are not supported yet",
			)
			return
		}

		productIDs[product.ID] = struct{}{}
	}

	// build customer

	customer := model.SprintCustomer{}

	switch {
	case utils.IsEmail(req.CustomerContact):
		customer.Email = req.CustomerContact

	case utils.IsPhone(req.CustomerContact):
		customer.Phone = req.CustomerContact

	default:
		utils.BadRequestResponse(
			w,
			nil,
			"Customer contact must be a valid email or phone number",
		)
		return
	}

	// build price query

	var (
		pricePlaceholders []string
		priceQueryArgs    []any
	)

	priceQueryArgs = append(
		priceQueryArgs,
		sprintMeta.Token,
	)

	for productID := range productIDs {
		pricePlaceholders = append(
			pricePlaceholders,
			"?",
		)

		priceQueryArgs = append(
			priceQueryArgs,
			productID,
		)
	}

	priceQuery := fmt.Sprintf(`
		SELECT
			COUNT(*),
			COALESCE(
				SUM(offer_price_cents),
				0
			)
		FROM sprint_product_offerings
		WHERE
			sprint_token = ?
			AND
			product_id IN (%s)
	`,
		strings.Join(pricePlaceholders, ","),
	)

	// calculate total price

	var totalPriceCents int
	var totalProducts int

	if err := h.DB.QueryRowContext(
		r.Context(),
		priceQuery,
		priceQueryArgs...,
	).Scan(&totalProducts, &totalPriceCents); err != nil {
		utils.InternalServerErrorResponse(
			w,
			err,
		)
		return
	}
	if totalProducts != len(productIDs) {
		utils.BadRequestResponse(
			w,
			nil,
			"One or more products are not available in this sprint",
		)
		return
	}

	// begin transaction

	tx, err := h.DB.BeginTx(r.Context(), nil)
	if err != nil {
		utils.InternalServerErrorResponse(
			w,
			err,
		)
		return
	}

	defer tx.Rollback()

	// create customer if not exists

	if _, err := tx.ExecContext(r.Context(), `
		INSERT INTO customers
			(
				name,
				email,
				phone,
				auth_provider,
				external_id
			)
		SELECT
			?,
			?,
			?,
			?,
			?
		WHERE NOT EXISTS (
			SELECT 1
			FROM customers
			WHERE
				email = ?
				AND
				phone = ?
		)
	`,
		req.CustomerName,
		customer.Email,
		customer.Phone,
		req.CustomerAuthProvider,
		req.CustomerExternalID,
		customer.Email,
		customer.Phone,
	); err != nil {
		utils.InternalServerErrorResponse(
			w,
			err,
			"Failed to create customer",
		)
		return
	}

	// create contract

	contractNumber := fmt.Sprintf(
		"ORD-%d-%03d",
		time.Now().UnixMilli(),
		rand.Intn(900)+100,
	)

	result, err := tx.ExecContext(r.Context(), `
		INSERT INTO sprint_contracts
			(
				contract_number,
				sprint_token,
				customer_name,
				customer_contact,
				notes,
				total_price_cents
			)
		VALUES
			(?, ?, ?, ?, ?, ?)
	`,
		contractNumber,
		sprintMeta.Token,
		req.CustomerName,
		req.CustomerContact,
		req.Notes,
		totalPriceCents,
	)

	if err != nil {
		utils.InternalServerErrorResponse(
			w,
			err,
			"Failed to create contract",
		)
		return
	}

	sprintContractID, err := result.LastInsertId()
	if err != nil {
		utils.InternalServerErrorResponse(
			w,
			err,
		)
		return
	}

	// build contract order query

	var (
		orderValues    []string
		orderQueryArgs []any
	)

	for _, product := range req.Products {
		orderValues = append(
			orderValues,
			"(?, ?, ?)",
		)

		orderQueryArgs = append(
			orderQueryArgs,
			sprintContractID,
			product.ID,
			product.SugarLevel,
		)
	}

	orderQuery := fmt.Sprintf(`
		INSERT INTO sprint_contract_orders
			(
				sprint_contract_id,
				product_id,
				sugar_level
			)
		VALUES %s
	`,
		strings.Join(orderValues, ","),
	)

	// create contract orders

	if _, err := tx.ExecContext(
		r.Context(),
		orderQuery,
		orderQueryArgs...,
	); err != nil {
		utils.InternalServerErrorResponse(
			w,
			err,
			"Failed to create contract orders",
		)
		return
	}

	// commit

	if err := tx.Commit(); err != nil {
		utils.InternalServerErrorResponse(
			w,
			err,
		)
		return
	}

	// response

	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(
		model.SuccessResponse{},
	)
}
