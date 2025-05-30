# Booking Service for TransBandung

This service manages ticket booking and seat reservations for the TransBandung system.

## Features
- Create, update, and cancel bookings
- Check seat availability
- Get booking information for users

## GraphQL Schema

### Types
- `Booking`: Represents a ticket booking
- `Schedule`: Represents a bus schedule (fetched from Route Service)
- `Route`: Represents a bus route (fetched from Route Service)
- `User`: Represents a user (fetched from User Service)

### Queries
- `booking(id: ID!)`: Get booking by ID
- `bookings`: Get all bookings
- `bookingsByUser(userId: ID!)`: Get bookings for a specific user
- `bookingsBySchedule(scheduleId: ID!)`: Get bookings for a specific schedule
- `isSeatAvailable(scheduleId: ID!, bookingDate: String!, seatNumber: Int!)`: Check if a specific seat is available
- `availableSeats(scheduleId: ID!, bookingDate: String!)`: Get all available seats for a schedule on a specific date

### Mutations
- `createBooking(input: CreateBookingInput!)`: Create a new booking
- `updateBooking(id: ID!, input: UpdateBookingInput!)`: Update a booking
- `cancelBooking(id: ID!)`: Cancel a booking
- `completeBooking(id: ID!)`: Mark a booking as completed

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
- `DB_NAME`: Database name (default: transbandung_booking)
- `USER_SERVICE_URL`: URL to the User Service GraphQL endpoint
- `ROUTE_SERVICE_URL`: URL to the Route Service GraphQL endpoint
