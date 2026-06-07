package model

type SessionRequest struct {
	Username string `json:"username"`
	Password string `json:"password"`
}
