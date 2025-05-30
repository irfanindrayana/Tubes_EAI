const { ApolloServer } = require('apollo-server');
const { ApolloGateway } = require('@apollo/gateway');
require('dotenv').config();

// Service URLs
const USER_SERVICE_URL = process.env.USER_SERVICE_URL || 'http://localhost:4001/graphql';
const BOOKING_SERVICE_URL = process.env.BOOKING_SERVICE_URL || 'http://localhost:4002/graphql';
const ROUTE_SERVICE_URL = process.env.ROUTE_SERVICE_URL || 'http://localhost:4003/graphql';
const REVIEW_SERVICE_URL = process.env.REVIEW_SERVICE_URL || 'http://localhost:4004/graphql';
const PAYMENT_SERVICE_URL = process.env.PAYMENT_SERVICE_URL || 'http://localhost:4005/graphql';

// Create a gateway instance
const gateway = new ApolloGateway({
  serviceList: [
    { name: 'users', url: USER_SERVICE_URL },
    { name: 'bookings', url: BOOKING_SERVICE_URL },
    { name: 'routes', url: ROUTE_SERVICE_URL },
    { name: 'reviews', url: REVIEW_SERVICE_URL },
    { name: 'payments', url: PAYMENT_SERVICE_URL }
  ],
  // Experimental: Enabling this enables automatic composition of services
  experimental_pollInterval: 10000
});

// Custom error formatter to standardize error responses
const formatError = (error) => {
  console.error('GraphQL Error:', error);
  
  // Don't expose internal server errors to clients
  if (error.extensions?.code === 'INTERNAL_SERVER_ERROR') {
    return {
      message: 'An unexpected error occurred',
      code: 'SERVER_ERROR',
      status: 500
    };
  }
  
  // Return more specific error details for other error types
  return {
    message: error.message,
    code: error.extensions?.code || 'ERROR',
    path: error.path,
    status: error.extensions?.status || 400,
  };
};

// Create Apollo Server instance
const server = new ApolloServer({
  gateway,
  subscriptions: false, // Apollo Gateway doesn't support subscriptions yet
  context: ({ req }) => {
    // Get token from headers
    const token = req.headers.authorization || '';
    return { token };
  },
  formatError,
  cors: {
    origin: '*',
    credentials: true,
  },
  plugins: [
    {
      requestDidStart(requestContext) {
        console.log(`Request started: ${requestContext.request.operationName || 'anonymous operation'}`);
        
        return {
          didEncounterErrors(ctx) {
            console.error('Encountered errors during request execution', ctx.errors);
          },
          willSendResponse(ctx) {
            console.log(`Request completed: ${requestContext.request.operationName || 'anonymous operation'}`);
          }
        };
      }
    }
  ]
});

// Start the server
const PORT = process.env.PORT || 4000;
server.listen(PORT).then(({ url }) => {
  console.log(`🚌 TransBandung API Gateway running at ${url}`);
  console.log('Connected Services:');
  console.log(`- User Service: ${USER_SERVICE_URL}`);
  console.log(`- Booking Service: ${BOOKING_SERVICE_URL}`);
  console.log(`- Route Service: ${ROUTE_SERVICE_URL}`);
  console.log(`- Review Service: ${REVIEW_SERVICE_URL}`);
  console.log(`- Payment Service: ${PAYMENT_SERVICE_URL}`);
});
