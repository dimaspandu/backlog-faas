package product

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

type Sprint struct {
	Token         string `json:"token"`
	Name          string `json:"name"`
	Description   string `json:"description"`
	TotalProducts int    `json:"totalProducts"`
	IsOpen        int8   `json:"isOpen"`
	Status        string `json:"status"`
}