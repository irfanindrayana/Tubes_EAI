# User Management Service for TransBandung

This service manages user data, authentication, and authorization for the TransBandung system.

## Features
- User registration and login
- JWT authentication
- User profile management
- Role-based authorization (customer vs admin)

## GraphQL Schema

### Types
- `User`: Represents user information
- `AuthPayload`: Authentication response containing JWT token and user info

### Queries
- `user(id: ID!)`: Get user by ID
- `userByUsername(username: String!)`: Get user by username
- `users`: Get all users
- `me`: Get current logged-in user based on JWT token

### Mutations
- `createUser(input: CreateUserInput!)`: Register a new user
- `updateUser(id: ID!, input: UpdateUserInput!)`: Update user information
- `deleteUser(id: ID!)`: Delete a user
- `login(username: String!, password: String!)`: Authenticate user and get JWT token

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
- `DB_NAME`: Database name (default: transbandung_user)
