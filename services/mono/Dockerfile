FROM node:18-alpine

# Install pnpm
RUN npm install -g pnpm

# Set working directory
WORKDIR /app

# Copy package files
COPY package*.json pnpm-lock.yaml ./

# Install dependencies
RUN pnpm install --frozen-lockfile --prod

# Copy source code
COPY . .

# Expose port
EXPOSE 4000

# Start the application
CMD ["pnpm", "run", "prod"]