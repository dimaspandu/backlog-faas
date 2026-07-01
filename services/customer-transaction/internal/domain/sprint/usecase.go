package sprint

import (
	"context"
	"math"
	"time"

	"bcsaas-customer-transaction-service/internal/utils"
)

type Usecase struct {
	repo *Repository
}

func NewUsecase(repo *Repository) *Usecase {
	return &Usecase{repo: repo}
}

type SprintListResult struct {
	Data       []Sprint
	Page       int
	PerPage    int
	Total      int
	TotalPages int
}

func (u *Usecase) List(ctx context.Context, page, perPage int) (*SprintListResult, error) {
	total, err := u.repo.CountList(ctx)
	if err != nil {
		return nil, err
	}
	if total == 0 {
		return nil, ErrNoSprintsFound
	}

	sprints, err := u.repo.List(ctx, perPage, (page-1)*perPage)
	if err != nil {
		return nil, err
	}

	totalPages := int(math.Ceil(float64(total) / float64(perPage)))

	return &SprintListResult{
		Data:       sprints,
		Page:       page,
		PerPage:    perPage,
		Total:      total,
		TotalPages: totalPages,
	}, nil
}

func (u *Usecase) Show(ctx context.Context, token string) (*SprintShow, error) {
	sprintMeta, err := u.repo.FindByToken(ctx, token)
	if err != nil {
		return nil, err
	}

	products, err := u.repo.GetProducts(ctx, token)
	if err != nil {
		return nil, err
	}

	return &SprintShow{
		Sprint:   *sprintMeta,
		Products: products,
	}, nil
}

type SprintContractListResult struct {
	Data       []SprintContract
	Page       int
	PerPage    int
	Total      int
	TotalPages int
}

func (u *Usecase) ContractList(ctx context.Context, token string, page, perPage int) (*SprintContractListResult, error) {
	total, err := u.repo.CountContracts(ctx, token)
	if err != nil {
		return nil, err
	}
	if total == 0 {
		return nil, ErrNoContractsFound
	}

	contracts, err := u.repo.ListContracts(ctx, token, perPage, (page-1)*perPage)
	if err != nil {
		return nil, err
	}

	totalPages := int(math.Ceil(float64(total) / float64(perPage)))

	return &SprintContractListResult{
		Data:       contracts,
		Page:       page,
		PerPage:    perPage,
		Total:      total,
		TotalPages: totalPages,
	}, nil
}

func (u *Usecase) ContractShow(ctx context.Context, token, contractNumber string) (*SprintContractShow, error) {
	contract, err := u.repo.FindContract(ctx, token, contractNumber)
	if err != nil {
		return nil, err
	}

	items, err := u.repo.ListContractItems(ctx, contractNumber, token)
	if err != nil {
		return nil, err
	}

	return &SprintContractShow{
		Contract: *contract,
		Items:    items,
	}, nil
}

func (u *Usecase) CreateContract(ctx context.Context, sprintMeta *SprintMeta, req *SprintContractRequest) error {
	if sprintMeta.IsOpen == 0 || sprintMeta.Status != "ACTIVE" {
		return ErrSprintNotAvailable
	}

	if sprintMeta.EndAt != nil && !time.Now().Before(*sprintMeta.EndAt) {
		return ErrSprintHasEnded
	}

	if len(req.Products) == 0 {
		return ErrProductsEmpty
	}

	productIDs := make(map[int]struct{}, len(req.Products))
	for _, product := range req.Products {
		if product.ID <= 0 {
			return ErrInvalidProductID
		}
		productIDs[product.ID] = struct{}{}
	}

	customer := &SprintCustomer{}
	switch {
	case utils.IsEmail(req.CustomerContact):
		customer.Email = req.CustomerContact
	case utils.IsPhone(req.CustomerContact):
		customer.Phone = req.CustomerContact
	default:
		return ErrInvalidContact
	}

	totalProducts, prices, err := u.repo.ValidateProducts(ctx, sprintMeta.Token, productIDs)
	if err != nil {
		return err
	}
	if totalProducts != len(productIDs) {
		return ErrProductsNotAvailable
	}

	totalPriceCents := 0
	for _, product := range req.Products {
		if price, ok := prices[product.ID]; ok {
			totalPriceCents += price
		}
	}

	customerAuthProvider := "GUEST"
	if req.CustomerAuthProvider != nil {
		customerAuthProvider = *req.CustomerAuthProvider
	}

	customerExternalID := ""
	if req.CustomerExternalID != nil {
		customerExternalID = utils.SanitizeString(*req.CustomerExternalID, 0)
	}

	notes := ""
	if req.Notes != nil {
		notes = utils.SanitizeString(*req.Notes, 1000)
	}

	customerName := utils.SanitizeString(req.CustomerName, 255)

	if len(customerName) == 0 {
		return ErrCustomerNameEmpty
	}
	if len(customerName) < 2 {
		return ErrCustomerNameShort
	}
	if len(customerName) > 255 {
		return ErrCustomerNameLong
	}

	contact := utils.SanitizeContact(req.CustomerContact)
	if len(contact) == 0 {
		return ErrContactEmpty
	}

	if len(notes) > 1000 {
		return ErrNotesTooLong
	}

	_, err = u.repo.CreateContract(ctx, &CreateContractParams{
		SprintToken:         sprintMeta.Token,
		CustomerName:        customerName,
		CustomerContact:     contact,
		Notes:               notes,
		TotalPriceCents:     totalPriceCents,
		CustomerAuthProvider: customerAuthProvider,
		CustomerExternalID:    customerExternalID,
		CustomerEmail:         customer.Email,
		CustomerPhone:         customer.Phone,
		Products:              req.Products,
	})
	if err != nil {
		return err
	}

	return nil
}