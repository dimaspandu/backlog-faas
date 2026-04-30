package repository

import "backlog-faas/internal/domain"

type SprintRepository interface {
	GetByToken(token string) (*domain.Sprint, error)
	GetProducts(sprintID string) ([]domain.SprintProduct, error)
}

