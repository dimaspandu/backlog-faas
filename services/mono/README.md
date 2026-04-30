# Mono GraphQL Service

This is a GraphQL service built with GraphQL Yoga, designed for serverless deployment (FaaS).

## Features

- GraphQL API with "hello" and "sprints" queries
- TypeScript support
- Development server for local testing
- Serverless handler for production deployment

## Installation

Ensure you have pnpm installed. Then, install dependencies:

```bash
pnpm install
```

## Development

To run the development server on port 4000:

```bash
pnpm run dev
```

The GraphQL endpoint will be available at `http://localhost:4000/graphql`.

## Production

For production, you have two deployment options:

### Serverless Deployment (FaaS)

Deploy the service as a serverless function. The handler is located in `src/handler.ts`, which exports the GraphQL Yoga instance as `handler`.

Configure your FaaS platform (e.g., AWS Lambda, Vercel, Netlify) to use this handler. For AWS Lambda, set the handler to `src/handler.handler`.

### Container Deployment

Build and run the service as a Docker container.

Build the image:

```bash
docker build -t mono-graphql .
```

Run the container:

```bash
docker run -p 4000:4000 mono-graphql
```

The GraphQL endpoint will be available at `http://localhost:4000/graphql`.

## Scripts

- `pnpm run dev`: Start the development server
- `pnpm run typecheck`: Run TypeScript type checking
- `pnpm run test`: Run tests (placeholder)

## Dependencies

- `graphql-yoga`: GraphQL server framework
- `graphql`: GraphQL implementation
- `@types/node`: TypeScript types for Node.js
- `tsx`: TypeScript execution
- `typescript`: TypeScript compiler