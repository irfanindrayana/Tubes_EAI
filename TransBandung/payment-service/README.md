# Payment Service for TransBandung

This service manages payment processing for the TransBandung system.

## Features
- Create and track payments
- Process payment transactions
- Handle refunds
- Support multiple payment methods

## GraphQL Schema

### Types
- `Payment`: Represents a payment transaction
- `Booking`: Represents a booking (fetched from Booking Service)

### Queries
- `payment(id: ID!)`: Get payment by ID
- `payments`: Get all payments
- `paymentByBookingId(bookingId: ID!)`: Get payment for a specific booking
- `paymentsByStatus(status: PaymentStatus!)`: Get payments with a specific status

### Mutations
- `createPayment(input: CreatePaymentInput!)`: Create a new payment
- `updatePayment(id: ID!, input: UpdatePaymentInput!)`: Update a payment
- `processPayment(id: ID!)`: Process a payment transaction
- `refundPayment(id: ID!)`: Refund a payment

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

## Environment Variables
- `DB_HOST`: Database host (default: localhost)
- `DB_PORT`: Database port (default: 3308)
- `DB_USER`: Database user (default: root)
- `DB_PASSWORD`: Database password (default: empty)
- `DB_NAME`: Database name (default: transbandung_payment)
- `BOOKING_SERVICE_URL`: URL to the Booking Service GraphQL endpoint
