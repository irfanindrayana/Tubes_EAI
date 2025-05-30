const { gql } = require('apollo-server');

const typeDefs = gql`
  # User type definition
  type User {
    id: ID!
    username: String!
    email: String!
    fullName: String!
    phoneNumber: String
    userType: UserType!
    createdAt: String!
    updatedAt: String!
  }
  
  # User type enum
  enum UserType {
    customer
    admin
  }
  
  # Authentication response type
  type AuthPayload {
    token: String!
    user: User!
  }
  
  # Input type for creating a new user
  input CreateUserInput {
    username: String!
    password: String!
    email: String!
    fullName: String!
    phoneNumber: String
    userType: UserType
  }
  
  # Input type for updating an existing user
  input UpdateUserInput {
    username: String
    password: String
    email: String
    fullName: String
    phoneNumber: String
    userType: UserType
  }
  
  # Root Query type
  type Query {
    # Get a user by ID
    user(id: ID!): User
    
    # Get a user by username
    userByUsername(username: String!): User
    
    # Get all users
    users: [User!]!
    
    # Get current logged in user
    me: User
  }
  
  # Root Mutation type
  type Mutation {
    # Create a new user
    createUser(input: CreateUserInput!): User!
    
    # Update an existing user
    updateUser(id: ID!, input: UpdateUserInput!): User!
    
    # Delete a user
    deleteUser(id: ID!): Boolean!
    
    # Login and get authentication token
    login(username: String!, password: String!): AuthPayload!
  }
`;

module.exports = { typeDefs };
