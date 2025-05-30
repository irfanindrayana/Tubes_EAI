# Review Rating Service for TransBandung

This service manages user reviews and ratings for the TransBandung system.

## Features
- Create, update, and delete reviews
- Get reviews by user or booking
- Calculate average ratings

## GraphQL Schema

### Types
- `Review`: Represents a user review and rating
- `User`: Represents a user (fetched from User Service)
- `Booking`: Represents a booking (fetched from Booking Service)

### Queries
- `review(id: ID!)`: Get review by ID
- `reviews`: Get all reviews
- `reviewsByUser(userId: ID!)`: Get reviews by a specific user
- `reviewsByBookingId(bookingId: ID!)`: Get reviews for a specific booking
- `averageRating`: Get overall average rating

### Mutations
- `createReview(input: CreateReviewInput!)`: Create a new review
- `updateReview(id: ID!, input: UpdateReviewInput!)`: Update a review
- `deleteReview(id: ID!)`: Delete a review

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
- `DB_NAME`: Database name (default: transbandung_review)
- `USER_SERVICE_URL`: URL to the User Service GraphQL endpoint
- `BOOKING_SERVICE_URL`: URL to the Booking Service GraphQL endpoint
