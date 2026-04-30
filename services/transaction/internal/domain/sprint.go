package domain

import "time"

type Sprint struct {
	ID        string     `json:"id"`
	Token     string     `json:"token"`
	IsOpen    bool       `json:"is_open"`
	IsVisible bool       `json:"is_visible"`
	CreatedAt time.Time  `json:"created_at"`
	DueDate   *time.Time `json:"due_date"`
}
