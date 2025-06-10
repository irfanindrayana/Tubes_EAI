# GraphQL API Documentation - Trans Bandung Bus Ticketing System

## Overview

This document provides comprehensive documentation for all GraphQL Query and Mutation endpoints in the Trans Bandung bus ticketing system. The API is organized into four main services:

1. **User Management Service** - Authentication, user profiles, and account management
2. **Ticketing Service** - Routes, schedules, bookings, and seat management  
3. **Payment Service** - Payment processing, verification, and financial reports
4. **Inbox Service** - Messages, notifications, and communication

## Authentication

All authenticated endpoints require a valid JWT token in the Authorization header:
```
Authorization: Bearer <your-jwt-token>
```

## Data Types

### Enums

```graphql
enum UserRole {
  ADMIN
  KONSUMEN
}

enum Gender {
  LAKI_LAKI
  PEREMPUAN
}

enum BookingStatus {
  PENDING
  CONFIRMED
  CANCELLED
  COMPLETED
}

enum PaymentStatus {
  PENDING
  VERIFIED
  REJECTED
  REFUNDED
}

enum PaymentMethodType {
  BANK_TRANSFER
  E_WALLET
  CASH
}

enum MessageStatus {
  SENT
  READ
  ARCHIVED
}
```

---

## 1. User Management Service

### Queries

#### Get Current User
**Authentication:** Required  
**Role:** Any authenticated user

```graphql
query {
  me {
    id
    name
    email
    role
    phone
    address
    birth_date
    gender
    email_verified_at
    created_at
    updated_at
    profile {
      id
      avatar
      bio
      preferences
    }
  }
}
```

**Example Response:**
```json
{
  "data": {
    "me": {
      "id": "1",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "KONSUMEN",
      "phone": "08123456789",
      "address": "Jl. Asia Afrika No. 123, Bandung",
      "birth_date": "1990-01-15 00:00:00",
      "gender": "LAKI_LAKI",
      "email_verified_at": "2024-01-15 10:30:00",
      "created_at": "2024-01-15 10:30:00",
      "updated_at": "2024-01-15 10:30:00",
      "profile": {
        "id": "1",
        "avatar": "avatars/john-doe.jpg",
        "bio": "Regular commuter from Bandung to Jakarta",
        "preferences": "{\"notifications\": true, \"language\": \"id\"}"
      }
    }
  }
}
```

#### Find User by ID or Email
**Authentication:** Required  
**Role:** Admin only

```graphql
    query GetUser($id: ID) {
    user(id: $id) {
        id
        name
        email
        role
        phone
        address
        birth_date
        gender
        created_at
        updated_at
    }
    }
```

**Variables:**
```json
{
  "id": "1"
}
```

#### List Users with Filters
**Authentication:** Required  
**Role:** Admin only

```graphql
query ListUsers($name: String, $role: UserRole, $first: Int, $page: Int) {
  users(name: $name, role: $role, first: $first, page: $page) {
    data {
      id
      name
      email
      role
      phone
      created_at
    }
    paginatorInfo {
      currentPage
      lastPage
      total
      count
      firstItem
      lastItem
      hasMorePages
    }
  }
}
```

**Variables:**
```json
{
  "name": "%john%",
  "role": "KONSUMEN",
  "first": 10,
  "page": 1
}
```

### Mutations

#### Register New User
**Authentication:** Not required

```graphql
mutation RegisterUser($input: CreateUserInput!) {
  register(input: $input) {
    id
    name
    email
    role
    phone
    address
    birth_date
    gender
    created_at
  }
}
```

**Variables:**
```json
{
  "input": {
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "securePassword123",
    "role": "KONSUMEN",
    "phone": "08234567890",
    "address": "Jl. Braga No. 45, Bandung",
    "birth_date": "1992-03-20",
    "gender": "PEREMPUAN"
  }
}
```

#### Login User
**Authentication:** Not required

```graphql
mutation LoginUser($email: String!, $password: String!) {
  login(email: $email, password: $password) {
    id
    name
    email
    role
    phone
    created_at
  }
}
```

**Variables:**
```json
{
  "email": "jane@example.com",
  "password": "securePassword123"
}
```

#### Update User Information
**Authentication:** Required  
**Role:** Owner or Admin

```graphql
mutation UpdateUser($id: ID!, $input: UpdateUserInput!) {
  updateUser(id: $id, input: $input) {
    id
    name
    phone
    address
    birth_date
    gender
    updated_at
  }
}
```

**Variables:**
```json
{
  "id": "1",
  "input": {
    "name": "Jane Smith Updated",
    "phone": "08234567891",
    "address": "Jl. Braga No. 47, Bandung",
    "birth_date": "1992-03-20",
    "gender": "PEREMPUAN"
  }
}
```

#### Logout User
**Authentication:** Required

```graphql
mutation {
  logout
}
```

#### Delete User
**Authentication:** Required  
**Role:** Admin only

```graphql
mutation DeleteUser($id: ID!) {
  deleteUser(id: $id) {
    id
    name
    email
  }
}
```

**Variables:**
```json
{
  "id": "5"
}
```

---

## 2. Ticketing Service

### Queries

#### Get All Routes
**Authentication:** Not required

```graphql
query GetRoutes($is_active: Boolean, $first: Int, $page: Int) {
  routes(is_active: $is_active, first: $first, page: $page) {
    data {
      id
      route_name
      origin
      destination
      stops
      distance
      estimated_duration
      base_price
      is_active
      created_at
      schedules {
        id
        departure_time
        arrival_time
        price
        available_seats
      }
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "is_active": true,
  "first": 10,
  "page": 1
}
```

#### Find Single Route
**Authentication:** Not required

```graphql
query GetRoute($id: ID!) {
  route(id: $id) {
    id
    route_name
    origin
    destination
    stops
    distance
    estimated_duration
    base_price
    is_active
    schedules {
      id
      departure_time
      arrival_time
      total_seats
      available_seats
      price
      is_active
    }
  }
}
```

**Variables:**
```json
{
  "id": "1"
}
```

#### Get Schedules
**Authentication:** Not required

```graphql
query GetSchedules($route_id: ID, $is_active: Boolean, $departure_date: DateTime, $first: Int, $page: Int) {
  schedules(
    route_id: $route_id, 
    is_active: $is_active, 
    departure_date: $departure_date,
    first: $first,
    page: $page
  ) {
    data {
      id
      route_id
      departure_time
      arrival_time
      total_seats
      available_seats
      price
      is_active
      route {
        id
        route_name
        origin
        destination
      }
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "route_id": "1",
  "is_active": true,
  "departure_date": "2024-01-20 00:00:00",
  "first": 10,
  "page": 1
}
```

#### Get Available Seats
**Authentication:** Not required

```graphql
query GetSeats($schedule_id: ID!, $is_available: Boolean) {
  seats(schedule_id: $schedule_id, is_available: $is_available) {
    id
    schedule_id
    seat_number
    travel_date
    status
    booking_id
    is_available
    schedule {
      id
      departure_time
      arrival_time
      route {
        route_name
        origin
        destination
      }
    }
  }
}
```

**Variables:**
```json
{
  "schedule_id": "1",
  "is_available": true
}
```

#### Get User Bookings
**Authentication:** Required

```graphql
query GetBookings($user_id: ID, $status: BookingStatus, $first: Int, $page: Int) {
  bookings(user_id: $user_id, status: $status, first: $first, page: $page) {
    data {
      id
      booking_code
      travel_date
      seat_count
      seat_numbers
      passenger_details
      total_amount
      status
      booking_date
      schedule {
        id
        departure_time
        arrival_time
        route {
          route_name
          origin
          destination
        }
      }
      payment {
        id
        payment_code
        status
        amount
        payment_method
      }
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "user_id": "1",
  "status": "CONFIRMED",
  "first": 10,
  "page": 1
}
```

#### Find Single Booking
**Authentication:** Required

```graphql
query GetBooking($id: ID, $booking_code: String) {
  booking(id: $id, booking_code: $booking_code) {
    id
    booking_code
    travel_date
    seat_count
    seat_numbers
    passenger_details
    total_amount
    status
    booking_date
    user {
      id
      name
      email
      phone
    }
    schedule {
      id
      departure_time
      arrival_time
      route {
        route_name
        origin
        destination
      }
    }
    payment {
      id
      payment_code
      status
      amount
      payment_method
      proof_image
    }
  }
}
```

**Variables:**
```json
{
  "booking_code": "TB240120001"
}
```

### Mutations

#### Create New Booking
**Authentication:** Required

```graphql
mutation CreateBooking($input: CreateBookingInput!) {
  createBooking(input: $input) {
    id
    booking_code
    travel_date
    seat_count
    seat_numbers
    passenger_details
    total_amount
    status
    booking_date
    schedule {
      id
      departure_time
      arrival_time
      route {
        route_name
        origin
        destination
      }
    }
  }
}
```

**Variables:**
```json
{
  "input": {
    "schedule_id": "1",
    "seat_id": "15",
    "travel_date": "2024-01-25",
    "passenger_name": "John Doe",
    "passenger_phone": "08123456789"
  }
}
```

#### Cancel Booking
**Authentication:** Required

```graphql
mutation CancelBooking($id: ID!) {
  cancelBooking(id: $id) {
    id
    booking_code
    status
    total_amount
    schedule {
      departure_time
      route {
        route_name
        origin
        destination
      }
    }
  }
}
```

**Variables:**
```json
{
  "id": "5"
}
```

---

## 3. Payment Service

### Queries

#### Get Payment Methods
**Authentication:** Not required

```graphql
query GetPaymentMethods($is_active: Boolean) {
  paymentMethods(is_active: $is_active) {
    id
    name
    type
    account_number
    account_name
    instructions
    is_active
  }
}
```

**Variables:**
```json
{
  "is_active": true
}
```

#### Get User Payments
**Authentication:** Required

```graphql
query GetPayments($user_id: ID, $status: PaymentStatus, $first: Int, $page: Int) {
  payments(user_id: $user_id, status: $status, first: $first, page: $page) {
    data {
      id
      payment_code
      payment_method
      amount
      status
      proof_image
      verified_at
      admin_notes
      created_at
      booking {
        id
        booking_code
        travel_date
        schedule {
          departure_time
          route {
            route_name
            origin
            destination
          }
        }
      }
      verifiedBy {
        id
        name
      }
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "user_id": "1",
  "status": "VERIFIED",
  "first": 10,
  "page": 1
}
```

#### Find Single Payment
**Authentication:** Required

```graphql
query GetPayment($id: ID!) {
  payment(id: $id) {
    id
    payment_code
    payment_method
    amount
    status
    proof_image
    verified_by
    verified_at
    admin_notes
    created_at
    user {
      id
      name
      email
    }
    booking {
      id
      booking_code
      travel_date
      total_amount
      schedule {
        departure_time
        arrival_time
        route {
          route_name
          origin
          destination
        }
      }
    }
    verifiedBy {
      id
      name
    }
  }
}
```

**Variables:**
```json
{
  "id": "3"
}
```

#### Get Financial Reports
**Authentication:** Required  
**Role:** Admin only

```graphql
query GetFinancialReports($first: Int, $page: Int) {
  financialReports(first: $first, page: $page) {
    data {
      id
      report_date
      total_revenue
      total_bookings
      total_refunds
      report_data
      created_at
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "first": 10,
  "page": 1
}
```

### Mutations

#### Create Payment
**Authentication:** Required

```graphql
mutation CreatePayment($input: CreatePaymentInput!) {
  createPayment(input: $input) {
    id
    payment_code
    payment_method
    amount
    status
    created_at
    booking {
      id
      booking_code
      total_amount
      schedule {
        departure_time
        route {
          route_name
          origin
          destination
        }
      }
    }
  }
}
```

**Variables (with file upload):**
```json
{
  "input": {
    "booking_id": "5",
    "payment_method_id": "1"
  }
}
```

**Variables for file upload (using multipart/form-data):**
```
operations: {"query": "mutation CreatePayment($input: CreatePaymentInput!) { createPayment(input: $input) { id payment_code status } }", "variables": {"input": {"booking_id": "5", "payment_method_id": "1", "payment_proof": null}}}
map: {"0": ["variables.input.payment_proof"]}
0: [binary file data]
```

#### Verify Payment
**Authentication:** Required  
**Role:** Admin only

```graphql
mutation VerifyPayment($id: ID!, $status: PaymentStatus!, $notes: String) {
  verifyPayment(id: $id, status: $status, notes: $notes) {
    id
    payment_code
    status
    verified_at
    admin_notes
    verifiedBy {
      id
      name
    }
    booking {
      id
      booking_code
      user {
        name
        email
      }
    }
  }
}
```

**Variables:**
```json
{
  "id": "3",
  "status": "VERIFIED",
  "notes": "Payment verified successfully. Bank transfer confirmed."
}
```

---

## 4. Inbox Service

### Queries

#### Get User Messages
**Authentication:** Required

```graphql
query GetMessages($recipient_id: ID, $status: MessageStatus, $first: Int, $page: Int) {
  messages(recipient_id: $recipient_id, status: $status, first: $first, page: $page) {
    data {
      id
      subject
      body
      status
      created_at
      sender {
        id
        name
        email
      }
      recipients {
        id
        is_read
        read_at
        recipient {
          id
          name
        }
      }
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "recipient_id": "1",
  "status": "SENT",
  "first": 10,
  "page": 1
}
```

#### Find Single Message
**Authentication:** Required

```graphql
query GetMessage($id: ID!) {
  message(id: $id) {
    id
    subject
    body
    status
    created_at
    updated_at
    sender {
      id
      name
      email
    }
    recipients {
      id
      is_read
      read_at
      recipient {
        id
        name
        email
      }
    }
  }
}
```

**Variables:**
```json
{
  "id": "2"
}
```

#### Get User Notifications
**Authentication:** Required

```graphql
query GetNotifications($user_id: ID, $is_read: Boolean, $first: Int, $page: Int) {
  notifications(user_id: $user_id, is_read: $is_read, first: $first, page: $page) {
    data {
      id
      title
      message
      type
      is_read
      read_at
      created_at
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "user_id": "1",
  "is_read": false,
  "first": 10,
  "page": 1
}
```

### Mutations

#### Create Message
**Authentication:** Required

```graphql
mutation CreateMessage($input: CreateMessageInput!) {
  createMessage(input: $input) {
    id
    subject
    body
    status
    created_at
    sender {
      id
      name
    }
    recipients {
      id
      is_read
      recipient {
        id
        name
        email
      }
    }
  }
}
```

**Variables:**
```json
{
  "input": {
    "recipient_ids": ["2", "3"],
    "subject": "Booking Confirmation",
    "body": "Your booking has been confirmed. Please check your booking details and proceed with payment."
  }
}
```

#### Mark Message as Read
**Authentication:** Required

```graphql
mutation MarkMessageAsRead($message_id: ID!) {
  markMessageAsRead(message_id: $message_id) {
    id
    is_read
    read_at
    message {
      id
      subject
    }
    recipient {
      id
      name
    }
  }
}
```

**Variables:**
```json
{
  "message_id": "2"
}
```

#### Mark Notification as Read
**Authentication:** Required

```graphql
mutation MarkNotificationAsRead($id: ID!) {
  markNotificationAsRead(id: $id) {
    id
    title
    message
    is_read
    read_at
  }
}
```

**Variables:**
```json
{
  "id": "5"
}
```

---

## 5. Review & Complaint Service

### Queries

#### Get Reviews
**Authentication:** Not required

```graphql
query GetReviews($booking_id: ID, $first: Int, $page: Int) {
  reviews(booking_id: $booking_id, first: $first, page: $page) {
    data {
      id
      rating
      comment
      created_at
      user {
        id
        name
      }
      booking {
        id
        booking_code
        schedule {
          route {
            route_name
            origin
            destination
          }
        }
      }
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "booking_id": "3",
  "first": 10,
  "page": 1
}
```

#### Get User Complaints
**Authentication:** Required

```graphql
query GetComplaints($user_id: ID, $status: String, $first: Int, $page: Int) {
  complaints(user_id: $user_id, status: $status, first: $first, page: $page) {
    data {
      id
      subject
      description
      status
      priority
      created_at
      user {
        id
        name
        email
      }
      booking {
        id
        booking_code
        schedule {
          route {
            route_name
          }
        }
      }
      adminResponses {
        id
        response
        created_at
        admin {
          id
          name
        }
      }
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}
```

**Variables:**
```json
{
  "user_id": "1",
  "status": "open",
  "first": 10,
  "page": 1
}
```

#### Find Single Complaint
**Authentication:** Required

```graphql
query GetComplaint($id: ID!) {
  complaint(id: $id) {
    id
    subject
    description
    status
    priority
    created_at
    updated_at
    user {
      id
      name
      email
      phone
    }
    booking {
      id
      booking_code
      travel_date
      schedule {
        departure_time
        route {
          route_name
          origin
          destination
        }
      }
    }
    adminResponses {
      id
      response
      created_at
      admin {
        id
        name
      }
    }
  }
}
```

**Variables:**
```json
{
  "id": "2"
}
```

### Mutations

#### Create Review
**Authentication:** Required

```graphql
mutation CreateReview($input: CreateReviewInput!) {
  createReview(input: $input) {
    id
    rating
    comment
    created_at
    user {
      id
      name
    }
    booking {
      id
      booking_code
      schedule {
        route {
          route_name
          origin
          destination
        }
      }
    }
  }
}
```

**Variables:**
```json
{
  "input": {
    "booking_id": "3",
    "rating": 5,
    "comment": "Excellent service! The bus was on time and comfortable. Highly recommended!"
  }
}
```

#### Update Review
**Authentication:** Required

```graphql
mutation UpdateReview($id: ID!, $rating: Int, $comment: String) {
  updateReview(id: $id, rating: $rating, comment: $comment) {
    id
    rating
    comment
    updated_at
    user {
      id
      name
    }
    booking {
      id
      booking_code
    }
  }
}
```

**Variables:**
```json
{
  "id": "2",
  "rating": 4,
  "comment": "Good service overall, but could improve on punctuality."
}
```

#### Delete Review
**Authentication:** Required

```graphql
mutation DeleteReview($id: ID!) {
  deleteReview(id: $id) {
    id
    rating
    comment
    user {
      name
    }
    booking {
      booking_code
    }
  }
}
```

**Variables:**
```json
{
  "id": "2"
}
```

#### Create Complaint
**Authentication:** Required

```graphql
mutation CreateComplaint($input: CreateComplaintInput!) {
  createComplaint(input: $input) {
    id
    subject
    description
    status
    priority
    created_at
    user {
      id
      name
      email
    }
    booking {
      id
      booking_code
      schedule {
        route {
          route_name
          origin
          destination
        }
      }
    }
  }
}
```

**Variables:**
```json
{
  "input": {
    "booking_id": "5",
    "subject": "Bus Delay Issue",
    "description": "The bus was delayed for over 2 hours without any prior notification. This caused significant inconvenience as I missed my important appointment."
  }
}
```

#### Respond to Complaint
**Authentication:** Required  
**Role:** Admin only

```graphql
mutation RespondToComplaint($complaint_id: ID!, $response: String!) {
  respondToComplaint(complaint_id: $complaint_id, response: $response) {
    id
    response
    created_at
    admin {
      id
      name
    }
    complaint {
      id
      subject
      status
      user {
        name
        email
      }
    }
  }
}
```

**Variables:**
```json
{
  "complaint_id": "2",
  "response": "We sincerely apologize for the inconvenience caused by the bus delay. This was due to unexpected traffic conditions. We are implementing better traffic monitoring systems to prevent such delays in the future. As compensation, we are providing you with a 50% discount on your next booking."
}
```

---

## Error Handling

### Common Error Responses

#### Authentication Error
```json
{
  "errors": [
    {
      "message": "Unauthenticated.",
      "extensions": {
        "category": "authentication"
      }
    }
  ]
}
```

#### Authorization Error
```json
{
  "errors": [
    {
      "message": "This action is unauthorized.",
      "extensions": {
        "category": "authorization"
      }
    }
  ]
}
```

#### Validation Error
```json
{
  "errors": [
    {
      "message": "Validation failed for the field [email].",
      "extensions": {
        "validation": {
          "email": [
            "The email field is required.",
            "The email must be a valid email address."
          ]
        },
        "category": "validation"
      }
    }
  ]
}
```

#### Not Found Error
```json
{
  "errors": [
    {
      "message": "No query results for model [App\\Models\\User] 999",
      "extensions": {
        "category": "graphql"
      }
    }
  ]
}
```

### Field-Level Validation Rules

- **Email fields**: Must be valid email format and unique where specified
- **Password fields**: Minimum 8 characters required
- **Phone fields**: Maximum 20 characters
- **Rating fields**: Integer between 1 and 5
- **File uploads**: Supported formats for payment proofs (jpg, png, pdf)
- **Date fields**: Format `Y-m-d H:i:s` (e.g., `2024-01-15 10:30:00`)

---

## Testing Tips

1. **Use GraphQL Playground or GraphiQL** for interactive testing
2. **Include proper Authorization headers** for authenticated endpoints
3. **Test pagination** by varying `first` and `page` parameters
4. **Test file uploads** using multipart/form-data format
5. **Validate error responses** by providing invalid inputs
6. **Test role-based access** with different user roles (admin vs konsumen)

## Rate Limiting

- **Authentication endpoints**: Limited to prevent brute force attacks
- **File upload endpoints**: Limited by file size and request frequency
- **Query endpoints**: Standard rate limiting applies to prevent abuse

---

*This documentation covers all available GraphQL endpoints in the Trans Bandung bus ticketing system. For additional support or questions, please contact the development team.*
