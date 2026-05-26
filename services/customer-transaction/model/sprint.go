package model

type Sprint struct {
	Token       string `json:"token"`
	Name        string `json:"name"`
	Description string `json:"description"`
	IsOpen      int8   `json:"isOpen"`
}
