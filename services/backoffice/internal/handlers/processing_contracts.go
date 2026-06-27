package handlers

import (
	"bcsaas-backoffice-service/internal/model"
	"bcsaas-backoffice-service/internal/utils"
	"encoding/json"
	"net/http"

	"github.com/gorilla/mux"
)

func (h *Handler) ProcessingContracts(
	w http.ResponseWriter,
	r *http.Request,
) {

	_, ok := AdminSessionFromContext(r.Context())
	if !ok {
		utils.UnauthorizedResponse(w, nil, "Session not found")
		return
	}

	var req model.ContractProcessingRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		utils.BadRequestResponse(w, err, "Invalid request body")
		return
	}

	if req.Status == "" || req.Action == "" {
		utils.BadRequestResponse(w, nil, "Status and Action are required")
		return
	}

	vars := mux.Vars(r)

	var (
		sprintToken    = vars["token"]
		contractNumber = vars["contractNumber"]
	)

	cnt := 0
	if err := h.DB.QueryRowContext(r.Context(), `
		SELECT COUNT(*) AS cnt
		FROM sprint_contracts
		WHERE
			sprint_token = ?
			AND
			contract_number = ?
	`, sprintToken, contractNumber).Scan(&cnt); err != nil {
		utils.InternalServerErrorResponse(w, err)
		return
	}
	if cnt == 0 {
		utils.ResourceNotFoundResponse(w, nil, "No sprint contracts found")
		return
	}

	var updateErr error
	switch req.Status {
	case "request":
		switch req.Action {
		case "PENDING", "APPROVED", "REJECTED", "FULFILLED", "CANCELLED":
			_, updateErr = h.DB.ExecContext(r.Context(), `
				UPDATE sprint_contracts
					SET request_status = ?
				WHERE
					sprint_token = ?
					AND
					contract_number = ?
			`, req.Action, sprintToken, contractNumber)
		default:
			utils.BadRequestResponse(w, nil, "Invalid action for request status")
			return
		}
	case "payment":
		switch req.Action {
		case "UNPAID", "PAID", "REFUNDED":
			_, updateErr = h.DB.ExecContext(r.Context(), `
				UPDATE sprint_contracts
					SET payment_status = ?
				WHERE
					sprint_token = ?
					AND
					contract_number = ?
			`, req.Action, sprintToken, contractNumber)
		default:
			utils.BadRequestResponse(w, nil, "Invalid action for payment status")
			return
		}
	default:
		utils.BadRequestResponse(w, nil, "Invalid status type")
		return
	}

	if updateErr != nil {
		utils.InternalServerErrorResponse(w, updateErr, "Failed to process contract")
		return
	}

	w.WriteHeader(http.StatusOK)

	json.NewEncoder(w).Encode(
		model.SuccessResponse{},
	)
}
