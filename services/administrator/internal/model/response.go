package model

type MetaResponse struct {
	Page       int `json:"page"`
	PerPage    int `json:"perPage"`
	Total      int `json:"total"`
	TotalPages int `json:"totalPages"`
}

type SuccessResponse struct {
	Data any `json:"data"`
}

type SuccessResponseWithMeta struct {
	Data any           `json:"data"`
	Meta *MetaResponse `json:"meta"`
}

type ErrorResponse struct {
	Message string `json:"message"`
}
