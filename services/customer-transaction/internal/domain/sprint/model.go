package sprint

import "time"

type Sprint struct {
	Token         string `json:"token"`
	Name          string `json:"name"`
	Description   string `json:"description"`
	TotalProducts int    `json:"totalProducts"`
	IsOpen        int8   `json:"isOpen"`
	Status        string `json:"status"`
}

type SprintMeta struct {
	Token       string     `json:"token"`
	Name        string     `json:"name"`
	Description string     `json:"description"`
	EndAt       *time.Time `json:"endAt"`
	IsOpen      int8       `json:"isOpen"`
	Status      string     `json:"status"`
}

type SprintProductOffering struct {
	ID              uint64  `json:"id"`
	SKU             string  `json:"sku"`
	IsAvailable     int8    `json:"isAvailable"`
	Name            string  `json:"name"`
	Description     string  `json:"description"`
	Images          *string `json:"images"`
	SellingPriceCents uint64  `json:"sellingPriceCents"`
	OfferPriceCents *uint64 `json:"offerPriceCents"`
}

type SprintShow struct {
	Sprint   SprintMeta                         `json:"sprint"`
	Products map[string][]*SprintProductOffering `json:"products"`
}

type SprintCustomer struct {
	ID           string  `json:"id"`
	Name         string  `json:"name"`
	Email        string  `json:"email"`
	Phone        string  `json:"phone"`
	AuthProvider *string `json:"authProvider"`
	ExternalID   *string `json:"externalId"`
}

type SprintContract struct {
	ContractNumber  string     `json:"contractNumber"`
	CustomerName    string     `json:"customerName"`
	CustomerContact string     `json:"customerContact"`
	Notes           *string    `json:"notes"`
	TotalItems      *int       `json:"totalItems"`
	TotalPriceCents *int       `json:"totalPriceCents"`
	RequestStatus   *string    `json:"requestStatus"`
	PaymentStatus   *string    `json:"paymentStatus"`
	CreatedAt       *time.Time `json:"createdAt"`
}

type SprintContractItem struct {
	ProductSku        string `json:"productSku"`
	ProductName       string `json:"productName"`
	SellingPriceCents int    `json:"sellingPriceCents"`
	OfferPriceCents   int    `json:"offerPriceCents"`
	SugarLevel        string `json:"sugarLevel"`
}

type SprintContractShow struct {
	Contract SprintContract       `json:"contract"`
	Items    []SprintContractItem `json:"items"`
}

type SprintProductRequest struct {
	ID         int    `json:"id"`
	SugarLevel string `json:"sugarLevel"`
}

type SprintContractRequest struct {
	CustomerName         string                 `json:"customerName"`
	CustomerContact      string                 `json:"customerContact"`
	CustomerAuthProvider *string                `json:"customerAuthProvider"`
	CustomerExternalID   *string                `json:"customerExternalId"`
	Products             []SprintProductRequest `json:"products"`
	Notes                *string                `json:"notes"`
}