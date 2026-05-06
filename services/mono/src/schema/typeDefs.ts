import { readFileSync } from 'node:fs';

export const typeDefs = readFileSync('./src/schema/typeDefs.graphql', 'utf-8');