package model

import "encoding/json"

type SprintDetailData struct {
	Sprint   SprintPublic    `json:"sprint"`
	Products []SprintProduct `json:"products"`
}

type SprintDetailResponse struct {
	Data SprintDetailData `json:"data"`
}

type SprintPublic struct {
	Name        string `json:"name"`
	Description string `json:"description"`
}

type SprintProduct struct {
	ID                 int             `json:"id"`
	ProductName        string          `json:"name"`
	ProductDescription string          `json:"description"`
	VariantAttributes  json.RawMessage `json:"attributes"`
	PriceCents         int64           `json:"priceCents"`
	ListPriceCents     int             `json:"listPriceCents"`
}
