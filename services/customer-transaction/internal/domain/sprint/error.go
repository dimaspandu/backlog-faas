package sprint

import "errors"

var (
	ErrNoSprintsFound      = errors.New("no sprints found")
	ErrSprintNotFound       = errors.New("sprint not found")
	ErrSprintNotAvailable  = errors.New("sprint is closed or inactive")
	ErrSprintHasEnded      = errors.New("sprint has ended")
	ErrNoContractsFound    = errors.New("no sprint contracts found")
	ErrContractNotFound    = errors.New("no sprint contract found")
	ErrProductsEmpty       = errors.New("products cannot be empty")
	ErrInvalidProductID    = errors.New("product id must be valid")
	ErrInvalidContact      = errors.New("customer contact must be valid email or phone")
	ErrCustomerNameEmpty   = errors.New("customer name cannot be empty")
	ErrCustomerNameShort   = errors.New("customer name must be at least 2 characters")
	ErrCustomerNameLong    = errors.New("customer name must not exceed 255 characters")
	ErrContactEmpty        = errors.New("customer contact cannot be empty")
	ErrNotesTooLong        = errors.New("notes must not exceed 1000 characters")
	ErrProductsNotAvailable = errors.New("one or more products are not available in this sprint")
)