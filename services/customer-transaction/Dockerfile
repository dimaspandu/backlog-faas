# Build stage
FROM golang:1.25-alpine AS builder

WORKDIR /app

# Download dependencies
COPY go.mod go.sum ./
RUN go mod download

# Copy source code
COPY . .

# Build the application (static binary, no CGO)
RUN CGO_ENABLED=0 GOOS=linux go build -ldflags="-w -s" -o /customer-transaction ./main.go

# Final minimal runtime image
FROM alpine:3.19

WORKDIR /app

# Copy the compiled binary from builder stage
COPY --from=builder /customer-transaction .

# Expose default port (can be overridden via SERVER_PORT env)
EXPOSE 8889

# Run the binary
ENTRYPOINT ["./customer-transaction"]
