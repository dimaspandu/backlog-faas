package utils

import (
	"net/http"
	"strconv"
)

func ParsePagination(r *http.Request) (int, int) {
	page := 1
	perPage := 10

	q := r.URL.Query()

	if v := q.Get("page"); v != "" {
		if n, err := strconv.Atoi(v); err == nil && n > 0 {
			page = n
		}
	}

	if v := q.Get("perPage"); v != "" {
		if n, err := strconv.Atoi(v); err == nil && n > 0 {
			perPage = n
		}
	}

	if perPage > 100 {
		perPage = 100
	}

	return page, perPage
}