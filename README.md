# Dokumen Perencanaan Proyek UAS Komputasi Awan
# PixieCloud: Magical IaaS Simulation Platform

PixieCloud adalah prototipe portal penyedia layanan infrastruktur awan (*Infrastructure as a Service / IaaS*) berbasis web dengan tema *cottagecore fairytale*. Sistem memungkinkan pengguna untuk mendaftar, melihat dashboard, menyewa ruang penyimpanan virtual, serta memperoleh kredensial akses cloud menggunakan MiniStack sebagai mesin simulasi layanan AWS.

Tema fantasi digunakan sebagai identitas visual dan pengalaman pengguna, sementara istilah teknis utama seperti bucket, storage, access key, dan secret key tetap dipertahankan agar sistem tetap mudah dipahami dan sesuai konteks komputasi awan.

---

# 1. Tujuan Sistem

PixieCloud dirancang untuk memenuhi kebutuhan simulasi platform IaaS dengan fitur utama berikut:

- Registrasi dan autentikasi pengguna
- Dashboard pemantauan kuota penyimpanan
- Penyewaan storage berbasis bucket
- Pembuatan bucket terisolasi otomatis menggunakan MiniStack
- Pembuatan Access Key dan Secret Key
- Pencatatan aktivitas pengguna
- Simulasi pembatasan kuota layanan cloud

---

# 2. Arsitektur Sistem

## Diagram Arsitektur Sederhana

```text
User
   ↓
Frontend Web (React / Blade / Tailwind)
   ↓
Backend API (Express / Laravel)
   ↓
Database (PostgreSQL / MySQL)
   ↓
MiniStack API
   ↓
Bucket & Cloud Credentials
```

## Alur Utama Sistem

```text
Register User
   ↓
Backend membuat akun user
   ↓
MiniStack membuat Access Key & Secret Key
   ↓
Sistem membuat bucket default
   ↓
Data disimpan ke database
   ↓
User masuk ke dashboard
```

---

# 3. Arsitektur Data (Database Schema)

Database menggunakan PostgreSQL atau MySQL dengan struktur tabel berikut:

## Tabel `users`

Menyimpan informasi akun pengguna dan kredensial cloud.

| Field | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary Key, Auto Increment |
| username | VARCHAR | Nama pengguna |
| email | VARCHAR | Unique |
| password | VARCHAR | Password yang telah di-hash |
| ministack_access_key | VARCHAR | Nullable |
| ministack_secret_key | VARCHAR | Nullable |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu pembaruan |

---

## Tabel `subscriptions`

Menyimpan paket layanan aktif pengguna.

| Field | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary Key |
| user_id | INT | Foreign Key → users.id |
| plan_name | ENUM | Nama paket |
| max_buckets | INT | Maksimal jumlah bucket |
| max_storage_mb | INT | Total kapasitas maksimal |
| status | ENUM | Active / Expired |
| start_date | TIMESTAMP | Tanggal mulai |
| end_date | TIMESTAMP | Tanggal berakhir |

---

## Tabel `buckets`

Mencatat bucket yang dibuat untuk setiap pengguna.

| Field | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary Key |
| user_id | INT | Foreign Key → users.id |
| bucket_name | VARCHAR | Unique |
| allocated_size_mb | INT | Simulasi kapasitas bucket |
| created_at | TIMESTAMP | Waktu pembuatan |

Format penamaan bucket:

```text
pixie-[username]-[random]
```

Contoh:

```text
pixie-elaina-a1b2
```

---

## Tabel `activity_logs`

Mencatat aktivitas utama sistem.

| Field | Tipe | Keterangan |
|---|---|---|
| id | INT | Primary Key |
| user_id | INT | Foreign Key → users.id |
| activity | VARCHAR | Deskripsi aktivitas |
| ip_address | VARCHAR | IP pengguna |
| created_at | TIMESTAMP | Waktu aktivitas |

Contoh log:

```text
"Membuat bucket baru: pixie-elaina-a1b2"
```

---

# 4. Detail Alur Sistem & Integrasi MiniStack

## Alur 1 — Registrasi dan Provisioning Otomatis

Untuk memenuhi requirement pembuatan bucket otomatis:

1. User mengisi form registrasi
2. Backend menyimpan data pengguna ke database
3. Backend memanggil API MiniStack untuk membuat user cloud
4. MiniStack mengembalikan:
   - Access Key
   - Secret Key
5. Sistem otomatis memberikan paket default:
   - Pixie Dust Pouch Plan
6. Sistem membuat 1 bucket default:
   - `pixie-[username]-init`
7. Data bucket dan kredensial disimpan ke database
8. User diarahkan ke dashboard

---

## Alur 2 — Dashboard dan Monitoring Kuota

Dashboard menampilkan:

- Informasi paket aktif
- Jumlah bucket aktif
- Sisa kuota storage
- Daftar bucket
- Riwayat aktivitas
- Access Key dan Secret Key

## Visualisasi Kuota

Sistem menggunakan grafik sederhana (*doughnut chart*) untuk membandingkan:

- kapasitas terpakai
- kapasitas maksimal paket

## Keamanan Kredensial

Secara default:

```text
Secret Key = ********
```

User harus memasukkan ulang password untuk melihat Secret Key.

---

## Alur 3 — Penyewaan Storage dan Validasi Kuota

Ketika user membuat bucket baru:

### Validasi Jumlah Bucket

Backend menghitung jumlah bucket aktif milik pengguna.

Jika jumlah bucket sudah mencapai batas paket:

```text
Permintaan bucket baru ditolak
```

### Validasi Kapasitas

Sistem menggunakan simulasi kuota berbasis database untuk menghitung:

```text
total kapasitas bucket user
≤
max_storage_mb
```

Jika melebihi batas paket:

```text
Permintaan ditolak
```

### Provisioning Bucket

Jika validasi berhasil:

1. Backend memanggil API MiniStack
2. Bucket dibuat
3. Aktivitas dicatat pada `activity_logs`

---

## Alur 4 — Permintaan Kredensial

User dapat melihat kembali Access Key dan Secret Key melalui dashboard.

Untuk menjaga kestabilan sistem, fitur *Regenerate Key* tidak menjadi fitur utama pada versi awal sistem dan hanya akan diimplementasikan jika integrasi MiniStack telah stabil.

---

# 5. API Endpoint Utama

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | /register | Registrasi pengguna |
| POST | /login | Login pengguna |
| GET | /dashboard | Mengambil data dashboard |
| POST | /bucket/create | Membuat bucket baru |
| GET | /buckets | Melihat daftar bucket |
| GET | /logs | Melihat aktivitas pengguna |

---

# 6. Paket Layanan (Subscription Plans)

| Nama Paket | Deskripsi | Maks Bucket | Maks Kapasitas |
|---|---|---|---|
| Pixie Dust Pouch | Paket dasar pengguna baru | 1 Bucket | 500 MB |
| Grove Plan | Paket menengah | 3 Bucket | 5 GB |
| Dragon’s Hoard Plan | Paket terbesar | 10 Bucket | 50 GB |

---

# 7. Pembagian Kerja Tim

## Anggota 1 — Frontend & UI/UX
- Mendesain UI bertema cottagecore
- Membuat halaman Login, Register, Dashboard
- Menampilkan data bucket dan kuota

## Anggota 2 — Backend & Authentication
- Membangun REST API
- Sistem Login & Register
- JWT Authentication
- Validasi quota logic

## Anggota 3 — Database Engineer
- Mendesain ERD
- Membuat schema database
- Menyiapkan query dan relasi tabel
- Menyiapkan seeder paket layanan

## Anggota 4 — Integration & Testing
- Menghubungkan frontend dan backend
- API testing
- Debugging dan end-to-end testing
- Validasi fitur login dan bucket

## Anggota 5 — Cloud Infrastructure & Documentation
- Setup MiniStack
- Riset dan integrasi API MiniStack
- Dokumentasi konfigurasi sistem
- Menyusun laporan dan presentasi

---

# 8. Timeline Pengerjaan (4 Minggu)

## Minggu 1 — Fondasi Sistem
- Setup repository dan struktur project
- Setup database
- Desain UI awal
- Setup MiniStack
- Riset endpoint API MiniStack

## Minggu 2 — Authentication & Dashboard
- Register & Login
- JWT Authentication
- Dashboard awal
- Integrasi frontend-backend

## Minggu 3 — MiniStack Integration
- Pembuatan bucket otomatis
- Integrasi Access Key & Secret Key
- Validasi quota storage
- Activity logging

## Minggu 4 — Testing & Finalisasi
- Pengujian end-to-end
- Perbaikan bug
- Finalisasi UI
- Penyusunan laporan
- Persiapan presentasi demo

---

# 9. Teknologi yang Digunakan

| Komponen | Teknologi |
|---|---|
| Frontend | React / Blade + Tailwind CSS |
| Backend | Express.js / Laravel |
| Database | PostgreSQL / MySQL |
| Cloud Simulation | MiniStack |
| Authentication | JWT + bcrypt |

---

# 10. Kesimpulan

PixieCloud dirancang sebagai simulasi platform IaaS yang memenuhi kebutuhan utama komputasi awan:

- Registrasi dan autentikasi pengguna
- Penyewaan storage virtual
- Manajemen bucket terisolasi
- Monitoring quota
- Integrasi kredensial cloud menggunakan MiniStack

Dengan pendekatan visual bertema *cottagecore fairytale* dan implementasi konsep cloud computing yang tetap realistis, sistem ini diharapkan mampu memenuhi kebutuhan UAS sekaligus memberikan pengalaman penggunaan yang menarik dan mudah dipahami.