import { readFileSync } from 'node:fs';

export const resolvers = {
  Query: {
    hello: () => {
      return "Hello, world!";
    },
    sprints: () => {
      return JSON.parse(readFileSync('./data/sprints.json', 'utf-8'));
    },
  },
};