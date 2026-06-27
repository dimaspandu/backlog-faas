package product

import "errors"

var (
	ErrNoProductsFound   = errors.New("no products found")
	ErrNoActiveSprints   = errors.New("no active sprints found")
	ErrInvalidProductID  = errors.New("invalid product id")
)