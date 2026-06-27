package model

type Product struct {
	ID                int     `json:"id"`
	Sku               string  `json:"sku"`
	Slug              string  `json:"slug"`
	Name              string  `json:"name"`
	Description       *string `json:"description"`
	Category          string  `json:"category"`
	Images            *string `json:"images"`
	SellingPriceCents int     `json:"sellingPriceCents"`
}
