# TransBandung Microservices

TransBandung adalah sistem transportasi umum bus yang diimplementasikan dengan arsitektur microservices. Sistem ini terdiri dari beberapa layanan independen yang saling terintegrasi menggunakan GraphQL.

## Arsitektur Sistem

Sistem ini terdiri dari 5 layanan utama dan 1 API Gateway:

1. **User Management Service** - Mengelola data pengguna dan admin, otentikasi, dan otorisasi.
2. **Booking Service** - Mengelola pemesanan tiket.
3. **Route Schedule Service** - Mengelola rute perjalanan dan jadwal keberangkatan.
4. **Review Rating Service** - Mengelola ulasan dan penilaian dari pengguna.
5. **Payment Service** - Menangani proses pembayaran dan transaksi keuangan.
6. **API Gateway** - Menyediakan single entry point untuk semua layanan dengan GraphQL.
7. **Frontend** - Antarmuka pengguna dengan HTML, CSS, dan JavaScript.

## Teknologi yang Digunakan

- **Docker** - Containerisasi untuk setiap layanan
- **Node.js** - Framework backend untuk setiap mikroservis
- **GraphQL** - API layer untuk komunikasi antar layanan
- **MySQL** - Database untuk semua layanan
- **Express** - Web framework untuk frontend dan API Gateway
- **HTML, CSS, JavaScript** - Frontend development

## Cara Menjalankan Sistem

### Prerequisites

- Docker dan Docker Compose
- Node.js (untuk pengembangan lokal)
- MySQL (akan dijalankan dalam container)

### Langkah-langkah

1. **Clone repository**

2. **Jalankan semua layanan dengan Docker Compose**
   ```
   docker-compose up -d
   ```

   Atau dengan script build khusus (untuk Windows):
   ```
   .\build-containers.ps1
   ```

3. **Akses layanan**
   - Frontend: http://localhost
   - API Gateway: http://localhost:4000/graphql
   - User Service: http://localhost:4001/graphql
   - Booking Service: http://localhost:4002/graphql
   - Route Service: http://localhost:4003/graphql
   - Review Service: http://localhost:4004/graphql
   - Payment Service: http://localhost:4005/graphql

4. **Akses database**
   - Host: localhost
   - Port: 3309
   - User: root
   - Password: (kosong)

## Struktur Project

```
.
├── api-gateway/             # API Gateway service
├── booking-service/         # Booking management service
├── frontend/                # Express.js frontend
│   ├── public/              # Static files (HTML, CSS, JS)
│   └── src/                 # Frontend server code
├── init-db/                 # Database initialization scripts
├── payment-service/         # Payment processing service
├── review-service/          # Review and rating service
├── route-service/           # Route and schedule service
├── user-service/            # User management service
├── build-containers.ps1     # Script for building containers
├── docker-compose.yml       # Docker compose configuration
└── README.md                # This file
```

## Pengembangan Lokal

Untuk pengembangan lokal tanpa Docker, ikuti langkah berikut untuk setiap layanan:

1. Masuk ke direktori layanan (misal: `user-service`)
2. Install dependencies
   ```
   npm install
   ```
3. Jalankan layanan
   ```
   npm start
   ```

Untuk frontend:

1. Masuk ke direktori `frontend`
2. Install dependencies
   ```
   npm install
   ```
3. Jalankan frontend server
   ```
   npm start
   ```
   Frontend akan tersedia di http://localhost:3000

## Troubleshooting

Jika mengalami masalah saat menggunakan Docker atau menjalankan layanan, silakan lihat [TROUBLESHOOTING.md](TROUBLESHOOTING.md) untuk panduan pemecahan masalah detail.

## Testing

Untuk menjalankan tes API:

1. Install dependencies
   ```
   npm install --prefix ./api-test-package.json
   ```

2. Jalankan tes
   ```
   node api-test.js
   ```

Lihat [TESTING.md](TESTING.md) untuk panduan testing lebih detail.

## Contoh Query GraphQL

Berikut adalah contoh-contoh query GraphQL untuk berinteraksi dengan layanan-layanan TransBandung.

### User Service

```graphql
# Query untuk mendapatkan data pengguna
query GetUser {
  user(id: 1) {
    id
    username
    email
    fullName
  }
}

# Mutation untuk mendaftarkan pengguna baru
mutation RegisterUser {
  createUser(
    input: {
      username: "newuser"
      password: "password123"
      email: "newuser@example.com"
      fullName: "New User"
      phoneNumber: "081234567893"
    }
  ) {
    id
    username
    email
  }
}

# Mutation untuk login
mutation Login {
  login(username: "johndoe", password: "password123") {
    token
    user {
      id
      username
    }
  }
}
```

### Route Service

```graphql
# Query untuk mendapatkan semua rute
query GetRoutes {
  routes {
    id
    name
    startPoint
    endPoint
    distance
    schedules {
      id
      departureTime
      arrivalTime
      price
    }
  }
}

# Query untuk mencari jadwal berdasarkan rute dan hari
query FindSchedules {
  schedules(routeId: 1, dayOfWeek: "Monday") {
    id
    departureTime
    arrivalTime
    busNumber
    capacity
    price
    dayOfWeek
  }
}

# Mutation untuk membuat rute baru
mutation CreateRoute {
  createRoute(
    input: {
      name: "Rute Cicaheum - Elang"
      startPoint: "Terminal Cicaheum"
      endPoint: "Terminal Elang"
      distance: 15.5
    }
  ) {
    id
    name
    startPoint
    endPoint
  }
}
```

### Booking Service

```graphql
# Query untuk mendapatkan semua pemesanan
query GetAllBookings {
  bookings {
    id
    bookingDate
    seatNumber
    status
  }
}

# Mutation untuk membuat pemesanan tiket
mutation CreateBooking {
  createBooking(
    input: {
      userId: 2
      scheduleId: 1
      bookingDate: "2025-06-10"
      seatNumber: 12
    }
  ) {
    id
    status
    seatNumber
  }
}

# Query untuk mendapatkan pemesanan pengguna
query GetUserBookings {
  bookingsByUser(userId: 2) {
    id
    bookingDate
    seatNumber
    status
    schedule {
      id
      departureTime
      arrivalTime
      route {
        name
        startPoint
        endPoint
      }
    }
  }
}
```

### Payment Service

```graphql
# Query untuk mendapatkan semua pembayaran
query GetAllPayments {
  payments {
    id
    amount
    paymentMethod
    status
    paymentDate
  }
}

# Mutation untuk membuat pembayaran
mutation CreatePayment {
  createPayment(
    input: {
      bookingId: 3
      amount: 12000
      paymentMethod: "credit_card"
    }
  ) {
    id
    status
    transactionId
  }
}

# Query untuk mendapatkan pembayaran berdasarkan booking ID
query GetPayment {
  paymentByBookingId(bookingId: 1) {
    id
    amount
    paymentMethod
    status
    paymentDate
  }
}
```

### Review Service

```graphql
# Query untuk mendapatkan semua ulasan
query GetAllReviews {
  reviews {
    id
    rating
    comment
    createdAt
    user {
      username
    }
  }
}

# Mutation untuk menambahkan review
mutation AddReview {
  createReview(
    input: {
      userId: 2
      bookingId: 1
      rating: 4
      comment: "Bus was clean and comfortable"
    }
  ) {
    id
    rating
    comment
  }
}

# Query untuk mendapatkan ulasan berdasarkan booking ID
query GetReviews {
  reviewsByBookingId(bookingId: 1) {
    id
    rating
    comment
    user {
      username
    }
  }
}
```

### Contoh Integrasi Antar Layanan via API Gateway

```graphql
# Mendapatkan data booking beserta detail rute dan pembayaran
query GetBookingDetails {
  booking(id: 1) {
    id
    bookingDate
    status
    user {
      username
      email
    }
    schedule {
      departureTime
      arrivalTime
      route {
        name
        startPoint
        endPoint
      }
    }
    payment {
      amount
      status
      paymentMethod
    }
    review {
      rating
      comment
    }
  }
}

# Mendapatkan profil pengguna lengkap dengan pemesanan dan ulasan
query GetUserProfile {
  user(id: 2) {
    id
    username
    email
    fullName
    bookings {
      id
      bookingDate
      status
      schedule {
        departureTime
        route {
          name
        }
      }
      payment {
        amount
        status
      }
    }
    reviews {
      id
      rating
      comment
      booking {
        schedule {
          route {
            name
          }
        }
      }
    }
  }
}
```

## Maintainer

- TransBandung Development Team
