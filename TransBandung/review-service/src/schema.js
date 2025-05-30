const { gql } = require('apollo-server');

const typeDefs = gql`
  # Review type definition
  type Review {
    id: ID!
    userId: ID!
    bookingId: ID!
    rating: Int!
    comment: String
    createdAt: String!
    updatedAt: String!
    user: User
    booking: Booking
  }
  
  # User type (simplified, will be fetched from User Service)
  type User {
    id: ID!
    username: String!
    fullName: String!
  }
  
  # Booking type (simplified, will be fetched from Booking Service)
  type Booking {
    id: ID!
    userId: ID!
    scheduleId: ID!
    bookingDate: String!
    status: String!
  }
  
  # Input type for creating a new review
  input CreateReviewInput {
    userId: ID!
    bookingId: ID!
    rating: Int!
    comment: String
  }
  
  # Input type for updating an existing review
  input UpdateReviewInput {
    rating: Int
    comment: String
  }
  
  # Root Query type
  type Query {
    # Get a review by ID
    review(id: ID!): Review
    
    # Get all reviews
    reviews: [Review!]!
    
    # Get reviews by user ID
    reviewsByUser(userId: ID!): [Review!]!
    
    # Get reviews by booking ID
    reviewsByBookingId(bookingId: ID!): [Review!]!
    
    # Get average rating
    averageRating: Float!
  }
  
  # Root Mutation type
  type Mutation {
    # Create a new review
    createReview(input: CreateReviewInput!): Review!
    
    # Update an existing review
    updateReview(id: ID!, input: UpdateReviewInput!): Review!
    
    # Delete a review
    deleteReview(id: ID!): Boolean!
  }
`;

module.exports = { typeDefs };
