import { readFileSync } from 'node:fs';

const products = JSON.parse(readFileSync('./data/products.json', 'utf-8'));
const sprintProducts = JSON.parse(readFileSync('./data/sprint-products.json', 'utf-8'));
const sprints = JSON.parse(readFileSync('./data/sprints.json', 'utf-8'));

export const resolvers = {
  Sprint: {
    token: (parent: any) => Buffer.from(parent.id).toString('base64'),
  },
  Query: {
    hello: () => {
      return "Hello, world!";
    },
    sprints: () => sprints.filter((s: any) => s.isVisible),
    sprint: (_: any, { token }: { token: string }) => {
      const id = Buffer.from(token, 'base64').toString('ascii');
      return sprints.find((s: any) => s.id === id && s.isVisible && s.isOpen);
    },
    products: () => products,
    sprintProducts: () => sprintProducts.map((sp: any) => ({
      ...sp,
      product: products.find((p: any) => p.id === sp.productId),
    })),
  },
};