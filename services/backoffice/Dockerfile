# ----------------------------------------------------------------------
# Backoffice Service – Production‑Ready Go Docker Image
# ----------------------------------------------------------------------
# Multi‑stage build to produce a minimal, secure container image.
# No build tools or source code in the final image.
# ----------------------------------------------------------------------

# ----------------
# Build stage
# ----------------
FROM golang:1.25-alpine AS builder

# Install certificates and git for module downloads
RUN apk add --no-cache \
    ca-certificates \
    git

WORKDIR /build

# Cache Go module downloads by copying go.mod/go.sum first
COPY go.mod go.sum ./
RUN go mod download

# Copy application source
COPY . .

# Build the binary with CGO disabled for static linking
RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 \
    go build -ldflags="-s -w" -o /app/bin/server ./cmd/api

# ----------------
# Run stage
# ----------------
FROM alpine:3.20

# Install ca-certificates for HTTPS outbound calls
RUN apk add --no-cache ca-certificates

# Create non‑root user for security
RUN addgroup -g 1001 -S appgroup && \
    adduser -u 1001 -S appuser -G appgroup

WORKDIR /app

# Copy binary from builder stage
COPY --from=builder --chown=appuser:appgroup /app/bin/server /app/server

# Expose the application port
EXPOSE 8699

# Switch to non‑root user
USER appuser

# Run the service
CMD ["./server"]
