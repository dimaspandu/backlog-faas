import { readFileSync } from 'node:fs';

const products = JSON.parse(readFileSync('./data/products.json', 'utf-8'));
const sprintProductsData = JSON.parse(readFileSync('./data/sprint-products.json', 'utf-8'));
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
    sprintProducts: (_: any, { token }: { token: string }) => {
      const sprintId = Buffer.from(token, 'base64').toString('ascii');
      return sprintProductsData
        .filter((sp: any) => sp.sprintId === sprintId)
        .map((sp: any) => {
          return {
            ...sp,
            product: products.find((p: any) => p.id === sp.productId),
          };
        })
    },
  },
};