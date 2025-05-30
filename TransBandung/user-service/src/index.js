const { ApolloServer } = require('apollo-server');
const { typeDefs } = require('./schema');
const { resolvers } = require('./resolvers');
const { connectDB } = require('./db');

// Connect to database
connectDB();

// Create Apollo Server instance
const server = new ApolloServer({
  typeDefs,
  resolvers,
  context: ({ req }) => {
    // Get token from headers
    const token = req.headers.authorization || '';
    // Here you would normally validate JWT token and return user
    return { token };
  },
});

// Start the server
const PORT = process.env.PORT || 4000;
server.listen(PORT).then(({ url }) => {
  console.log(`User Management Service running at ${url}`);
});
