package product

import (
	"bcsaas-customer-transaction-service/internal/model"
	"bcsaas-customer-transaction-service/internal/utils"
	"encoding/json"
	"net/http"
	"strconv"

	"github.com/gorilla/mux"
)

type Handler struct {
	Usecase *Usecase
}

func NewHandler(usecase *Usecase) *Handler {
	return &Handler{Usecase: usecase}
}

func (h *Handler) List(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	page, perPage := utils.ParsePagination(r)
	result, err := h.Usecase.List(r.Context(), page, perPage)
	if err != nil {
		switch err {
		case ErrNoProductsFound:
			utils.ResourceNotFoundResponse(w, nil, "No products found")
		default:
			utils.InternalServerErrorResponse(w, err)
		}
		return
	}

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(model.SuccessResponseWithMeta{
		Data: result.Data,
		Meta: &model.MetaResponse{
			Page:       result.Page,
			PerPage:    result.PerPage,
			Total:      result.Total,
			TotalPages: result.TotalPages,
		},
	})
}

func (h *Handler) ActiveSprintList(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	vars := mux.Vars(r)
	productID, err := strconv.ParseUint(vars["id"], 10, 64)
	if err != nil {
		utils.BadRequestResponse(w, err, "Invalid product id")
		return
	}

	sprints, err := h.Usecase.ListActiveSprints(r.Context(), int(productID))
	if err != nil {
		switch err {
		case ErrNoActiveSprints, ErrInvalidProductID:
			utils.ResourceNotFoundResponse(w, nil, err.Error())
		default:
			utils.InternalServerErrorResponse(w, err)
		}
		return
	}

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(&model.SuccessResponse{Data: sprints})
}