package domain

type SprintProduct struct {
	ProductID   int     `json:"product_id"`
	Name        string  `json:"name"`
	Description string  `json:"description"`
	Price       int     `json:"price"`
	Discount    float64 `json:"discount"`
	FinalPrice  int     `json:"final_price"`
	IsAvailable bool    `json:"is_available"`
}
