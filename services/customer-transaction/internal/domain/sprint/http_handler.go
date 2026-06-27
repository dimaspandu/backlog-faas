package sprint

import (
	"bcsaas-customer-transaction-service/internal/model"
	"bcsaas-customer-transaction-service/internal/utils"
	"context"
	"encoding/json"
	"net/http"

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
		case ErrNoSprintsFound:
			utils.ResourceNotFoundResponse(w, nil, "No sprints found")
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

func (h *Handler) Show(w http.ResponseWriter, r *http.Request) {
	vars := mux.Vars(r)
	token := vars["token"]

	result, err := h.Usecase.Show(r.Context(), token)
	if err != nil {
		switch err {
		case ErrSprintNotFound:
			utils.ResourceNotFoundResponse(w, err, "Sprint not found")
		default:
			utils.InternalServerErrorResponse(w, err)
		}
		return
	}

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(model.SuccessResponse{Data: result})
}

type SprintTokenValidationMiddleware struct {
	Repo *Repository
}

func NewSprintTokenValidation(repo *Repository) *SprintTokenValidationMiddleware {
	return &SprintTokenValidationMiddleware{Repo: repo}
}

func (m *SprintTokenValidationMiddleware) Validate(f http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")

		vars := mux.Vars(r)
		token := vars["token"]

		sprintMeta, err := m.Repo.FindByToken(r.Context(), token)
		if err != nil {
			switch err {
			case ErrSprintNotFound:
				utils.ResourceNotFoundResponse(w, err, "Sprint not found or not open")
			default:
				utils.InternalServerErrorResponse(w, err)
			}
			return
		}

		ctx := contextWithSprintMeta(r.Context(), sprintMeta)
		f(w, r.WithContext(ctx))
	}
}

type sprintMetaContextKey struct{}

func contextWithSprintMeta(ctx context.Context, sprintMeta *SprintMeta) context.Context {
	return context.WithValue(ctx, sprintMetaContextKey{}, sprintMeta)
}

func sprintMetaFromContext(ctx context.Context) (*SprintMeta, bool) {
	sprintMeta, ok := ctx.Value(sprintMetaContextKey{}).(*SprintMeta)
	return sprintMeta, ok
}

func (h *Handler) CreateContract(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	sprintMeta, ok := sprintMetaFromContext(r.Context())
	if !ok {
		utils.ResourceNotFoundResponse(w, nil, "Sprint token disappeared")
		return
	}

	var req SprintContractRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.BadRequestResponse(w, err, "Invalid request body")
		return
	}

	if err := h.Usecase.CreateContract(r.Context(), sprintMeta, &req); err != nil {
		switch err {
		case ErrSprintNotAvailable, ErrSprintHasEnded:
			utils.ForbiddenResponse(w, nil, err.Error())
		case ErrProductsEmpty, ErrInvalidProductID, ErrInvalidContact, ErrProductsNotAvailable:
			utils.BadRequestResponse(w, nil, err.Error())
		case ErrCustomerNameEmpty, ErrCustomerNameShort, ErrCustomerNameLong, ErrContactEmpty, ErrNotesTooLong:
			utils.BadRequestResponse(w, nil, err.Error())
		default:
			utils.InternalServerErrorResponse(w, err)
		}
		return
	}

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(model.SuccessResponse{})
}

func (h *Handler) ContractList(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	sprintMeta, ok := sprintMetaFromContext(r.Context())
	if !ok {
		utils.ResourceNotFoundResponse(w, nil, "Sprint token disappeared")
		return
	}

	page, perPage := utils.ParsePagination(r)
	result, err := h.Usecase.ContractList(r.Context(), sprintMeta.Token, page, perPage)
	if err != nil {
		switch err {
		case ErrNoContractsFound:
			utils.ResourceNotFoundResponse(w, nil, "No sprint contracts found")
		default:
			utils.InternalServerErrorResponse(w, err)
		}
		return
	}

	for i, c := range result.Data {
		c.CustomerContact = utils.MaskContact(c.CustomerContact)
		result.Data[i] = c
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

func (h *Handler) ContractShow(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	sprintMeta, ok := sprintMetaFromContext(r.Context())
	if !ok {
		utils.ResourceNotFoundResponse(w, nil, "Sprint token disappeared")
		return
	}

	vars := mux.Vars(r)
	contractNumber := vars["contractNumber"]

	result, err := h.Usecase.ContractShow(r.Context(), sprintMeta.Token, contractNumber)
	if err != nil {
		switch err {
		case ErrContractNotFound:
			utils.ResourceNotFoundResponse(w, err, "No sprint contract found")
		default:
			utils.InternalServerErrorResponse(w, err)
		}
		return
	}

	result.Contract.CustomerContact = utils.MaskContact(result.Contract.CustomerContact)

	w.WriteHeader(http.StatusOK)
	json.NewEncoder(w).Encode(model.SuccessResponse{Data: result})
}