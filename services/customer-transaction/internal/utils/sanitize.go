package utils

import (
	"html"
	"regexp"
	"strings"
)

var htmlTagRegex = regexp.MustCompile(
	`<[^>]*>`,
)

var eventHandlerRegex = regexp.MustCompile(
	`(?i)(on\w+\s*=|javascript\s*:|data\s*:|vbscript\s*:)`,
)

func SanitizeString(s string, maxLength int) string {
	s = html.UnescapeString(s)
	s = htmlTagRegex.ReplaceAllString(s, "")
	s = eventHandlerRegex.ReplaceAllString(s, "")
	s = strings.TrimSpace(s)
	if maxLength > 0 && len(s) > maxLength {
		s = s[:maxLength]
	}
	return s
}

func SanitizeContact(s string) string {
	s = html.UnescapeString(s)
	s = htmlTagRegex.ReplaceAllString(s, "")
	s = eventHandlerRegex.ReplaceAllString(s, "")
	s = strings.TrimSpace(s)
	return s
}
