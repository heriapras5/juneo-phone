# Task List

## Metadata
- **Project:** Juneo Phone
- **Tanggal:** 2026-07-04
- **Mode:** Maintenance & Bug Fix

---

## Task

### T-01: Rename "Modal" → "Pengeluaran" di exports & form labels
- **Deskripsi:** Ganti label "Modal" dan "Pendapatan" yang masih tersisa di export files, form input, dan chart label.
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** —
- **File diubah:**
  - `app/Exports/LaporanBulananSheet.php:172` — summary label
  - `resources/views/exports/pdf-services-yearly.blade.php:128,132` — Pendapatan→Omzet, Modal→Pengeluaran
  - `resources/views/services/index.blade.php:502` — form label "Modal"
  - `resources/views/services/index.blade.php:544` — "Total Modal"
  - `resources/views/dashboard/index.blade.php:502` — chart dataset label "Pengeluaran (Modal)"
  - `resources/views/services/show.blade.php:77` — "Pengeluaran (Modal)"
  - `resources/views/services/show.blade.php:118` — th "Modal"

### T-02: Tambah "Lainnya" ke Kebutuhan Pribadi card
- **Deskripsi:** Query biaya_harian kategori 'lainnya' dan tampilkan di card Kebutuhan Pribadi dashboard (Blade + JS).
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** —
- **File diubah:**
  - `app/Services/DashboardService.php` — tambah query lainnya di getPeriodTotals() & getPreviousPeriodTotals()
  - `app/Http/Controllers/DashboardController.php` — tambah 'lainnya' ke JSON response
  - `resources/views/dashboard/_partials/kpi-cards.blade.php` — tampilkan Lainnya
  - `resources/views/dashboard/index.blade.php` — JS: tambah d.lainnya ke extra

### T-03: Fix grafik dashboard (mixed chart bar + line)
- **Deskripsi:** Ubah Chart.js dari grouped bar (3 dataset) ke mixed chart: Omzet & Pengeluaran sebagai bar, Profit sebagai line overlay. Fix weekly label parsing (Carbon::parse ISO week).
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** —
- **File diubah:**
  - `app/Services/DashboardService.php` — getChartData(): fix weekly parsing
  - `resources/views/dashboard/index.blade.php` — chart config: mixed type, beginAtZero:false, dataset labels

### T-04: Fix semua tanggal (cast + booted event + controller)
- **Deskripsi:** 
  1. Ubah model cast `'date'` → `'date:Y-m-d'` agar serialisasi timezone-safe (tidak geser -7 jam)
  2. Hapus overwrite `tanggal_selesai` saat status `diambil` di booted event (Service.php:277-278)
  3. Hapus overwrite `tanggal_selesai` saat `diambil` di updateStatus() (ServiceController.php:353-354)
- **Prioritas:** High
- **Status:** Done
- **Dependensi:** —
- **File diubah:**
  - `app/Models/Service.php:43-46` — casts date → date:Y-m-d
  - `app/Models/Service.php:277-278` — hapus set tanggal_selesai saat diambil
  - `app/Http/Controllers/ServiceController.php:353-354` — hapus set tanggal_selesai saat diambil

### T-05: Verifikasi semua perubahan
- **Deskripsi:** Cek apakah tidak ada typo, error syntax, atau test failure.
- **Prioritas:** Medium
- **Status:** Done
- **Dependensi:** T-01, T-02, T-03, T-04
- **File diubah:** —
