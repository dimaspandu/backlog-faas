package config

import (
	"os"

	"github.com/joho/godotenv"
)

const envFile = ".env"

func init() {
	godotenv.Load(envFile)
}

type Env struct {
	DBHost     string
	DBPort     string
	DBUser     string
	DBPassword string
	DBName     string

	ServerPort         string
	CORSAllowedOrigins []string
}

func Load() Env {
	originsStr := getEnv("CORS_ALLOWED_ORIGINS", "http://localhost:4500,https://backlog.deduksi.com")
	return Env{
		DBHost:     getEnv("DB_HOST", "127.0.0.1"),
		DBPort:     getEnv("DB_PORT", "3306"),
		DBUser:     getEnv("DB_USER", "root"),
		DBPassword: getEnv("DB_PASSWORD", ""),
		DBName:     getEnv("DB_NAME", "bcsaas"),

		ServerPort:         getEnv("SERVER_PORT", "8799"),
		CORSAllowedOrigins: splitCSV(originsStr),
	}
}

func getEnv(key, fallback string) string {
	if value, ok := os.LookupEnv(key); ok {
		return value
	}
	return fallback
}

func splitCSV(s string) []string {
	if s == "" {
		return nil
	}
	var result []string
	start := 0
	for i := 0; i <= len(s); i++ {
		if i == len(s) || s[i] == ',' {
			if i > start {
				result = append(result, s[start:i])
			}
			start = i + 1
		}
	}
	return result
}
