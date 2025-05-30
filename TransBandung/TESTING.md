# TransBandung Testing Guide

This guide provides instructions for testing the integration between the TransBandung frontend and backend services.

## Prerequisites

Before starting the tests, make sure all services are running:

```bash
docker-compose up -d
```

## Testing Frontend-Backend Integration

### 1. Authentication Testing

#### User Registration
1. Open the TransBandung homepage at `http://localhost:3000`
2. Click the "Masuk" button
3. Click "Daftar" to open the registration form
4. Fill in all required fields with valid data:
   - Nama Lengkap: Test User
   - Username: testuser
   - Email: test@example.com
   - Nomor HP: 08123456789
   - Password: password123
   - Konfirmasi Password: password123
5. Click "Daftar" button
6. Expected result: Success message and redirect to login form

#### User Login
1. On the login form, enter:
   - Username: testuser
   - Password: password123
2. Click "Masuk" button
3. Expected result: Success message and user is logged in (navbar changes to show Profile link and Logout button)

### 2. Route Management Testing

#### View Routes (Customer)
1. While logged in as a customer, click "Rute" in the navbar
2. Expected result: List of available routes is displayed

#### Manage Routes (Admin)
1. Login with an admin account:
   - Username: admin
   - Password: admin123
2. Click "Rute" in the navbar
3. Expected result: Admin controls for routes are displayed (Add, Edit, Delete buttons)
4. Test adding a route:
   - Click "Tambah Rute"
   - Fill the form with test data
   - Click "Simpan"
   - Expected result: New route appears in the list

### 3. Booking Testing

1. Login with a customer account
2. Click "Pesan Tiket" in the navbar
3. Follow the multi-step booking process:
   - Step 1: Select route and date
   - Step 2: Select schedule and passenger count
   - Step 3: Select seats and enter passenger details
   - Step 4: Choose payment method (select bank transfer)
4. Upload a test image as payment proof
5. Click "Konfirmasi Pemesanan"
6. Expected result: Booking confirmation is displayed with booking details

### 4. Review Testing

1. Login with a customer account
2. Click "Ulasan" in the navbar
3. Find a previous booking and click "Beri Ulasan"
4. Enter a rating and comment
5. Click "Kirim Ulasan"
6. Expected result: New review is displayed in the reviews list

### 5. Profile Testing

1. Login with a customer account
2. Click "Profil" in the navbar
3. Check that user information is correctly displayed
4. Check the booking history section
5. Click on a booking to view details
6. Expected result: Booking details are displayed correctly

## Error Handling Testing

Test various error scenarios to ensure the application handles them gracefully:

1. **Invalid Login**: Try logging in with incorrect credentials
   - Expected: Clear error message displayed

2. **Form Validation**: Submit forms with missing or invalid data
   - Expected: Validation errors displayed next to relevant fields

3. **Network Error**: Temporarily disconnect from the network and try operations
   - Expected: Appropriate error messages displayed

4. **Unauthorized Access**: Try to access admin features as a regular user
   - Expected: Access denied message or redirect

## Mobile Responsiveness Testing

Test the application on different screen sizes:

1. Desktop browser (1920x1080)
2. Tablet (768x1024)
3. Mobile (375x812)

For each device size, check:
- Navigation menu displays correctly
- Forms are usable
- Content is readable
- No horizontal scrolling required

## Performance Testing

Measure and record:
- Page load time
- Time for forms to submit
- Response time for API requests

## Known Issues

Current known issues that are being addressed:
1. On mobile devices, seat selection interface may be difficult to use on very small screens
2. Bank transfer upload might fail with large image files
3. API requests might timeout during high server load

Report any additional issues using the GitHub Issues tracker.
