export const typeDefs = /* GraphQL */ `
  type Quota {
    regularBottle: Int!
    largeBottle: Int!
    regularCup: Int!
    largeCup: Int!
  }

  type Sprint {
    id: ID!
    token: String!
    isOpen: Boolean!
    isVisible: Boolean!
    quota: Quota!
    createdAt: String!
    dueDate: String!
  }

  type Query {
    hello: String,
    sprints: [Sprint!]!
  }
`;