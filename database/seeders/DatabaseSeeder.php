<?php

namespace Database\Seeders;

use App\Models\AuditTrail;
use App\Models\Bid;
use App\Models\Evaluation;
use App\Models\OrganizationSetting;
use App\Models\Tender;
use App\Models\TenderBoxWitness;
use App\Models\TenderPurchase;
use App\Models\User;
use App\Models\Vendor;
use App\Services\PdfReportService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dynamic Organization Settings (Universal / White-label)
        $setting = OrganizationSetting::updateOrCreate(
            ['id' => 1],
            [
                'org_name' => 'Lembaga Perolehan & Kontrak Universal',
                'org_short_name' => 'e-Perolehan Universal',
                'org_reg_no' => 'LPKU-2026-MY',
                'org_email' => 'perolehan@organisasi.my',
                'org_phone' => '+60 3-8888 7777',
                'org_address' => "Kompleks Pentadbiran & Pusat Perolehan Digital\nPresint 4, 62100 Putrajaya, Malaysia",
                'org_logo_path' => null,
                'currency' => 'MYR',
                'tax_percentage' => 0.00,
                'anti_bribery_policy_text' => 'Organisasi ini berpegang teguh kepada polisi sifar toleransi terhadap sebarang bentuk rasuah, suapan, atau penyalahgunaan kuasa selaras dengan Seksyen 17A Akta SPRM 2009. Sebarang cubaan mempengaruhi proses bidaan akan membatalkan penyertaan serta-merta.',
                'procurement_terms_text' => 'Semua pembida tertakluk kepada Terma Am Perolehan Kerajaan & Korporat. Bidaan harga tertutup tidak boleh ditarik balik selepas tarikh tutup bidaan.',
                'letterhead_footer_text' => 'Dokumen ini dijana secara digital oleh Sistem e-Perolehan Berintegriti Tinggi berasaskan enjin PHP 8.5.',
            ]
        );

        // 2. Administrative & Assessment Users
        $admin = User::firstOrCreate(['email' => 'admin@organisasi.my'], [
            'name' => 'Dr. Fauzi Abdullah (Pentadbir Sistem)',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+60 12-345 6789',
            'is_active' => true,
        ]);

        $urusSetia = User::firstOrCreate(['email' => 'urussetia@organisasi.my'], [
            'name' => 'Puan Harlina Mansor (Urus Setia Perolehan)',
            'password' => Hash::make('password'),
            'role' => 'urus_setia',
            'phone' => '+60 13-987 6543',
            'is_active' => true,
        ]);

        $urusSetia2 = User::firstOrCreate(['email' => 'urussetia2@organisasi.my'], [
            'name' => 'Encik Shahrul Nizam (Pegawai Perolehan Kanan)',
            'password' => Hash::make('password'),
            'role' => 'urus_setia',
            'phone' => '+60 13-444 8899',
            'is_active' => true,
        ]);

        $saksi1 = User::firstOrCreate(['email' => 'saksi1@organisasi.my'], [
            'name' => 'Encik Razak Ismail (Pegawai Saksi 1)',
            'password' => Hash::make('password'),
            'role' => 'saksi_pembuka',
            'phone' => '+60 19-222 3344',
            'is_active' => true,
        ]);

        $saksi2 = User::firstOrCreate(['email' => 'saksi2@organisasi.my'], [
            'name' => 'Cik Nadia Hashim (Pegawai Saksi 2)',
            'password' => Hash::make('password'),
            'role' => 'saksi_pembuka',
            'phone' => '+60 17-555 6677',
            'is_active' => true,
        ]);

        $penilaiTeknikal = User::firstOrCreate(['email' => 'penilai.teknikal@organisasi.my'], [
            'name' => 'Ir. Zulkifli Ahmad (Pengerusi Penilai Teknikal)',
            'password' => Hash::make('password'),
            'role' => 'penilai_teknikal',
            'phone' => '+60 12-777 8899',
            'is_active' => true,
        ]);

        $penilaiTeknikal2 = User::firstOrCreate(['email' => 'penilai.teknikal2@organisasi.my'], [
            'name' => 'Ts. Mastura Badron (Penilai Teknikal ICT)',
            'password' => Hash::make('password'),
            'role' => 'penilai_teknikal',
            'phone' => '+60 12-333 1122',
            'is_active' => true,
        ]);

        $penilaiKewangan = User::firstOrCreate(['email' => 'penilai.kewangan@organisasi.my'], [
            'name' => 'Puan Salmah Yusof (Ketua Penilai Kewangan)',
            'password' => Hash::make('password'),
            'role' => 'penilai_kewangan',
            'phone' => '+60 14-888 9900',
            'is_active' => true,
        ]);

        $penilaiKewangan2 = User::firstOrCreate(['email' => 'penilai.kewangan2@organisasi.my'], [
            'name' => 'Encik Khairul Azman (Akauntan Penilai)',
            'password' => Hash::make('password'),
            'role' => 'penilai_kewangan',
            'phone' => '+60 14-222 5566',
            'is_active' => true,
        ]);

        // 3. Registered & Verified Vendors Ecosystem (12 Diverse Malaysian Vendors)
        $vendorList = [
            [
                'email' => 'vendor1@bina-maju.com.my',
                'contact_name' => 'Ahmad Shahril',
                'company_name' => 'Bina Maju Construction Sdn Bhd',
                'reg_no' => '199801012345 (456789-A)',
                'tin' => 'C23456789010',
                'phone' => '+60 3-7722 1100',
                'address' => 'Wisma Bina Maju, Jalan Sultan Ismail',
                'city' => 'Kuala Lumpur',
                'state' => 'Wilayah Persekutuan Kuala Lumpur',
                'postcode' => '50250',
                'mof' => 'MOF-357-020199',
                'cidb' => '0120150312-SL123456 (Gred G7)',
                'bumi' => true,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor2@ict-global.com.my',
                'contact_name' => 'Kelvin Tan',
                'company_name' => 'ICT Global Cloud Solutions Sdn Bhd',
                'reg_no' => '201501098765 (1122334-B)',
                'tin' => 'C98765432010',
                'phone' => '+60 3-8312 9000',
                'address' => 'Level 8, Cyberview Tower, Cyberjaya',
                'city' => 'Cyberjaya',
                'state' => 'Selangor',
                'postcode' => '63000',
                'mof' => 'MOF-210101-ICT',
                'cidb' => null,
                'bumi' => false,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor3@hebat-servis.com.my',
                'contact_name' => 'Noraini Othman',
                'company_name' => 'Hebat Facility Services Sdn Bhd',
                'reg_no' => '201901054321 (1357924-C)',
                'tin' => 'C54321678010',
                'phone' => '+60 3-5511 2233',
                'address' => 'No. 22, Jalan Pengurusan 1, Seksyen 15',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'postcode' => '40200',
                'mof' => 'MOF-220401-FACILITY',
                'cidb' => null,
                'bumi' => true,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor4@puncak-mahsuri.com.my',
                'contact_name' => 'Ir. Hafizul Bakar',
                'company_name' => 'Puncak Mahsuri Kejuruteraan Sdn Bhd',
                'reg_no' => '201201043210 (998877-D)',
                'tin' => 'C43219876010',
                'phone' => '+60 7-234 5678',
                'address' => 'Plot 45, Kawasan Perindustrian Tebrau II',
                'city' => 'Johor Bahru',
                'state' => 'Johor',
                'postcode' => '81100',
                'mof' => 'MOF-220501-ME',
                'cidb' => '0120180514-JH443322 (Gred G5)',
                'bumi' => true,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor5@technexus.com.my',
                'contact_name' => 'Chong Wei Lun',
                'company_name' => 'TechNexus Software & Cyber Security Sdn Bhd',
                'reg_no' => '201701032145 (1234567-E)',
                'tin' => 'C32145678010',
                'phone' => '+60 4-658 9988',
                'address' => 'Suite 12-A, Bayan Lepas Technoplex',
                'city' => 'Bayan Lepas',
                'state' => 'Pulau Pinang',
                'postcode' => '11900',
                'mof' => 'MOF-210103-CYBER',
                'cidb' => null,
                'bumi' => false,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor6@megame.com.my',
                'contact_name' => 'M. Ganesan',
                'company_name' => 'Mega Mechanical & Electrical Works Sdn Bhd',
                'reg_no' => '201001019876 (912345-F)',
                'tin' => 'C19876543010',
                'phone' => '+60 5-528 7766',
                'address' => 'No. 15, Kawasan Perindustrian IGB',
                'city' => 'Ipoh',
                'state' => 'Perak',
                'postcode' => '30100',
                'mof' => 'MOF-220502-ELEC',
                'cidb' => '0120140711-PR556677 (Gred G6)',
                'bumi' => false,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor7@pharmacare.com.my',
                'contact_name' => 'Datin Dr. Zubaidah',
                'company_name' => 'PharmaCare Medical Supplies & Logistics Sdn Bhd',
                'reg_no' => '201601028765 (1198765-G)',
                'tin' => 'C28765432010',
                'phone' => '+60 6-288 3322',
                'address' => 'No. 8, Taman Melaka Raya',
                'city' => 'Melaka',
                'state' => 'Melaka',
                'postcode' => '75000',
                'mof' => 'MOF-050101-MED',
                'cidb' => null,
                'bumi' => true,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor8@proclean.com.my',
                'contact_name' => 'Faridah Sulaiman',
                'company_name' => 'ProClean Environmental Services Sdn Bhd',
                'reg_no' => '202001015678 (1378901-H)',
                'tin' => 'C15678901010',
                'phone' => '+60 3-8922 4455',
                'address' => 'No. 5, Jalan Medan Bangi, Seksyen 9',
                'city' => 'Bandar Baru Bangi',
                'state' => 'Selangor',
                'postcode' => '43650',
                'mof' => 'MOF-220402-CLEAN',
                'cidb' => null,
                'bumi' => true,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor9@wira-utara.com.my',
                'contact_name' => 'Mohd Azhar Radzi',
                'company_name' => 'Wira Pembinaan Utara Sdn Bhd',
                'reg_no' => '202101099887 (1412345-J)',
                'tin' => 'C99887766010',
                'phone' => '+60 4-733 8811',
                'address' => 'No. 102, Pusat Komersial Mergong',
                'city' => 'Alor Setar',
                'state' => 'Kedah',
                'postcode' => '05150',
                'mof' => null,
                'cidb' => '0120210819-KD887766 (Gred G4)',
                'bumi' => true,
                'status' => 'pending',
            ],
            [
                'email' => 'vendor10@datahub.com.my',
                'contact_name' => 'Lim Jian Hao',
                'company_name' => 'DataHub Analytics & AI Solutions Sdn Bhd',
                'reg_no' => '202201088776 (1456789-K)',
                'tin' => 'C88776655010',
                'phone' => '+60 3-8861 2233',
                'address' => 'Level 3, Co-Labs Cyberjaya',
                'city' => 'Cyberjaya',
                'state' => 'Selangor',
                'postcode' => '63000',
                'mof' => 'MOF-210105-AI',
                'cidb' => null,
                'bumi' => false,
                'status' => 'verified',
            ],
            [
                'email' => 'vendor11@biolab.com.my',
                'contact_name' => 'Dr. Wan Hasmah',
                'company_name' => 'BioLab Scientific & Diagnostics Sdn Bhd',
                'reg_no' => '202301077665 (1498765-L)',
                'tin' => 'C77665544010',
                'phone' => '+60 6-678 1122',
                'address' => 'No. 12, Pusat Perniagaan Senawang',
                'city' => 'Seremban',
                'state' => 'Negeri Sembilan',
                'postcode' => '70450',
                'mof' => 'MOF-050201-LAB',
                'cidb' => null,
                'bumi' => true,
                'status' => 'pending',
            ],
            [
                'email' => 'vendor12@serantau-logistik.com.my',
                'contact_name' => 'Zulkarnain Salleh',
                'company_name' => 'Konsortium Serantau Logistik Sdn Bhd',
                'reg_no' => '201801033445 (1276543-M)',
                'tin' => 'C33445566010',
                'phone' => '+60 9-514 6677',
                'address' => 'Lot 88, Kawasan Perindustrian Gebeng',
                'city' => 'Kuantan',
                'state' => 'Pahang',
                'postcode' => '26080',
                'mof' => 'MOF-220101-LOG',
                'cidb' => null,
                'bumi' => true,
                'status' => 'rejected',
            ],
        ];

        $vendors = [];
        foreach ($vendorList as $vData) {
            $user = User::firstOrCreate(['email' => $vData['email']], [
                'name' => "{$vData['contact_name']} ({$vData['company_name']})",
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'phone' => $vData['phone'],
                'is_active' => true,
            ]);

            $vendor = Vendor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $vData['company_name'],
                    'registration_number' => $vData['reg_no'],
                    'tax_identification_number' => $vData['tin'],
                    'company_email' => $vData['email'],
                    'company_phone' => $vData['phone'],
                    'address' => $vData['address'],
                    'city' => $vData['city'],
                    'state' => $vData['state'],
                    'postcode' => $vData['postcode'],
                    'mof_registration_number' => $vData['mof'],
                    'cidb_registration_number' => $vData['cidb'],
                    'bumiputera_status' => $vData['bumi'],
                    'compliance_status' => $vData['status'],
                    'compliance_verified_at' => $vData['status'] === 'verified' ? now()->subMonths(rand(1, 6)) : null,
                    'verified_by_user_id' => $vData['status'] === 'verified' ? $admin->id : null,
                    'anti_bribery_agreed_at' => now()->subMonths(rand(1, 6)),
                    'compliance_notes' => $vData['status'] === 'rejected' ? 'Dokumen penyata bank dan pendaftaran MOF telah luput tarikh sah laku.' : 'Profil pembekal disahkan lengkap dan mematuhi keperluan integriti.',
                ]
            );

            $vendors[$vData['email']] = $vendor;
        }

        // 4. Initial Audit Trail
        AuditTrail::log(
            eventType: 'SYSTEM_INITIALIZED',
            description: 'Pangkalan data e-Perolehan diisi dengan ekosistem data perolehan komprehensif untuk pengujian & papan pemuka analitik.',
            userId: $admin->id
        );

        // 5. Tenders across categories, statuses, and timeframes (16 Realistic Malaysian Tenders)
        $tendersData = [
            // Tender 1: Supply (Published / Open for Bidding)
            [
                'ref' => 'TDR-2026-0001',
                'title' => 'Pembekalan & Pemasangan Peralatan Pelayan Pusat Data dan Storan NVMe Berprestasi Tinggi',
                'slug' => 'pembekalan-pemasangan-pelayan-pusat-data-nvme',
                'category' => 'supply',
                'type' => 'open',
                'desc' => 'Membekal, menghantar, memasang, menguji, dan mentauliahkan pelayan pusat data berkeupayaan tinggi serta storan rangkaian NVMe untuk menampung integrasi data raya perolehan.',
                'scope' => "1. Membekalkan 4 unit Pelayan Rack 2U Enterprise 64-core.\n2. Membekalkan Storan Rangkaian Berkapasiti 200TB NVMe all-flash.\n3. Perkhidmatan sokongan teknikal perkakasan selama 36 bulan (24/7 on-site support).",
                'criteria' => "1. Syarikat berdaftar SSM yang masih sah.\n2. Sijil Kementerian Kewangan (MOF) Kod Bidang 210101 (Hardware & Servers).\n3. Modal berbayar sekurang-kurangnya RM 100,000.00.",
                'budget' => 450000.00,
                'price' => 50.00,
                'deposit' => 5000.00,
                'pub_at' => now()->subDays(10),
                'close_at' => now()->addDays(14),
                'status' => 'published',
                'min_score' => 70,
            ],
            // Tender 2: Service (Box Opened / Ready for evaluation)
            [
                'ref' => 'TDR-2026-0002',
                'title' => 'Perkhidmatan Penyelenggaraan Sistem Aplikasi Awan dan Pusat Operasi Keselamatan Siber (SOC)',
                'slug' => 'penyelenggaraan-sistem-aplikasi-awan-keselamatan-siber-soc',
                'category' => 'service',
                'type' => 'open',
                'desc' => 'Perkhidmatan pengurusan pemantauan keselamatan siber secara proaktif dan penyelenggaraan infrastruktur awan selama 24 bulan.',
                'scope' => "1. Pemantauan SOC 24x7x365 dengan SLA tindak balas 15 minit.\n2. Pengurusan tambalan sekuriti (patching) dan penentukuran WAF berkala.\n3. Laporan audit keselamatan berkala dwibulanan.",
                'criteria' => "1. Berdaftar SSM dan MOF Kod Bidang 210103 / 210104.\n2. Memiliki persijilan ISO/IEC 27001.\n3. Pasukan jurutera bertauliah (CISSP, CEH, atau setaraf).",
                'budget' => 380000.00,
                'price' => 100.00,
                'deposit' => 0.00,
                'pub_at' => now()->subDays(30),
                'close_at' => now()->subDays(5),
                'status' => 'box_opened',
                'min_score' => 75,
                'dual_witness_at' => now()->subDays(4),
            ],
            // Tender 3: Work (Awarded / Completed Contract)
            [
                'ref' => 'TDR-2026-0003',
                'title' => 'Kerja-kerja Naik Taraf Infrastruktur Rangkaian dan Kemudahan Pejabat Pintar Bersepadu',
                'slug' => 'kerja-naik-taraf-infrastruktur-rangkaian-pejabat-pintar',
                'category' => 'work',
                'type' => 'open',
                'desc' => 'Kerja pengubahsuaian sivil dan pendawaian rangkaian gentian optik pejabat pintar merangkumi sistem automasi bilik mesyuarat dan kawalan capaian biometrik.',
                'scope' => "1. Pemasangan kabel optik OM4 dan 120 nod Cat6A.\n2. Naik taraf kemudahan bilik mesyuarat pintar.\n3. Pemasangan sistem kawalan pintu pintar & CCTV AI.",
                'criteria' => "1. Berdaftar SSM dan CIDB sekurang-kurangnya Gred G3 (Kategori ME/CE).\n2. Mempunyai rekod menyiapkan projek serupa sekurang-kurangnya RM 200,000.",
                'budget' => 280000.00,
                'price' => 50.00,
                'deposit' => 2500.00,
                'pub_at' => now()->subMonths(3),
                'close_at' => now()->subMonths(2),
                'status' => 'awarded',
                'min_score' => 70,
                'dual_witness_at' => now()->subMonths(2)->addDays(1),
                'awarded_at' => now()->subMonths(2)->addDays(10),
                'award_notes' => 'Lembaga Perolehan bersetuju sebulat suara menganugerahkan kontrak kepada Bina Maju Construction Sdn Bhd berasaskan pematuhan teknikal 100% dan tawaran harga paling wajar dan kompetitif.',
            ],
            // Tender 4: Consultancy (Published / Open for Bidding)
            [
                'ref' => 'TDR-2026-0004',
                'title' => 'Perkhidmatan Perunding Pengauditan Keselamatan Maklumat dan Tadbir Urus Data ISO/IEC 27001:2022',
                'slug' => 'perunding-pengauditan-keselamatan-maklumat-iso27001',
                'category' => 'consultancy',
                'type' => 'open',
                'desc' => 'Kajian pengauditan, analisis jurang (gap analysis), dan penyediaan kerangka kerja tadbir urus perlindungan data organisasi.',
                'scope' => "1. Pelaksanaan gap analysis terhadap 93 kawalan keselamatan maklumat ISO 27001:2022.\n2. Bengkel pengurusan risiko dan latihan kakitangan.\n3. Audit simulasi persijilan.",
                'criteria' => "1. Berdaftar MOF di bawah Kod Perundingan Pengurusan.\n2. Mempunyai sekurang-kurangnya 2 juruaudit berdaftar IRCA ISO 27001 Lead Auditor.",
                'budget' => 120000.00,
                'price' => 0.00,
                'deposit' => 0.00,
                'pub_at' => now()->subDays(5),
                'close_at' => now()->addDays(20),
                'status' => 'published',
                'min_score' => 80,
            ],
            // Tender 5: Supply (Closed / Awaiting Witness Box Opening)
            [
                'ref' => 'TDR-2026-0005',
                'title' => 'Pembekalan 6 Unit Kenderaan Ambulans Respons Pantas Dilengkapi Peralatan Resusitasi Lanjutan',
                'slug' => 'pembekalan-ambulans-respons-pantas-peralatan-resusitasi',
                'category' => 'supply',
                'type' => 'open',
                'desc' => 'Membekal dan menghantar ambulans jenis Type B/C lengkap dengan ventilator perubatan, defibrilator mudah alih, dan radio komunikasi kecemasan bersepadu.',
                'scope' => "1. Membekal 6 unit van transmisi automatik berkuasa diesel euro 5.\n2. Pemasangan siren kecemasan, sistem oksigen berpusat, dan katil pengusung hidraulik.\n3. Waranti kenderaan 5 tahun dan servis berkala percuma.",
                'criteria' => "1. Berdaftar SSM dan MOF Kod Bidang 050101/050102 (Peralatan Perubatan & Kenderaan Khas).\n2. Mempunyai kemudahan pusat servis sah di seluruh Semenanjung Malaysia.",
                'budget' => 1850000.00,
                'price' => 200.00,
                'deposit' => 10000.00,
                'pub_at' => now()->subDays(25),
                'close_at' => now()->subDays(1),
                'status' => 'closed',
                'min_score' => 75,
            ],
            // Tender 6: Service (Evaluating Technical & Financial)
            [
                'ref' => 'TDR-2026-0006',
                'title' => 'Perkhidmatan Kawalan Keselamatan Tanpa Senjata 24 Jam di Bangunan Utama dan Cawangan Wilayah',
                'slug' => 'perkhidmatan-kawalan-keselamatan-24-jam-bangunan-utama',
                'category' => 'service',
                'type' => 'open',
                'desc' => 'Penyediaan tenaga pengawal keselamatan terlatih bertauliah CSG dengan sistem rondaan masa nyata (guard tour RFID) selama 36 bulan.',
                'scope' => "1. Menempatkan 18 pengawal keselamatan syif 12 jam (9 siang, 9 malam).\n2. Sistem pemantauan rondaan RFID & GPS berasaskan aplikasi awan.\n3. Latihan pencegahan kebakaran dan kecemasan setiap 6 bulan.",
                'criteria' => "1. Lesen sah Kementerian Dalam Negeri (KDN) di bawah Akta Agensi Persendirian 1971.\n2. Berdaftar dengan Persatuan Industri Keselamatan Malaysia (PIKM).\n3. Berdaftar MOF Kod Bidang 220801.",
                'budget' => 620000.00,
                'price' => 100.00,
                'deposit' => 5000.00,
                'pub_at' => now()->subDays(40),
                'close_at' => now()->subDays(12),
                'status' => 'evaluating',
                'min_score' => 75,
                'dual_witness_at' => now()->subDays(10),
            ],
            // Tender 7: Supply (Awarded)
            [
                'ref' => 'TDR-2026-0007',
                'title' => 'Pembangunan Sistem Kepintaran Buatan (AI) Perolehan Pintar & Platform Data Lakehouse',
                'slug' => 'pembangunan-sistem-ai-perolehan-pintar-data-lakehouse',
                'category' => 'supply',
                'type' => 'open',
                'desc' => 'Membangunkan modul ramalan corak perolehan berkuasa AI, semakan harga silang secara automatik, dan integrasi pangkalan data berpusat.',
                'scope' => "1. Arkitektur Data Lakehouse berasaskan Apache Iceberg & BigQuery.\n2. Model AI Pengesanan Anomali Harga Bidaan.\n3. Panel visualisasi eksekutif masa nyata.",
                'criteria' => "1. MOF Kod Bidang 210103 / 210105 (Perisian & Perundingan ICT).\n2. Mempunyai sekurang-kurangnya 3 projek kecerdasan buatan enterprise yang telah disiapkan.",
                'budget' => 890000.00,
                'price' => 150.00,
                'deposit' => 5000.00,
                'pub_at' => now()->subMonths(4),
                'close_at' => now()->subMonths(3),
                'status' => 'awarded',
                'min_score' => 80,
                'dual_witness_at' => now()->subMonths(3)->addDays(2),
                'awarded_at' => now()->subMonths(3)->addDays(15),
                'award_notes' => 'Dianugerahkan kepada TechNexus Software berasaskan skor teknikal tertinggi (94%) dan metodologi kecerdasan buatan tercanggih.',
            ],
            // Tender 8: Work (Published / Open for Bidding)
            [
                'ref' => 'TDR-2026-0008',
                'title' => 'Kerja Membina Bangsal Kenderaan Berat, Stor Logistik & Pagar Keselamatan Anti-Panjat',
                'slug' => 'membina-bangsal-kenderaan-berat-stor-logistik',
                'category' => 'work',
                'type' => 'open',
                'desc' => 'Kerja-kerja pembinaan struktur keluli pasang siap, lantai konkrit tahan beban berat (heavy-duty reinforced concrete), dan pemasangan pagar anti-climb.',
                'scope' => "1. Meratakan tapak dan cerucuk asas (micropiling).\n2. Mendirikan bangsal keluli berkeluasan 8,000 kaki persegi berserta saliran hujan.\n3. Pemasangan pagar keselamatan anti-climb sepanjang 350 meter.",
                'criteria' => "1. Pendaftaran CIDB Perakuan Pendaftaran Kontraktor (PPK) Gred G4 (Pengkhususan CE21 / B04).\n2. Sijil Perolehan Kerja Kerajaan (SPKK) yang sah.",
                'budget' => 340000.00,
                'price' => 50.00,
                'deposit' => 2500.00,
                'pub_at' => now()->subDays(8),
                'close_at' => now()->addDays(18),
                'status' => 'published',
                'min_score' => 70,
            ],
            // Tender 9: Service (Awarded)
            [
                'ref' => 'TDR-2026-0009',
                'title' => 'Perkhidmatan Pembersihan Bangunan Menara Pentadbiran dan Landskap Kawasan Sekitar',
                'slug' => 'perkhidmatan-pembersihan-bangunan-landskap-kawasan',
                'category' => 'service',
                'type' => 'open',
                'desc' => 'Perkhidmatan mencuci harian ruang pejabat, pembersihan kaca luar bertingkat tinggi (abseiling), pelupusan sisa berjadual, dan pemeliharaan landskap flora.',
                'scope' => "1. Pembersihan 14 tingkat menara pejabat seluas 180,000 kps.\n2. Pembersihan kaca luaran 2 kali setahun oleh pekerja bertauliah IRATA.\n3. Penjagaan rumput, cantasan pokok teduhan, dan pembajaan landskap.",
                'criteria' => "1. Sijil MOF Kod Bidang 220401 (Pembersihan Bangunan dan Pejabat).\n2. Memiliki sekurang-kurangnya 25 orang pekerja tempatan berdaftar PERKESO.",
                'budget' => 420000.00,
                'price' => 80.00,
                'deposit' => 0.00,
                'pub_at' => now()->subMonths(5),
                'close_at' => now()->subMonths(4),
                'status' => 'awarded',
                'min_score' => 70,
                'dual_witness_at' => now()->subMonths(4)->addDays(1),
                'awarded_at' => now()->subMonths(4)->addDays(12),
                'award_notes' => 'Dianugerahkan kepada Hebat Facility Services Sdn Bhd kerana menawarkan harga paling ekonomi serta rekod prestasi cemerlang.',
            ],
            // Tender 10: Consultancy (Evaluated / Awaiting Board Award)
            [
                'ref' => 'TDR-2026-0010',
                'title' => 'Kajian Pelan Induk Transformasi Digital dan Reka Bentuk Arkitek Enterprise (EA) 2026-2030',
                'slug' => 'kajian-pelan-induk-transformasi-digital-arkitek-enterprise',
                'category' => 'consultancy',
                'type' => 'open',
                'desc' => 'Kajian menyeluruh ekosistem ICT organisasi, reka bentuk sasaran seni bina masa depan (TOGAF-based Enterprise Architecture), dan pelan hala tuju pelaburan digital 5 tahun.',
                'scope' => "1. Penilaian kematangan digital semasa berasaskan model DMM.\n2. Reka bentuk Arkitek Bisnes, Data, Aplikasi, dan Teknologi.\n3. Garis panduan tadbir urus dan pelan pengurusan perubahan.",
                'criteria' => "1. Berdaftar MOF Kod 210105 (Perundingan ICT & Arkitek Sistem).\n2. Ketua Pasukan memiliki pensijilan TOGAF 9/10 Certified.",
                'budget' => 250000.00,
                'price' => 0.00,
                'deposit' => 0.00,
                'pub_at' => now()->subDays(45),
                'close_at' => now()->subDays(15),
                'status' => 'evaluating',
                'min_score' => 80,
                'dual_witness_at' => now()->subDays(14),
            ],
            // Tender 11: Work (Awarded)
            [
                'ref' => 'TDR-2026-0011',
                'title' => 'Kerja-kerja Menurap Semula Jalan Akses Utama Kompleks dan Membaik Pulih Sistem Saliran',
                'slug' => 'kerja-menurap-jalan-akses-membaik-pulih-saliran',
                'category' => 'work',
                'type' => 'open',
                'desc' => 'Kerja milling, premix tar asfalt ACB28/SMA14, pembinaan longkang konkrit bertetulang U-drain 600mm, dan penandaan garisan jalan pantulan cahaya termoplastik.',
                'scope' => "1. Kerja milling dan turapan asfalt ketebalan 50mm sepanjang 1.8km.\n2. Pemasangan longkang konkrit U-drain bertutup sepanjang 400m.\n3. Penandaan garisan jalan dan papan tanda keselamatan reflektif.",
                'criteria' => "1. Berdaftar CIDB Gred G4 (Kategori CE01 - Pembinaan Jalan dan Turapan).\n2. Memiliki loji jentera paver dan roller bertarikh operasi sah.",
                'budget' => 510000.00,
                'price' => 50.00,
                'deposit' => 3000.00,
                'pub_at' => now()->subMonths(2),
                'close_at' => now()->subDays(35),
                'status' => 'awarded',
                'min_score' => 70,
                'dual_witness_at' => now()->subDays(34),
                'awarded_at' => now()->subDays(20),
                'award_notes' => 'Kontrak dianugerahkan kepada Bina Maju Construction Sdn Bhd dengan harga tawaran RM 478,000.00, menghasilkan penjimatan bajet sebanyak RM 32,000.00.',
            ],
            // Tender 12: Supply (Published / Open for Bidding)
            [
                'ref' => 'TDR-2026-0012',
                'title' => 'Pembekalan & Konfigurasi Perkakasan Rangkaian Wi-Fi 7 Kampus Berserta Suis Inti 10Gbps',
                'slug' => 'pembekalan-perkakasan-wifi-7-suis-inti-10gbps',
                'category' => 'supply',
                'type' => 'open',
                'desc' => 'Membekal 80 unit Access Point generasi Wi-Fi 7 (802.11be), 6 unit suis pengagihan PoE+ 48-port, dan 2 unit suis teras redundan 10G/40G fiber.',
                'scope' => "1. Pembekalan 80 AP Wi-Fi 7 Tri-Band dengan kawalan pengawal awan.\n2. Pendawaian kabel Cat6A F/UTP tahan gangguan elektromagnetik.\n3. Ujian penembusan liputan (heat-map coverage survey) sebelum serahan.",
                'criteria' => "1. MOF Kod Bidang 210101 (Peralatan Komunikasi dan Rangkaian).\n2. Surat kebenaran pengeluar (Manufacturer Authorization Form - MAF) rasmi.",
                'budget' => 295000.00,
                'price' => 50.00,
                'deposit' => 0.00,
                'pub_at' => now()->subDays(6),
                'close_at' => now()->addDays(22),
                'status' => 'published',
                'min_score' => 75,
            ],
            // Tender 13: Service (Box Opened)
            [
                'ref' => 'TDR-2026-0013',
                'title' => 'Penyelenggaraan Komprehensif Berkala 14 Unit Lif Penumpang dan Sistem Automasi Mekanikal M&E',
                'slug' => 'penyelenggaraan-lif-penumpang-sistem-automasi-me',
                'category' => 'service',
                'type' => 'open',
                'desc' => 'Perkhidmatan penyelenggaraan pencegahan dwimingguan, pemeriksaan tahunan JKKP/DOSH, dan jaminan panggilan kecemasan 24 jam dengan waktu sampai bawah 30 minit.',
                'scope' => "1. Penyelenggaraan 14 unit lif berkelajuan tinggi (2.5m/s).\n2. Penggantian tali dawai pengangkat (hoist ropes) dan pad brek keselamatan jika haus.\n3. Kelulusan Sijil Perakuan Kelayakan (PMA) JKKP.",
                'criteria' => "1. Syarikat Pembina Lif Bertauliah Berdaftar dengan JKKP (Jabatan Keselamatan dan Kesihatan Pekerjaan).\n2. CIDB Gred G5 (Pengkhususan M03 - Sistem Lif dan Eskalator).",
                'budget' => 360000.00,
                'price' => 100.00,
                'deposit' => 0.00,
                'pub_at' => now()->subDays(28),
                'close_at' => now()->subDays(3),
                'status' => 'box_opened',
                'min_score' => 75,
                'dual_witness_at' => now()->subDays(2),
            ],
            // Tender 14: Consultancy (Published)
            [
                'ref' => 'TDR-2026-0014',
                'title' => 'Perkhidmatan Perunding Pengurusan Risiko Bencana ICT, Kesinambungan Operasi (BCP) & Ujian Pemulihan Bencana (DRP)',
                'slug' => 'perunding-pengurusan-risiko-bencana-bcp-drp',
                'category' => 'consultancy',
                'type' => 'open',
                'desc' => 'Penyediaan Pelan Kesinambungan Perniagaan (Business Continuity Plan), pengemaskinian Analisis Impak Perniagaan (BIA), dan simulasi pensuisan failover pusat pemulihan bencana (DRC).',
                'scope' => "1. Kajian BIA merentasi 12 bahagian operasi utama.\n2. Penyediaan Standard Operating Procedure (SOP) DRP.\n3. Pelaksanaan live simulation drill pensuisan trafik DRC.",
                'criteria' => "1. Berdaftar MOF Kod 210105 / 222601.\n2. Jururunding memiliki persijilan CBCP (Certified Business Continuity Professional) atau setaraf.",
                'budget' => 95000.00,
                'price' => 0.00,
                'deposit' => 0.00,
                'pub_at' => now()->subDays(3),
                'close_at' => now()->addDays(25),
                'status' => 'published',
                'min_score' => 75,
            ],
            // Tender 15: Supply (Draft - for Urus Setia Testing)
            [
                'ref' => 'TDR-2026-0015',
                'title' => 'Pembekalan Perabot Pejabat Ergonomik dan Stesen Kerja Modular Kakitangan',
                'slug' => 'pembekalan-perabot-pejabat-ergonomik-stesen-kerja',
                'category' => 'supply',
                'type' => 'open',
                'desc' => 'Membekal kerusi ergonomik persijilan BIFMA, meja boleh laras ketinggian (height-adjustable motorized desk), dan partition modular akustik.',
                'scope' => "1. 150 unit kerusi ergonomik dengan sokongan lumbar dinamik.\n2. 80 set stesen kerja modular 2-way dan 4-way.\n3. Pemasangan dan pembersihan sisa pembungkusan.",
                'criteria' => "1. Berdaftar MOF Kod Bidang 020101 (Perabot dan Kelengkapan Pejabat).\n2. Memiliki sijil ujian kualiti SIRIM atau BIFMA X5.1.",
                'budget' => 180000.00,
                'price' => 50.00,
                'deposit' => 0.00,
                'pub_at' => null,
                'close_at' => null,
                'status' => 'draft',
                'min_score' => 70,
            ],
            // Tender 16: Work (Draft - for Urus Setia Testing)
            [
                'ref' => 'TDR-2026-0016',
                'title' => 'Cadangan Pemasangan Sistem Panel Solar Fotovoltaik (PV) Berkapasiti 250kWp Atas Bumbung Kompleks',
                'slug' => 'cadangan-pemasangan-panel-solar-fotovoltaik-250kwp',
                'category' => 'work',
                'type' => 'open',
                'desc' => 'Reka bentuk, pembekalan, pemasangan, penyambungan grid NEM (Net Energy Metering) dan pentauliahan sistem solar fotovoltaik bagi inisiatif bangunan hijau.',
                'scope' => "1. Pemasangan 450 keping panel solar Tier-1 Monocrystalline.\n2. Inverter solar pintar grid-tie dengan modul pemantauan IoT.\n3. Permohonan permit SEDA dan ujian suntikan TNB.",
                'criteria' => "1. Berdaftar dengan SEDA (Sustainable Energy Development Authority) sebagai Service Provider berdaftar.\n2. CIDB Gred G5 (Pengkhususan E11 - Kerja Pemasangan Elektrik Voltan Tinggi/Solar).",
                'budget' => 750000.00,
                'price' => 100.00,
                'deposit' => 5000.00,
                'pub_at' => null,
                'close_at' => null,
                'status' => 'draft',
                'min_score' => 75,
            ],
        ];

        $tenders = [];
        foreach ($tendersData as $tData) {
            $tender = Tender::updateOrCreate(
                ['reference_no' => $tData['ref']],
                [
                    'title' => $tData['title'],
                    'slug' => $tData['slug'],
                    'category' => $tData['category'],
                    'procurement_type' => $tData['type'],
                    'description' => $tData['desc'],
                    'scope_of_work' => $tData['scope'],
                    'eligibility_criteria' => $tData['criteria'],
                    'estimated_budget' => $tData['budget'],
                    'document_price' => $tData['price'],
                    'tender_deposit_amount' => $tData['deposit'],
                    'published_at' => $tData['pub_at'],
                    'closing_at' => $tData['close_at'],
                    'status' => $tData['status'],
                    'min_technical_score' => $tData['min_score'],
                    'dual_witness_opened_at' => $tData['dual_witness_at'] ?? null,
                    'awarded_at' => $tData['awarded_at'] ?? null,
                    'award_notes' => $tData['award_notes'] ?? null,
                    'created_by_user_id' => $urusSetia->id,
                    'created_at' => $tData['pub_at'] ?? now(),
                    'updated_at' => $tData['awarded_at'] ?? $tData['pub_at'] ?? now(),
                ]
            );

            $tenders[$tData['ref']] = $tender;
        }

        // 6. Purchases, Dual-Witness Confirmations, Bids & Evaluations Setup
        // Helper mapping of vendor emails to vendor models
        $v1 = $vendors['vendor1@bina-maju.com.my'];
        $v2 = $vendors['vendor2@ict-global.com.my'];
        $v3 = $vendors['vendor3@hebat-servis.com.my'];
        $v4 = $vendors['vendor4@puncak-mahsuri.com.my'];
        $v5 = $vendors['vendor5@technexus.com.my'];
        $v6 = $vendors['vendor6@megame.com.my'];
        $v7 = $vendors['vendor7@pharmacare.com.my'];
        $v8 = $vendors['vendor8@proclean.com.my'];
        $v10 = $vendors['vendor10@datahub.com.my'];

        // --- TDR-2026-0001 (Published / Open) ---
        // 2 Purchases & 1 Sealed Bid (Vendor 5 / TechNexus) - keep Vendor 1 & 2 free for tests
        $this->seedPurchase($tenders['TDR-2026-0001'], $v5, 50.00, now()->subDays(6));
        $this->seedPurchase($tenders['TDR-2026-0001'], $v10, 50.00, now()->subDays(4));

        $this->seedSealedBid($tenders['TDR-2026-0001'], $v5, '442000.00', 'Infrastruktur pelayan lengkap dengan perisian pemantauan automasi dan keselamatan siber terbina.', now()->subDays(2));

        // --- TDR-2026-0002 (Box Opened / Ready for evaluation) ---
        // 2 Purchases & 2 Opened Bids (Vendor 1 & Vendor 2) matching test requirements
        $this->seedPurchase($tenders['TDR-2026-0002'], $v1, 100.00, now()->subDays(25));
        $this->seedPurchase($tenders['TDR-2026-0002'], $v2, 100.00, now()->subDays(24));

        $this->seedDualWitnesses($tenders['TDR-2026-0002'], $saksi1, $saksi2, now()->subDays(4));

        $b2_1 = $this->seedOpenedBid($tenders['TDR-2026-0002'], $v1, 'PEMBIDA-A', 360000.00, 24, 'Cadangan komprehensif mengintegrasikan pasukan jurutera tempatan dengan ketersediaan sokongan 24/7.', now()->subDays(10), now()->subDays(4), 82.50);
        $b2_2 = $this->seedOpenedBid($tenders['TDR-2026-0002'], $v2, 'PEMBIDA-B', 345000.00, 24, 'Pusat operasi SOC berasaskan platform awan dengan AI SIEM detection automatik.', now()->subDays(9), now()->subDays(4), 88.00);

        $this->seedEvaluation($tenders['TDR-2026-0002'], $b2_1, $penilaiTeknikal, 'technical', 35.0, 23.5, 24.0, 82.50, true, 'Memenuhi spesifikasi teknikal utama.');
        $this->seedEvaluation($tenders['TDR-2026-0002'], $b2_2, $penilaiTeknikal, 'technical', 38.0, 25.0, 25.0, 88.00, true, 'Metodologi dan kepakaran pasukan keselamatan siber sangat cemerlang.');

        // --- TDR-2026-0003 (Awarded to Bina Maju) ---
        $this->seedPurchase($tenders['TDR-2026-0003'], $v1, 50.00, now()->subMonths(3)->addDays(5));
        $this->seedPurchase($tenders['TDR-2026-0003'], $v4, 50.00, now()->subMonths(3)->addDays(7));
        $this->seedPurchase($tenders['TDR-2026-0003'], $v6, 50.00, now()->subMonths(3)->addDays(9));

        $this->seedDualWitnesses($tenders['TDR-2026-0003'], $saksi1, $saksi2, now()->subMonths(2)->addDays(1));

        $b3_win = $this->seedAwardedBid($tenders['TDR-2026-0003'], $v1, 'PEMBIDA-A', 265000.00, 6, 'Pelan pelaksanaan naik taraf dengan bekalan bahan premium dan jaminan pematuhan jadual.', now()->subMonths(2)->subDays(5), now()->subMonths(2)->addDays(1), 92.00, 100.00, 94.40);
        $b3_other = $this->seedRejectedBid($tenders['TDR-2026-0003'], $v4, 'PEMBIDA-B', 278000.00, 6, 'Pemasangan sistem pintar dengan sokongan vendor antarabangsa.', now()->subMonths(2)->subDays(4), now()->subMonths(2)->addDays(1), 84.00, 92.50, 86.55);

        $tenders['TDR-2026-0003']->update(['awarded_bid_id' => $b3_win->id]);

        $this->seedEvaluation($tenders['TDR-2026-0003'], $b3_win, $penilaiTeknikal, 'technical', 38.0, 27.0, 27.0, 92.00, true, 'Metodologi kerja sangat teratur.');
        $this->seedEvaluation($tenders['TDR-2026-0003'], $b3_win, $penilaiKewangan, 'financial', 40.0, 30.0, 30.0, 100.00, true, 'Harga kompetitif di bawah siling bajet peruntukan.');

        // --- TDR-2026-0005 (Closed / Awaiting Witness Opening) ---
        $this->seedPurchase($tenders['TDR-2026-0005'], $v7, 200.00, now()->subDays(20));
        $this->seedPurchase($tenders['TDR-2026-0005'], $v1, 200.00, now()->subDays(18));
        $this->seedPurchase($tenders['TDR-2026-0005'], $v4, 200.00, now()->subDays(15));

        $this->seedSealedBid($tenders['TDR-2026-0005'], $v7, '1790000.00', 'Membekal 6 ambulans resusitasi lengkap pensijilan KKM dan Euro 5.', now()->subDays(3));
        $this->seedSealedBid($tenders['TDR-2026-0005'], $v4, '1820000.00', 'Pakej ambulans bersama latihan pemanduan respons kecemasan dan simulator.', now()->subDays(2));

        // --- TDR-2026-0006 (Evaluating) ---
        $this->seedPurchase($tenders['TDR-2026-0006'], $v3, 100.00, now()->subDays(35));
        $this->seedPurchase($tenders['TDR-2026-0006'], $v8, 100.00, now()->subDays(32));

        $this->seedDualWitnesses($tenders['TDR-2026-0006'], $saksi1, $saksi2, now()->subDays(10));

        $b6_1 = $this->seedOpenedBid($tenders['TDR-2026-0006'], $v3, 'PEMBIDA-A', 595000.00, 36, 'Kawalan keselamatan berdisiplin tinggi dengan pemantauan rondaan RFID.', now()->subDays(15), now()->subDays(10), 87.00);
        $b6_2 = $this->seedOpenedBid($tenders['TDR-2026-0006'], $v8, 'PEMBIDA-B', 610000.00, 36, 'Pengawal keselamatan berlesen CSG dengan kawalan CCTV AI.', now()->subDays(14), now()->subDays(10), 81.50);

        $this->seedEvaluation($tenders['TDR-2026-0006'], $b6_1, $penilaiTeknikal, 'technical', 37.0, 25.0, 25.0, 87.00, true, 'Kekuatan anggota mencukupi dan berpengalaman.');
        $this->seedEvaluation($tenders['TDR-2026-0006'], $b6_2, $penilaiTeknikal, 'technical', 35.0, 23.0, 23.5, 81.50, true, 'Spesifikasi mematuhi standard asas.');

        // --- TDR-2026-0007 (Awarded to TechNexus) ---
        $this->seedPurchase($tenders['TDR-2026-0007'], $v2, 150.00, now()->subMonths(4)->addDays(3));
        $this->seedPurchase($tenders['TDR-2026-0007'], $v5, 150.00, now()->subMonths(4)->addDays(4));
        $this->seedPurchase($tenders['TDR-2026-0007'], $v10, 150.00, now()->subMonths(4)->addDays(5));

        $this->seedDualWitnesses($tenders['TDR-2026-0007'], $saksi1, $saksi2, now()->subMonths(3)->addDays(2));

        $b7_win = $this->seedAwardedBid($tenders['TDR-2026-0007'], $v5, 'PEMBIDA-A', 840000.00, 12, 'Penyelesaian AI Perolehan komprehensif berasaskan model transformer tempatan dan privasi data terjamin.', now()->subMonths(3)->subDays(3), now()->subMonths(3)->addDays(2), 94.00, 95.00, 94.30);
        $b7_rej = $this->seedRejectedBid($tenders['TDR-2026-0007'], $v10, 'PEMBIDA-B', 865000.00, 12, 'Platform data lakehouse dengan analitik ramalan.', now()->subMonths(3)->subDays(2), now()->subMonths(3)->addDays(2), 85.00, 88.00, 85.90);

        $tenders['TDR-2026-0007']->update(['awarded_bid_id' => $b7_win->id]);

        $this->seedEvaluation($tenders['TDR-2026-0007'], $b7_win, $penilaiTeknikal, 'technical', 40.0, 27.0, 27.0, 94.00, true, 'Cadangan terbaik dengan prototaip yang sangat berfungsi.');
        $this->seedEvaluation($tenders['TDR-2026-0007'], $b7_win, $penilaiKewangan, 'financial', 38.0, 28.5, 28.5, 95.00, true, 'Struktur kos terperinci dan berpatutan.');

        // --- TDR-2026-0009 (Awarded to Hebat Facility) ---
        $this->seedPurchase($tenders['TDR-2026-0009'], $v3, 80.00, now()->subMonths(5)->addDays(2));
        $this->seedPurchase($tenders['TDR-2026-0009'], $v8, 80.00, now()->subMonths(5)->addDays(4));

        $this->seedDualWitnesses($tenders['TDR-2026-0009'], $saksi1, $saksi2, now()->subMonths(4)->addDays(1));

        $b9_win = $this->seedAwardedBid($tenders['TDR-2026-0009'], $v3, 'PEMBIDA-A', 388000.00, 24, 'Penggunaan jentera pencuci lantai mesra alam dan pasukan abseiling bertauliah.', now()->subMonths(4)->subDays(4), now()->subMonths(4)->addDays(1), 90.00, 98.00, 92.40);
        $tenders['TDR-2026-0009']->update(['awarded_bid_id' => $b9_win->id]);

        // --- TDR-2026-0011 (Awarded to Bina Maju) ---
        $this->seedPurchase($tenders['TDR-2026-0011'], $v1, 50.00, now()->subMonths(2)->addDays(3));
        $this->seedPurchase($tenders['TDR-2026-0011'], $v6, 50.00, now()->subMonths(2)->addDays(4));

        $this->seedDualWitnesses($tenders['TDR-2026-0011'], $saksi1, $saksi2, now()->subDays(34));

        $b11_win = $this->seedAwardedBid($tenders['TDR-2026-0011'], $v1, 'PEMBIDA-A', 478000.00, 4, 'Turapan premix berkualiti tinggi SMA14 dan longkang konkrit tahan banjir.', now()->subDays(38), now()->subDays(34), 89.00, 95.00, 90.80);
        $tenders['TDR-2026-0011']->update(['awarded_bid_id' => $b11_win->id]);

        // --- TDR-2026-0013 (Box Opened) ---
        $this->seedPurchase($tenders['TDR-2026-0013'], $v4, 100.00, now()->subDays(20));
        $this->seedPurchase($tenders['TDR-2026-0013'], $v6, 100.00, now()->subDays(18));

        $this->seedDualWitnesses($tenders['TDR-2026-0013'], $saksi1, $saksi2, now()->subDays(2));

        $this->seedOpenedBid($tenders['TDR-2026-0013'], $v4, 'PEMBIDA-A', 335000.00, 36, 'Jurutera lif bertauliah JKKP dengan masa respon kecemasan 20 minit.', now()->subDays(5), now()->subDays(2), 86.00);
        $this->seedOpenedBid($tenders['TDR-2026-0013'], $v6, 'PEMBIDA-B', 348000.00, 36, 'Penyelenggaraan M&E bersepadu dan stok alat ganti tulen sedia ada.', now()->subDays(4), now()->subDays(2), 84.50);

        // Purchases for other active tenders (for revenue trends)
        $this->seedPurchase($tenders['TDR-2026-0004'], $v5, 0.00, now()->subDays(4));
        $this->seedPurchase($tenders['TDR-2026-0008'], $v1, 50.00, now()->subDays(5));
        $this->seedPurchase($tenders['TDR-2026-0008'], $v6, 50.00, now()->subDays(4));
        $this->seedPurchase($tenders['TDR-2026-0012'], $v2, 50.00, now()->subDays(3));
        $this->seedPurchase($tenders['TDR-2026-0012'], $v5, 50.00, now()->subDays(2));
        $this->seedPurchase($tenders['TDR-2026-0014'], $v10, 0.00, now()->subDays(2));

        // 7. Audit Trails of Key Milestones
        AuditTrail::log(
            eventType: 'TENDER_PUBLISHED',
            description: 'Tender TDR-2026-0001 dibuka untuk bidaan awam di portal.',
            auditable: $tenders['TDR-2026-0001'],
            userId: $urusSetia->id
        );

        AuditTrail::log(
            eventType: 'DUAL_WITNESS_OPENED',
            description: 'Dua orang pegawai saksi berjaya membuka peti bidaan TDR-2026-0002 secara digital.',
            auditable: $tenders['TDR-2026-0002'],
            userId: $saksi1->id
        );

        AuditTrail::log(
            eventType: 'CONTRACT_AWARDED',
            description: 'Kontrak TDR-2026-0003 dianugerahkan kepada Bina Maju Construction Sdn Bhd (Nilai: RM 265,000.00).',
            auditable: $tenders['TDR-2026-0003'],
            userId: $admin->id
        );

        // Generate sample PDF reports for seeded documents
        $pdfService = app(PdfReportService::class);
        try {
            $pdfService->generateTenderOpeningSlipPdf($tenders['TDR-2026-0002']);
            $pdfService->generateTenderOpeningSlipPdf($tenders['TDR-2026-0003']);
            $pdfService->generateEvaluationReportPdf($tenders['TDR-2026-0003']);
            $pdfService->generateSstLetterPdf($tenders['TDR-2026-0003']);
            $pdfService->generateTenderNoticePdf($tenders['TDR-2026-0001']);
            $pdfService->generateTenderNoticePdf($tenders['TDR-2026-0002']);
            $pdfService->generateTenderNoticePdf($tenders['TDR-2026-0003']);
        } catch (\Exception $e) {
            // PDF generation might omit font files in lightweight testing environments
        }

        AuditTrail::log(
            eventType: 'DATABASE_SEED_COMPLETED',
            description: 'Pangkalan data lengkap dengan pelbagai kategori tender, pembekal berdaftar, peti bidaan bertutup, dan data penganugerahan untuk analitik.',
            userId: $admin->id
        );
    }

    private function seedPurchase(Tender $tender, Vendor $vendor, float $amount, Carbon $date): TenderPurchase
    {
        $ref = 'AP-'.date('Y', $date->timestamp).'-T'.$tender->id.'V'.$vendor->id.'-'.strtoupper(Str::random(4));

        return TenderPurchase::firstOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendor->id],
            [
                'purchase_reference' => $ref,
                'amount' => $amount,
                'status' => 'paid',
                'affanpay_bill_id' => 'BILL-'.$ref,
                'affanpay_payment_ref' => 'PAY-'.strtoupper(Str::random(8)),
                'idempotency_key' => (string) Str::uuid(),
                'paid_at' => $date,
                'created_at' => $date,
                'updated_at' => $date,
            ]
        );
    }

    private function seedSealedBid(Tender $tender, Vendor $vendor, string $amount, string $summary, Carbon $date): Bid
    {
        $ref = 'BID-'.date('Y', $date->timestamp).'-T'.$tender->id.'V'.$vendor->id;

        return Bid::updateOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendor->id],
            [
                'bid_reference' => $ref,
                'blind_code' => 'PEMBIDA-'.strtoupper(Str::random(4)),
                'encrypted_bid_amount' => Crypt::encryptString($amount),
                'decrypted_bid_amount' => null,
                'completion_duration_months' => rand(6, 24),
                'technical_summary' => $summary,
                'status' => 'sealed',
                'sha256_package_hash' => hash('sha256', "SEALED|{$ref}|{$amount}"),
                'submitted_at' => $date,
                'opened_at' => null,
                'created_at' => $date,
                'updated_at' => $date,
            ]
        );
    }

    private function seedOpenedBid(Tender $tender, Vendor $vendor, string $blindCode, float $amount, int $months, string $summary, Carbon $subDate, Carbon $openDate, ?float $techScore = null): Bid
    {
        $ref = 'BID-'.date('Y', $subDate->timestamp).'-T'.$tender->id.'V'.$vendor->id;

        return Bid::updateOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendor->id],
            [
                'bid_reference' => $ref,
                'blind_code' => $blindCode,
                'encrypted_bid_amount' => Crypt::encryptString((string) $amount),
                'decrypted_bid_amount' => $amount,
                'completion_duration_months' => $months,
                'technical_summary' => $summary,
                'status' => 'opened',
                'sha256_package_hash' => hash('sha256', "OPENED|{$ref}|{$amount}"),
                'submitted_at' => $subDate,
                'opened_at' => $openDate,
                'technical_score' => $techScore,
                'created_at' => $subDate,
                'updated_at' => $openDate,
            ]
        );
    }

    private function seedAwardedBid(Tender $tender, Vendor $vendor, string $blindCode, float $amount, int $months, string $summary, Carbon $subDate, Carbon $openDate, float $techScore, float $finScore, float $totalScore): Bid
    {
        $ref = 'BID-'.date('Y', $subDate->timestamp).'-T'.$tender->id.'V'.$vendor->id;

        return Bid::updateOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendor->id],
            [
                'bid_reference' => $ref,
                'blind_code' => $blindCode,
                'encrypted_bid_amount' => Crypt::encryptString((string) $amount),
                'decrypted_bid_amount' => $amount,
                'completion_duration_months' => $months,
                'technical_summary' => $summary,
                'status' => 'awarded',
                'sha256_package_hash' => hash('sha256', "AWARDED|{$ref}|{$amount}"),
                'submitted_at' => $subDate,
                'opened_at' => $openDate,
                'technical_score' => $techScore,
                'financial_score' => $finScore,
                'total_score' => $totalScore,
                'created_at' => $subDate,
                'updated_at' => $openDate,
            ]
        );
    }

    private function seedRejectedBid(Tender $tender, Vendor $vendor, string $blindCode, float $amount, int $months, string $summary, Carbon $subDate, Carbon $openDate, float $techScore, float $finScore, float $totalScore): Bid
    {
        $ref = 'BID-'.date('Y', $subDate->timestamp).'-T'.$tender->id.'V'.$vendor->id;

        return Bid::updateOrCreate(
            ['tender_id' => $tender->id, 'vendor_id' => $vendor->id],
            [
                'bid_reference' => $ref,
                'blind_code' => $blindCode,
                'encrypted_bid_amount' => Crypt::encryptString((string) $amount),
                'decrypted_bid_amount' => $amount,
                'completion_duration_months' => $months,
                'technical_summary' => $summary,
                'status' => 'rejected',
                'sha256_package_hash' => hash('sha256', "REJECTED|{$ref}|{$amount}"),
                'submitted_at' => $subDate,
                'opened_at' => $openDate,
                'technical_score' => $techScore,
                'financial_score' => $finScore,
                'total_score' => $totalScore,
                'created_at' => $subDate,
                'updated_at' => $openDate,
            ]
        );
    }

    private function seedDualWitnesses(Tender $tender, User $s1, User $s2, Carbon $time): void
    {
        TenderBoxWitness::firstOrCreate(
            ['tender_id' => $tender->id, 'user_id' => $s1->id],
            [
                'witness_role' => 'saksi_pembuka',
                'digital_signature_hash' => hash('sha256', "WITNESS|{$tender->reference_no}|{$s1->id}"),
                'ip_address' => '10.0.0.15',
                'user_agent' => 'Mozilla/5.0 Government Portal (Saksi 1)',
                'witness_remarks' => 'Semua fail bidaan digital disahkan lengkap dan tiada sebarang kebocoran kriptografi sebelum pembukaan peti.',
                'confirmed_at' => $time,
            ]
        );

        TenderBoxWitness::firstOrCreate(
            ['tender_id' => $tender->id, 'user_id' => $s2->id],
            [
                'witness_role' => 'saksi_pembuka',
                'digital_signature_hash' => hash('sha256', "WITNESS|{$tender->reference_no}|{$s2->id}"),
                'ip_address' => '10.0.0.18',
                'user_agent' => 'Mozilla/5.0 Government Portal (Saksi 2)',
                'witness_remarks' => 'Saksi kedua mengesahkan integriti peti tender dan kunci kriptografi dinyahsulitkan serentak secara sah.',
                'confirmed_at' => $time,
            ]
        );
    }

    private function seedEvaluation(Tender $tender, Bid $bid, User $evaluator, string $type, float $c1, float $c2, float $c3, float $total, bool $passed, string $comments): void
    {
        Evaluation::firstOrCreate(
            ['tender_id' => $tender->id, 'bid_id' => $bid->id, 'evaluator_user_id' => $evaluator->id, 'evaluation_type' => $type],
            [
                'score_criteria_1' => $c1,
                'score_criteria_2' => $c2,
                'score_criteria_3' => $c3,
                'total_score' => $total,
                'is_passed' => $passed,
                'comments' => $comments,
                'completed_at' => now()->subDays(2),
                'digital_sign_hash' => hash('sha256', "EVAL|{$type}|{$bid->id}|{$evaluator->id}"),
            ]
        );
    }
}
