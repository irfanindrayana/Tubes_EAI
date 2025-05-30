const { gql } = require('apollo-server');

const typeDefs = gql`
  # Payment type definition
  type Payment {
    id: ID!
    bookingId: ID!
    amount: Float!
    paymentMethod: PaymentMethod!
    transactionId: String
    status: PaymentStatus!
    paymentDate: String
    createdAt: String!
    updatedAt: String!
    booking: Booking
  }
  
  # Booking type (simplified, will be fetched from Booking Service)
  type Booking {
    id: ID!
    userId: ID!
    scheduleId: ID!
    bookingDate: String!
    status: String!
  }
  
  # Payment method enum
  enum PaymentMethod {
    credit_card
    bank_transfer
    e_wallet
    cash
  }
  
  # Payment status enum
  enum PaymentStatus {
    pending
    completed
    failed
    refunded
  }
  
  # Input type for creating a new payment
  input CreatePaymentInput {
    bookingId: ID!
    amount: Float!
    paymentMethod: PaymentMethod!
  }
  
  # Input type for updating an existing payment
  input UpdatePaymentInput {
    status: PaymentStatus
    transactionId: String
    paymentDate: String
  }
  
  # Root Query type
  type Query {
    # Get a payment by ID
    payment(id: ID!): Payment
    
    # Get all payments
    payments: [Payment!]!
    
    # Get payment by booking ID
    paymentByBookingId(bookingId: ID!): Payment
    
    # Get payments by status
    paymentsByStatus(status: PaymentStatus!): [Payment!]!
  }
  
  # Root Mutation type
  type Mutation {
    # Create a new payment
    createPayment(input: CreatePaymentInput!): Payment!
    
    # Update an existing payment
    updatePayment(id: ID!, input: UpdatePaymentInput!): Payment!
    
    # Process a payment (simulate payment processing)
    processPayment(id: ID!): Payment!
    
    # Refund a payment
    refundPayment(id: ID!): Payment!
  }
`;

module.exports = { typeDefs };
