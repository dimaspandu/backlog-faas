import { SprintRepository } from '../repositories/SprintRepository';

const sprintRepo = new SprintRepository();

export const resolvers = {
  Sprint: {
    token: (parent: any) => parent.token,
  },
  Query: {
    health: async () => {
      return 'ok';
    },
    sprints: async (_: any, args: any) => {
      const rows = await sprintRepo.findSprints(args);
      return rows;
    },
  },
};
