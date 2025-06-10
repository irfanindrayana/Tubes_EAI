# GraphQL Endpoint Testing Guide

This document contains test queries and mutations for the Laravel Trans Bandung GraphQL API.

## Basic Introspection Queries

### 1. Check Available Queries
```graphql
{
  __schema {
    queryType {
      fields {
        name
        description
        type {
          name
        }
      }
    }
  }
}
```

### 2. Check Available Mutations
```graphql
{
  __schema {
    mutationType {
      fields {
        name
        description
        args {
          name
          type {
            name
          }
        }
      }
    }
  }
}
```

### 3. Check Available Types
```graphql
{
  __schema {
    types {
      name
      kind
      description
    }
  }
}
```

## Authentication Testing

### 4. Register New User
```graphql
mutation {
  register(input: {
    name: "Test User"
    email: "test@example.com"
    password: "password123"
    password_confirmation: "password123"
    phone: "08123456789"
  }) {
    id
    name
    email
    role
  }
}
```

### 5. Login User
```graphql
mutation {
  login(email: "test@example.com", password: "password123") {
    id
    name
    email
    role
  }
}
```

### 6. Get Current User (requires authentication)
```graphql
{
  me {
    id
    name
    email
    role
  }
}
```

### 7. Logout User
```graphql
mutation {
  logout
}
```

## Data Queries (No Authentication Required)

### 8. Get Single Route
```graphql
{
  route(id: 1) {
    id
    route_name
    origin
    destination
    distance
    estimated_duration
  }
}
```

### 9. Get Single Schedule
```graphql
{
  schedule(id: 1) {
    id
    route {
      id
      route_name
      origin
      destination
    }
    departure_time
    arrival_time
    price
    total_seats
    available_seats
  }
}
```

### 10. Get Available Seats
```graphql
{
  seats(schedule_id: 1, travel_date: "2025-06-10") {
    seat_number
    status
    travel_date
  }
}
```

## Booking Operations (Requires Authentication)

### 11. Create Booking
```graphql
mutation {
  createBooking(input: {
    schedule_id: 1
    travel_date: "2025-06-10"
    seat_count: 2
    seat_numbers: ["A1", "A2"]
    passenger_details: [
      {
        name: "John Doe"
        phone: "08123456789"
        email: "john@example.com"
      },
      {
        name: "Jane Doe"
        phone: "08123456788"
        email: "jane@example.com"
      }
    ]
  }) {
    id
    schedule {
      id
      route {
        route_name
      }
    }
    travel_date
    seat_count
    seat_numbers
    total_amount
    status
  }
}
```

### 12. Cancel Booking
```graphql
mutation {
  cancelBooking(id: 1) {
    id
    status
  }
}
```

## Payment Operations (Requires Authentication)

### 13. Create Payment
```graphql
mutation {
  createPayment(input: {
    booking_id: 1
    payment_method: "bank_transfer"
    amount: 100000
  }) {
    id
    booking {
      id
    }
    payment_method
    amount
    status
    payment_code
  }
}
```

### 14. Verify Payment (Admin Only)
```graphql
mutation {
  verifyPayment(id: 1, status: CONFIRMED, notes: "Payment verified successfully") {
    id
    status
    admin_notes
  }
}
```

## Review Operations (Requires Authentication)

### 15. Create Review
```graphql
mutation {
  createReview(input: {
    booking_id: 1
    rating: 5
    comment: "Excellent service!"
  }) {
    id
    rating
    comment
    user {
      name
    }
    booking {
      id
    }
  }
}
```

## Complex Queries with Relationships

### 16. Get User with Bookings
```graphql
{
  user(id: 1) {
    id
    name
    email
    bookings {
      id
      travel_date
      seat_count
      total_amount
      status
      schedule {
        route {
          route_name
          origin
          destination
        }
        departure_time
      }
    }
  }
}
```

### 17. Get Route with Schedules
```graphql
{
  route(id: 1) {
    id
    route_name
    origin
    destination
    schedules {
      id
      departure_time
      arrival_time
      price
      total_seats
      available_seats
    }
  }
}
```

## Error Testing

### 18. Test Invalid Query (should return error)
```graphql
{
  nonExistentField
}
```

### 19. Test Invalid Mutation (should return error)
```graphql
mutation {
  nonExistentMutation(input: {})
}
```

### 20. Test Missing Required Fields (should return error)
```graphql
mutation {
  register(input: {
    name: "Test"
    # Missing required email and password
  }) {
    id
  }
}
```

## Testing Instructions

1. Open GraphQL Playground at: http://127.0.0.1:8000/graphql-playground
2. Copy and paste each query/mutation into the left panel
3. Click the play button to execute
4. Check the response in the right panel
5. For authentication-required operations, first run login mutation and use the returned user data
6. For operations requiring existing data, ensure you have routes, schedules, and users in the database

## Expected Results

- Introspection queries should return schema information
- Authentication mutations should work without errors
- Data queries should return existing data or null if no data exists
- Error queries should return appropriate error messages
- All operations should respect authentication requirements
