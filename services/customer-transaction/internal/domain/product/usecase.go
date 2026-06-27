package product

import (
	"context"
	"math"
)

type Usecase struct {
	repo *Repository
}

func NewUsecase(repo *Repository) *Usecase {
	return &Usecase{repo: repo}
}

type ProductListResult struct {
	Data       []Product
	Page       int
	PerPage    int
	Total      int
	TotalPages int
}

func (u *Usecase) List(ctx context.Context, page, perPage int) (*ProductListResult, error) {
	total, err := u.repo.CountList(ctx)
	if err != nil {
		return nil, err
	}
	if total == 0 {
		return nil, ErrNoProductsFound
	}

	products, err := u.repo.List(ctx, perPage, (page-1)*perPage)
	if err != nil {
		return nil, err
	}

	totalPages := int(math.Ceil(float64(total) / float64(perPage)))

	return &ProductListResult{
		Data:       products,
		Page:       page,
		PerPage:    perPage,
		Total:      total,
		TotalPages: totalPages,
	}, nil
}

func (u *Usecase) ListActiveSprints(ctx context.Context, productID int) ([]Sprint, error) {
	sprints, err := u.repo.ListActiveSprints(ctx, productID)
	if err != nil {
		return nil, err
	}
	if len(sprints) == 0 {
		return nil, ErrNoActiveSprints
	}
	return sprints, nil
}