# Dokumen Keperluan Produk (PRD) — Sistem e-Perolehan Pintar & Telus

**Versi Dokumen:** 2.0.0  
**Tarikh Kemaskini:** 2026-11-15  
**Status:** Draf Rasmi Dimuktamadkan (Official Specification)  
**Teras Piawaian:** Arahan Perbendaharaan Malaysia (1PK MOF), Akta SPRM 2009 (Seksyen 17A), ISO 37001 Sistem Pengurusan Anti-Rasuah (ABMS).

---

## 1. Pengenalan & Objektif Sistem

Sistem e-Perolehan ini dibangunkan sebagai platform perolehan universal, selamat, dan berintegriti tinggi bagi menguruskan kitaran hayat perolehan (Tender & Sebut Harga) secara digital. Platform ini mengutamakan ketelusan penuh, pematuhan prinsip persaingan sihat, dan keselamatan kriptografi bagi menghalang ketirisan maklumat, pakatan tender (*bid-rigging*), serta konflik kepentingan (*conflict of interest*).

### Objektif Utama:
1. **Pemisahan Kuasa Mutlak (Strict Segregation of Duties):** Memastikan peranan Pentadbir, Vendor, dan Penilai dipisahkan sepenuhnya tanpa pertindihan fungsi.
2. **Kitaran Bidaan Tertutup (Sealed Bidding):** Tawaran harga dan cadangan teknikal/kewangan disulitkan dengan penyulitan gred ketenteraan (AES-256) dan dilindungi oleh protokol pembukaan dua saksi (*Dual-Witness Digital Unlock*).
3. **Penilaian Dwi-Buta (Double-Blind Evaluation):** Penilai teknikal dan kewangan menjalankan penilaian tanpa mengetahui identiti pembida mahupun maklumat harga yang tidak berkaitan.
4. **Jejak Audit Kalis Usik (Tamper-Evident Audit Trail):** Setiap transaksi, perubahan status, dan interaksi dokumen diikat dengan rantaian hash SHA-256.

---

## 2. Matriks Peranan & Had Kuasa (Separation of Duties Matrix)

| Modul / Fungsi | Pentadbir / Urus Setia (`/admin`) | Pembekal / Kontraktor (`/vendor`) | Jawatankuasa Penilai (`/penilai`) | Portal Awam (`/`) |
| :--- | :---: | :---: | :---: | :---: |
| **Cipta & Iklan Tender** | ✅ **Kuasa Mutlak** | ❌ DILARANG | ❌ DILARANG | ❌ Paparan Sahaja |
| **Beli Dokumen Tender (AffanPay)** | ❌ DILARANG | ✅ **Akses Penuh** | ❌ DILARANG | ❌ Log Masuk Diperlukan |
| **Hantar Bidaan (Submit Bid)** | ❌ DILARANG | ✅ **Kuasa Mutlak** | ❌ DILARANG | ❌ DILARANG |
| **Kemaskini Profil SSM/MOF/CIDB** | ❌ Semak Sahaja | ✅ **Urus Sendiri** | ❌ DILARANG | ❌ DILARANG |
| **Buka Peti Tender (Dua Saksi)** | ✅ **Saksi Digital** | ❌ DILARANG | ❌ DILARANG | ❌ DILARANG |
| **Nyahsulit Harga Bidaan** | ❌ Automatik Sistem | ❌ DILARANG | ❌ Automatik Sistem | ❌ DILARANG |
| **Penilaian Bidaan (Blind)** | ❌ DILARANG | ❌ DILARANG | ✅ **Kuasa Mutlak** | ❌ DILARANG |
| **Penganugerahan Tender (Award)** | ✅ **Pihak Berkuasa** | ❌ Dilarang | ❌ Syor Sahaja | ❌ Paparan Keputusan |
| **Semak Slip / Resit / SST** | ✅ Semak Semua | ✅ Milik Sendiri Sahaja | ❌ Dokumen Dinilai Sahaja | ✅ Pengesahan QR/Hash |

---

## 3. Spesifikasi Fungsian Terperinci Mengikut Peranan

### 3.1. Pembekal / Kontraktor (Vendor Portal — `/vendor`)
Tugas vendor adalah **menyemak tender, membeli dokumen tender, dan menghantar bidaan bekalan/kerja/perkhidmatan**:

1. **Pendaftaran & Pengurusan Profil:**
   - Melengkapkan maklumat syarikat, nombor pendaftaran SSM, kod bidang MOF (bagi bekalan/perkhidmatan), gred CIDB (bagi kerja pembinaan), dan maklumat akaun bank.
2. **Semakan & Pembelian Dokumen Tender:**
   - Melayari senarai tender yang sedang diiklankan dan dibuka.
   - Membeli dokumen tender berbayar secara dalam talian melalui gerbang pembayaran **AffanPay**.
   - Menerima Resit Rasmi Digital (PDF) berautomatik sebaik pembayaran disahkan.
   - Memuat turun Dokumen Tender Lengkap dan Jadual Spesifikasi.
3. **Penyediaan & Penghantaran Bidaan Bertutup (Sealed Bid Submission):**
   - **Kelayakan:** Hanya boleh membida bagi tender aktif yang telah dibeli dokumennya (atau tender percuma).
   - **Input Tawaran:**
     - Tawaran Harga Bidaan (RM) — disimpan dalam format tersulit AES-256 (`encrypted_bid_amount`).
     - Tempoh Siap / Jadual Penghantaran (Minggu / Bulan).
     - Cadangan Kaedah Kerja / Spesifikasi Teknikal.
     - Muat naik Dokumen Cadangan Teknikal & Cadangan Kewangan berasingan (`disk: private`).
   - **Kunci Bidaan & Slip Penerimaan:**
     - Sebaik bidaan disahkan dan dihantar secara muktamad (*final submit*), status bidaan dikunci (`submitted`).
     - Sistem menjana hash integriti SHA-256 untuk keseluruhan pakej bidaan.
     - Vendor menerima **Slip Akuan Penerimaan Bidaan Bertutup (PDF)** yang mengandungi kod rujukan bidaan, tarikh/masa tepat cap waktu, dan hash SHA-256.
4. **Larangan Vendor:**
   - 🚫 **DILARANG SAMA SEKALI** mencipta, mengemaskini, atau memadam rekod Tender.
   - 🚫 Dilarang melihat tawaran harga atau dokumen bidaan syarikat pesaing.
   - 🚫 Dilarang mengetahui identiti pegawai penilai tender.

---

### 3.2. Pentadbir & Urus Setia Perolehan (Admin Panel — `/admin`)
Tugas pentadbir adalah **mengurus tadbir proses perolehan daripada perancangan sehingga penganugerahan**:

1. **Penciptaan & Pengiklanan Tender:**
   - Mencipta rekod tender baharu: Nombor Rujukan, Tajuk Perolehan, Kategori (Bekalan, Perkhidmatan, Kerja, Perunding), Kod Bidang MOF/CIDB, Anggaran Jabatan, Harga Dokumen, Tarikh/Masa Buka & Tutup.
   - Memuat naik Dokumen Tender Rasmi (PDF/ZIP).
   - Mengurus status tender: *Draf*, *Diiklankan / Dibuka*, *Tutup*, *Dalam Penilaian*, *Dianugerahkan*, *Batal*.
2. **Pemantauan Peti Tender Digital (Digital Tender Box):**
   - Memantau jumlah bidaan yang masuk ke dalam peti tender secara masa nyata (bilangan sahaja).
   - **Integriti Peti:** Pentadbir **TIDAK BOLEH** melihat harga bidaan atau memuat turun dokumen bidaan sebelum sesi pembukaan rasmi.
3. **Protokol Pembukaan Peti Dua Saksi (Dual-Witness Digital Unlock):**
   - Selepas tarikh tutup tender, pembukaan peti tender digital memerlukan perakuan dua (2) pegawai saksi berdaftar.
   - Bidaan dinyahsulitkan dan diberikan Kod Dwi-Buta Rawak (*Random Blind Code*, contoh: `BID-A19F`) untuk diserahkan kepada Jawatankuasa Penilai.
4. **Keputusan & Penganugerahan Tender (Tender Award):**
   - Menerima perakuan dan skor akhir daripada Jawatankuasa Penilai.
   - Merekodkan keputusan Lembaga Perolehan: Memilih pembida berjaya.
   - Menjana Surat Setuju Terima (SST) rasmi berformat PDF.
   - Menerbitkan maklumat penganugerahan pada Papan Kenyataan Awam (*Public Award Board*).
5. **Pengarkiban Automatik:**
   - Memindahkan berkas tender dan bidaan ke storan sejuk MinIO (`disk: minio`) dengan semakan verifikasi hash SHA-256.
6. **Larangan Pentadbir:**
   - 🚫 **DILARANG SAMA SEKALI** menghantar bidaan bagi mana-mana tender.
   - 🚫 Dilarang membuka atau mengubahsuai dokumen bidaan sebelum protokol pembukaan saksi dipenuhi.

---

### 3.3. Jawatankuasa Penilai (Penilai Panel — `/penilai`)
Tugas penilai adalah **menilai bidaan secara objektif, bebas, dan dwi-buta**:

1. **Penilaian Teknikal (Blind Technical Evaluation):**
   - Memeriksa pematuhan spesifikasi bekalan/kaedah kerja berasaskan Kod Dwi-Buta (`blind_code`).
   - Penilai teknikal **TIDAK** didedahkan kepada nama syarikat atau harga bidaan.
   - Menetapkan markah pematuhan teknikal (0 - 100) dan penentuan lulus/gagal ambang kelayakan.
2. **Penilaian Kewangan (Financial Evaluation):**
   - Dikira secara sistematis berasaskan formula perkadaran perbandingan terhadap tawaran munasabah terendah yang lulus penilaian teknikal.
3. **Pengesyoran Akhir:**
   - Menjana Skor Komposit (Contoh: 70% Teknikal + 30% Kewangan).
   - Menyerahkan Laporan Penilaian Rasmi bertandatangan digital kepada Lembaga Perolehan/Pentadbir.
4. **Larangan Penilai:**
   - 🚫 DILARANG mencipta atau mengubah maklumat asas tender.
   - 🚫 DILARANG menghantar bidaan.

---

### 3.4. Portal Awam & Ketelusan (Public Web — `/`)
Menyediakan akses maklumat terbuka kepada orang awam selaras dengan ketelusan antirasuah:

1. **Papan Iklan Tender Terbuka:**
   - Senarai tender aktif, penapis kategori, semakan syarat kelayakan dan tarikh tutup.
   - Muat turun Notis Iklan Tender (PDF).
2. **Papan Pengumuman Keputusan Rasmi (Award Board):**
   - Memaparkan tajuk tender, syarikat pemenang, dan nilai penganugerahan rasmi yang dimuktamadkan.
3. **Gerbang Verifikasi Integriti Dokumen (`/verify/{type}/{ref}`):**
   - Mengesahkan ketulenan resit bayaran, slip bidaan bertutup, slip pembukaan saksi, dan SST menggunakan perbandingan hash kriptografi SHA-256.

---

## 4. Aliran Kerja Sistem (End-to-End Workflow)

```
[ ADMIN / URUS SETIA ]
   │
   ├── 1. Cipta & Iklan Tender (Spesifikasi, Tarikh Tutup, Harga Dokumen)
   └── 2. Muat naik Dokumen Tender Rasmi
           │
           ▼
[ PORTAL AWAM (/) & VENDOR (/vendor) ]
   │
   ├── 3. Vendor layari tender & semak syarat kelayakan (MOF/CIDB/SSM)
   ├── 4. Beli Dokumen Tender melalui AffanPay (Terima Resit PDF)
   └── 5. Muat turun Dokumen Tender Rasmi
           │
           ▼
[ PENGHANTARAN BIDAAN BERTUTUP (VENDOR) ]
   │
   ├── 6. Masukkan Tawaran Harga RM (Disulitkan serta-merta AES-256)
   ├── 7. Muat naik Cadangan Teknikal & Kewangan
   └── 8. Hantar Bidaan Rasmi (Kunci Peti, Hash SHA-256, Cetak Slip Bidaan)
           │
           ▼
[ TARIKH TUTUP TENDER & PEMBUKAAN PETI ]
   │
   ├── 9. Peti Tender Digital dikunci secara automatik
   └── 10. Pembukaan Dua Saksi Digital (Admin / Jawatankuasa Pembuka)
           └── Nyahsulit tawaran & jana Kod Rawak Dwi-Buta (Blind Codes)
                   │
                   ▼
[ JAWATANKUASA PENILAI (/penilai) ]
   │
   ├── 11. Penilaian Spesifikasi Teknikal (Tanpa Nama/Harga) -> Markah Teknikal
   ├── 12. Penilaian Kewangan (Formula Perkadaran) -> Markah Kewangan
   └── 13. Hantar Laporan Penilaian Komposit kepada Urus Setia
           │
           ▼
[ LEMBAGA PEROLEHAN / ADMIN (/admin) ]
   │
   ├── 14. Pemilihan Pemenang & Muktamadkan Anugerah (Award)
   ├── 15. Penjanaan Surat Setuju Terima (SST PDF)
   ├── 16. Pengarkiban Dokumen ke Storan Sejuk MinIO
   └── 17. Paparan Keputusan Terbuka di Papan Pengumuman Awam
```

---

## 5. Pelan Penyelarasan Kod Fizikal (Codebase Alignment Plan)

Untuk membetulkan sistem sedia ada agar mematuhi PRD ini 100%:

1. **Keluarkan Akses Tender daripada Portal Vendor (`app/Filament/Vendor`):**
   - Buang `CreateTender` dan `EditTender` dari `app/Filament/Vendor/Resources/Tenders`.
   - Jadikan `TenderResource` di Portal Vendor sebagai paparan sahaja (*ReadOnly / View*) yang memaparkan status pembelian dokumen dan butang tindakan pantas **"Beli Dokumen"** (jika belum dibeli) atau **"Hantar Bidaan"** (jika telah dibeli).
2. **Keluarkan Akses Tender daripada Portal Penilai (`app/Filament/Penilai`):**
   - Buang `CreateTender` dan `EditTender` dari `app/Filament/Penilai/Resources/Tenders`. Penilai hanya menilai bidaan tender yang ditugaskan.
3. **Sediakan Akses Pengurusan Bidaan di Admin (`app/Filament/Resources`):**
   - Pentadbir boleh melihat senarai bidaan yang dihantar (termasuk status peti dan rekod saksi pembukaan), tetapi tidak boleh mencipta bidaan.
4. **Kemas Kini Borang Bidaan Vendor (`app/Filament/Vendor/Resources/Bids`):**
   - Sembunyikan medan `vendor_id`, `blind_code`, `encrypted_bid_amount`, `decrypted_bid_amount`, `technical_score`, `financial_score`, dan `total_score`.
   - Tetapkan `vendor_id` secara automatik berasaskan sesi pengguna syarikat yang log masuk.
   - Sediakan medan mesra pengguna: Tawaran Harga (RM), Tempoh Pelaksanaan, Dokumen Teknikal, dan Dokumen Kewangan.
   - Sediakan tindakan "Hantar Bidaan Rasmi" yang mengunci bidaan dan mengeluarkan Slip Penerimaan.
5. **Kemas Kini Dasar Keselamatan (Laravel Policies):**
   - `TenderPolicy`: Hanya benarkan `create`, `update`, `delete` oleh peranan `admin`. Menolak peranan `vendor` dan `penilai`.
   - `BidPolicy`: Hanya benarkan `create` dan `update` (sebelum submit) oleh peranan `vendor`. Menolak peranan `admin` dan `penilai`.
6. **Ujian Automatik (PHPUnit Tests):**
   - Menulis dan mengemas kini ujian unit & fitur bagi membuktikan penguatkuasaan hak akses ini diuji dan lulus 100%.

---

## 6. Kelulusan & Penguatkuasaan

Dokumen ini menjadi rujukan rasmi tunggal (*Single Source of Truth*) bagi reka bentuk, pelaksanaan, dan penambahbaikan Sistem e-Perolehan ini. Sebarang modifikasi kepada aliran perolehan atau had kuasa peranan mestilah melalui proses semakan dan kelulusan rasmi.
