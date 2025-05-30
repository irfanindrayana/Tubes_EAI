const { gql } = require('apollo-server');

const typeDefs = gql`
  # Route type definition
  type Route {
    id: ID!
    name: String!
    startPoint: String!
    endPoint: String!
    distance: Float!
    description: String
    createdAt: String!
    updatedAt: String!
    schedules: [Schedule!]
  }
  
  # Schedule type definition
  type Schedule {
    id: ID!
    routeId: ID!
    departureTime: String!
    arrivalTime: String!
    busNumber: String!
    capacity: Int!
    price: Float!
    dayOfWeek: String!
    createdAt: String!
    updatedAt: String!
    route: Route!
  }
  
  # Day of week enum
  enum DayOfWeek {
    Monday
    Tuesday
    Wednesday
    Thursday
    Friday
    Saturday
    Sunday
  }
  
  # Input type for creating a new route
  input CreateRouteInput {
    name: String!
    startPoint: String!
    endPoint: String!
    distance: Float!
    description: String
  }
  
  # Input type for updating an existing route
  input UpdateRouteInput {
    name: String
    startPoint: String
    endPoint: String
    distance: Float
    description: String
  }
  
  # Input type for creating a new schedule
  input CreateScheduleInput {
    routeId: ID!
    departureTime: String!
    arrivalTime: String!
    busNumber: String!
    capacity: Int!
    price: Float!
    dayOfWeek: DayOfWeek!
  }
  
  # Input type for updating an existing schedule
  input UpdateScheduleInput {
    departureTime: String
    arrivalTime: String
    busNumber: String
    capacity: Int
    price: Float
    dayOfWeek: DayOfWeek
  }
  
  # Root Query type
  type Query {
    # Get a route by ID
    route(id: ID!): Route
    
    # Get all routes
    routes: [Route!]!
    
    # Search routes by name, start point, or end point
    searchRoutes(query: String!): [Route!]!
    
    # Get a schedule by ID
    schedule(id: ID!): Schedule
    
    # Get all schedules
    schedules(routeId: ID, dayOfWeek: DayOfWeek): [Schedule!]!
    
    # Get schedules by route ID
    schedulesByRoute(routeId: ID!): [Schedule!]!
    
    # Get schedules by day of week
    schedulesByDay(dayOfWeek: DayOfWeek!): [Schedule!]!
  }
  
  # Root Mutation type
  type Mutation {
    # Create a new route
    createRoute(input: CreateRouteInput!): Route!
    
    # Update an existing route
    updateRoute(id: ID!, input: UpdateRouteInput!): Route!
    
    # Delete a route
    deleteRoute(id: ID!): Boolean!
    
    # Create a new schedule
    createSchedule(input: CreateScheduleInput!): Schedule!
    
    # Update an existing schedule
    updateSchedule(id: ID!, input: UpdateScheduleInput!): Schedule!
    
    # Delete a schedule
    deleteSchedule(id: ID!): Boolean!
  }
`;

module.exports = { typeDefs };
