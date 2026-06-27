package model

import "time"

type Sprint struct {
	Token         string `json:"token"`
	Name          string `json:"name"`
	Description   string `json:"description"`
	TotalProducts int    `json:"totalProducts"`
	IsOpen        bool   `json:"isOpen"`
	Status        string `json:"status"`
}

type SprintContract struct {
	ID              uint64     `json:"id,string"`
	ContractNumber  string     `json:"contractNumber"`
	SprintToken     string     `json:"sprintToken"`
	CustomerName    string     `json:"customerName"`
	CustomerContact string     `json:"customerContact"`
	Notes           string     `json:"notes"`
	TotalPriceCents int64      `json:"totalPriceCents"`
	RequestStatus   string     `json:"requestStatus"`
	PaymentStatus   string     `json:"paymentStatus"`
	ApprovedBy      *uint64    `json:"approvedBy,string"`
	ApprovedAt      *time.Time `json:"approvedAt"`
}