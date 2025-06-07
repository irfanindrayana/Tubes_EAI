# Struktur Database Microservice

Dokumen ini menjelaskan struktur database yang benar untuk setiap microservice dalam sistem Transbandung.

## 1. User Management Microservice (transbandung_users)

Tabel-tabel yang seharusnya ada:
- `users` - Data pengguna sistem
- `user_profiles` - Profil detail pengguna
- `password_reset_tokens` - Token reset password
- `sessions` - Sesi login pengguna

## 2. Ticketing Microservice (transbandung_ticketing)

Tabel-tabel yang seharusnya ada:
- `routes` - Rute perjalanan
- `schedules` - Jadwal perjalanan
- `seats` - Data tempat duduk
- `bookings` - Reservasi/pemesanan tiket

## 3. Payment Microservice (transbandung_payments)

Tabel-tabel yang seharusnya ada:
- `payment_methods` - Metode pembayaran yang tersedia
- `payments` - Transaksi pembayaran

## 4. Review Microservice (transbandung_reviews)

Tabel-tabel yang seharusnya ada:
- `reviews` - Ulasan pengguna
- `complaints` - Keluhan pelanggan

## 5. Inbox Microservice (transbandung_inbox)

Tabel-tabel yang seharusnya ada:
- `messages` - Pesan
- `message_recipients` - Penerima pesan
- `notifications` - Notifikasi

## Masalah Sebelumnya

Sebelumnya, setiap database microservice berisi semua tabel yang tidak relevan untuk layanan tersebut. Hal ini menyebabkan:

1. Redundansi data
2. Potensi inkonsistensi data
3. Pelanggaran prinsip otonomi microservice
4. Overhead pemeliharaan

## Solusi

Script reset database yang telah dibuat (`reset_all_databases.ps1` atau `reset_all_databases.bat`) akan memastikan:

1. Semua database dihapus dan dibuat ulang
2. Hanya tabel yang relevan yang dibuat di setiap database microservice
3. Data awal (seeder) dimasukkan ke dalam tabel-tabel tersebut

## Cara Menjalankan Reset Database

### Menggunakan PowerShell:

```powershell
powershell -ExecutionPolicy Bypass -File reset_all_databases.ps1
```

### Menggunakan Command Prompt:

```cmd
reset_all_databases.bat
```

Setelah menjalankan script, verifikasi struktur database menggunakan HeidiSQL atau phpMyAdmin untuk memastikan setiap database hanya berisi tabel-tabel yang relevan.
