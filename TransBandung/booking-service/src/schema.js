const { gql } = require('apollo-server');

const typeDefs = gql`
  # Booking type definition
  type Booking {
    id: ID!
    userId: ID!
    scheduleId: ID!
    bookingDate: String!
    seatNumber: Int!
    status: BookingStatus!
    createdAt: String!
    updatedAt: String!
    user: User
    schedule: Schedule
  }
  
  # Schedule type (simplified, will be fetched from Route Service)
  type Schedule {
    id: ID!
    routeId: ID!
    departureTime: String!
    arrivalTime: String!
    busNumber: String!
    capacity: Int!
    price: Float!
    dayOfWeek: String!
    route: Route
  }
  
  # Route type (simplified, will be fetched from Route Service)
  type Route {
    id: ID!
    name: String!
    startPoint: String!
    endPoint: String!
    distance: Float!
    description: String
  }
  
  # User type (simplified, will be fetched from User Service)
  type User {
    id: ID!
    username: String!
    email: String!
    fullName: String!
  }
  
  # Booking status enum
  enum BookingStatus {
    pending
    confirmed
    cancelled
    completed
  }
  
  # Input type for creating a new booking
  input CreateBookingInput {
    userId: ID!
    scheduleId: ID!
    bookingDate: String!
    seatNumber: Int!
  }
  
  # Input type for updating an existing booking
  input UpdateBookingInput {
    status: BookingStatus
    bookingDate: String
    seatNumber: Int
  }
  
  # Root Query type
  type Query {
    # Get a booking by ID
    booking(id: ID!): Booking
    
    # Get all bookings
    bookings: [Booking!]!
    
    # Get bookings by user ID
    bookingsByUser(userId: ID!): [Booking!]!
    
    # Get bookings by schedule ID
    bookingsBySchedule(scheduleId: ID!): [Booking!]!
    
    # Check if a seat is available
    isSeatAvailable(scheduleId: ID!, bookingDate: String!, seatNumber: Int!): Boolean!
    
    # Get available seats for a schedule on a specific date
    availableSeats(scheduleId: ID!, bookingDate: String!): [Int!]!
  }
  
  # Root Mutation type
  type Mutation {
    # Create a new booking
    createBooking(input: CreateBookingInput!): Booking!
    
    # Update an existing booking
    updateBooking(id: ID!, input: UpdateBookingInput!): Booking!
    
    # Cancel a booking
    cancelBooking(id: ID!): Booking!
    
    # Complete a booking
    completeBooking(id: ID!): Booking!
  }
`;

module.exports = { typeDefs };
