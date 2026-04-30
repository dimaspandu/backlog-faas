import { createServer } from "node:http";
import { yoga } from "./server";

const server = createServer(yoga);

server.listen(4000, () => {
  console.log("Server running at http://localhost:4000");
});