package model

type Sprint struct {
	Token         string `json:"token"`
	Name          string `json:"name"`
	Description   string `json:"description"`
	TotalProducts int    `json:"totalProducts"`
	IsOpen        bool   `json:"isOpen"`
	Status        string `json:"status"`
}