# Mono service — GraphQL queries

This document provides example GraphQL queries for the Mono service.

Run the server:

pnpm run dev

Available queries

1) hello

```graphql
{
  hello
}
```

2) sprints

Returns visible sprints.

```graphql
{
  sprints {
    token
    isOpen
    dueDate
  }
}
```

3) sprint(token: String!)

Returns a single sprint by token. Token is the sprint.id encoded in base64 (example: "1" -> "MQ=="). The resolver returns the sprint only if it is visible and open.

```graphql
{
  sprint(token: "MQ==") {
    token
    isOpen
    dueDate
  }
}
```

4) products

```graphql
{
  products {
    id
    name
    description
    image
  }
}
```

5) sprintProducts(token: String!)

Returns products available in a sprint (attach product details to each sprintProduct). Example querying sprint id "1" (token "MQ=="):

```graphql
{
  sprintProducts(token: "MQ==") {
    id
    product {
      id
      name
      description
      image
    }
    variant {
      attributes {
        name
        value
      }
    }
    price
    discount
  }
}
```

Notes

- Encode sprint ids using base64 to build tokens (e.g. node: Buffer.from("1").toString('base64') -> "MQ==").
- Use GraphiQL or any GraphQL client against the running server to execute queries.

