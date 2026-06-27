package model

type SessionRequest struct {
	Username string `json:"username"`
	Password string `json:"password"`
}
type ContractProcessingRequest struct {
	Status string `json:"status"`
	Action string `json:"action"`
}