package model

type SprintProductRequest struct {
	ID         int    `json:"id"`
	SugarLevel string `json:"sugarLevel"`
}

type SprintContractRequest struct {
	CustomerName         string                 `json:"customerName"`
	CustomerContact      string                 `json:"customerContact"`
	CustomerAuthProvider *string                `json:"customerAuthProvider"`
	CustomerExternalID   *string                `json:"customerExternalId"`
	Products             []SprintProductRequest `json:"products"`
	Notes                *string                `json:"notes"`
}
