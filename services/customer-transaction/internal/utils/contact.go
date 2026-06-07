package utils

import "regexp"

var (
	emailRegex = regexp.MustCompile(
		`^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$`,
	)

	phoneRegex = regexp.MustCompile(
		`^\+?[0-9]{8,15}$`,
	)
)

func IsEmail(s string) bool {
	return emailRegex.MatchString(s)
}

func IsPhone(s string) bool {
	return phoneRegex.MatchString(s)
}

func MaskContact(s string) string {
	if IsEmail(s) {
		return maskEmail(s)
	}
	if IsPhone(s) {
		return maskPhone(s)
	}
	return s
}

func maskEmail(email string) string {
	parts := splitEmail(email)
	if len(parts) != 2 {
		return email
	}
	local, domain := parts[0], parts[1]
	if len(local) <= 2 {
		return "**" + "@" + domain
	}
	return local[:2] + "****@" + domain
}

func maskPhone(phone string) string {
	if len(phone) <= 4 {
		return "****"
	}
	return phone[:2] + "****" + phone[len(phone)-2:]
}

func splitEmail(s string) []string {
	for i := 0; i < len(s); i++ {
		if s[i] == '@' {
			return []string{s[:i], s[i+1:]}
		}
	}
	return nil
}
