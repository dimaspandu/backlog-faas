package model

import "time"

type AdminSession struct {
	ID        uint64    `json:"id,string"`
	Username  string    `json:"username"`
	Role      string    `json:"role"`
	Token     string    `json:"token"`
	ExpiresAt time.Time `json:"expiresAt"`
}
