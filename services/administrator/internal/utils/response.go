package utils

import (
	"bcsaas-administrator-service/internal/model"
	"encoding/json"
	"log"
	"net/http"
)

func InternalServerErrorResponse(
	w http.ResponseWriter,
	err error,
	message ...string,
) {
	log.Println(err)

	msg := "Internal Server Error"
	if len(message) > 0 {
		msg = message[0]
	}

	w.WriteHeader(http.StatusInternalServerError)

	json.NewEncoder(w).Encode(
		model.ErrorResponse{
			Message: msg,
		},
	)
}

func ResourceNotFoundResponse(
	w http.ResponseWriter,
	err error,
	message ...string,
) {
	log.Println(err)

	msg := "Resource Not Found"
	if len(message) > 0 {
		msg = message[0]
	}

	w.WriteHeader(http.StatusNotFound)

	json.NewEncoder(w).Encode(
		model.ErrorResponse{
			Message: msg,
		},
	)
}

func BadRequestResponse(
	w http.ResponseWriter,
	err error,
	message ...string,
) {
	log.Println(err)

	msg := "Bad Request"
	if len(message) > 0 {
		msg = message[0]
	}

	w.WriteHeader(http.StatusBadRequest)

	json.NewEncoder(w).Encode(
		model.ErrorResponse{
			Message: msg,
		},
	)
}

func ForbiddenResponse(
	w http.ResponseWriter,
	err error,
	message ...string,
) {
	log.Println(err)

	msg := "Forbidden"
	if len(message) > 0 {
		msg = message[0]
	}

	w.WriteHeader(http.StatusForbidden)

	json.NewEncoder(w).Encode(
		model.ErrorResponse{
			Message: msg,
		},
	)
}

func UnauthorizedResponse(
	w http.ResponseWriter,
	err error,
	message ...string,
) {
	log.Println(err)

	msg := "Unauthorized"
	if len(message) > 0 {
		msg = message[0]
	}

	w.WriteHeader(http.StatusUnauthorized)

	json.NewEncoder(w).Encode(
		model.ErrorResponse{
			Message: msg,
		},
	)
}
