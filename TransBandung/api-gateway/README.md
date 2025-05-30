# API Gateway for TransBandung

This API Gateway provides a unified GraphQL endpoint that integrates all microservices for the TransBandung system.

## Features
- Unified GraphQL schema
- Service composition using Apollo Federation
- Request routing to appropriate microservices

## Environment Variables
- `USER_SERVICE_URL`: URL to the User Management Service GraphQL endpoint
- `BOOKING_SERVICE_URL`: URL to the Booking Service GraphQL endpoint
- `ROUTE_SERVICE_URL`: URL to the Route Schedule Service GraphQL endpoint
- `REVIEW_SERVICE_URL`: URL to the Review Rating Service GraphQL endpoint
- `PAYMENT_SERVICE_URL`: URL to the Payment Service GraphQL endpoint

## Development
```
npm install
npm run dev
```

## Production
```
npm install
npm start
```

## GraphQL Playground
When running, the GraphQL playground is accessible at `/graphql`
