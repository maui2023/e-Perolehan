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

        // 2. Users (Roles: admin, urus_setia, saksi_pembuka, penilai_teknikal, penilai_kewangan, vendor)
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

        $penilaiKewangan = User::firstOrCreate(['email' => 'penilai.kewangan@organisasi.my'], [
            'name' => 'Puan Salmah Yusof (Ketua Penilai Kewangan)',
            'password' => Hash::make('password'),
            'role' => 'penilai_kewangan',
            'phone' => '+60 14-888 9900',
            'is_active' => true,
        ]);

        // 3. Registered & Verified Vendors
        $userVendor1 = User::firstOrCreate(['email' => 'vendor1@bina-maju.com.my'], [
            'name' => 'Ahmad Shahril (Bina Maju)',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'phone' => '+60 11-1234 5678',
            'is_active' => true,
        ]);

        $vendor1 = Vendor::updateOrCreate(
            ['user_id' => $userVendor1->id],
            [
                'company_name' => 'Bina Maju Construction Sdn Bhd',
                'registration_number' => '199801012345 (456789-A)',
                'tax_identification_number' => 'C23456789010',
                'company_email' => 'tenders@bina-maju.com.my',
                'company_phone' => '+60 3-7722 1100',
                'address' => 'Wisma Bina Maju, Jalan Sultan Ismail',
                'city' => 'Kuala Lumpur',
                'state' => 'Wilayah Persekutuan Kuala Lumpur',
                'postcode' => '50250',
                'mof_registration_number' => 'MOF-357-020199',
                'cidb_registration_number' => '0120150312-SL123456 (Gred G7)',
                'bumiputera_status' => true,
                'compliance_status' => 'verified',
                'compliance_verified_at' => now()->subMonths(3),
                'verified_by_user_id' => $admin->id,
                'anti_bribery_agreed_at' => now()->subMonths(3),
            ]
        );

        $userVendor2 = User::firstOrCreate(['email' => 'vendor2@ict-global.com.my'], [
            'name' => 'Kelvin Tan (ICT Global)',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'phone' => '+60 12-9988 7766',
            'is_active' => true,
        ]);

        $vendor2 = Vendor::updateOrCreate(
            ['user_id' => $userVendor2->id],
            [
                'company_name' => 'ICT Global Cloud Solutions Sdn Bhd',
                'registration_number' => '201501098765 (1122334-B)',
                'tax_identification_number' => 'C98765432010',
                'company_email' => 'sales@ict-global.com.my',
                'company_phone' => '+60 3-8312 9000',
                'address' => 'Level 8, Cyberview Tower, Cyberjaya',
                'city' => 'Cyberjaya',
                'state' => 'Selangor',
                'postcode' => '63000',
                'mof_registration_number' => 'MOF-210101-ICT',
                'cidb_registration_number' => null,
                'bumiputera_status' => false,
                'compliance_status' => 'verified',
                'compliance_verified_at' => now()->subMonths(2),
                'verified_by_user_id' => $admin->id,
                'anti_bribery_agreed_at' => now()->subMonths(2),
            ]
        );

        $userVendor3 = User::firstOrCreate(['email' => 'vendor3@hebat-servis.com.my'], [
            'name' => 'Noraini Othman (Hebat Servis)',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'phone' => '+60 13-333 4444',
            'is_active' => true,
        ]);

        $vendor3 = Vendor::updateOrCreate(
            ['user_id' => $userVendor3->id],
            [
                'company_name' => 'Hebat Facility Services Sdn Bhd',
                'registration_number' => '201901054321 (1357924-C)',
                'tax_identification_number' => 'C54321678010',
                'company_email' => 'info@hebat-servis.com.my',
                'company_phone' => '+60 3-5511 2233',
                'address' => 'No. 22, Jalan Pengurusan 1, Seksyen 15',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'postcode' => '40200',
                'mof_registration_number' => 'MOF-220401-FACILITY',
                'cidb_registration_number' => null,
                'bumiputera_status' => true,
                'compliance_status' => 'verified',
                'compliance_verified_at' => now()->subMonth(),
                'verified_by_user_id' => $admin->id,
                'anti_bribery_agreed_at' => now()->subMonth(),
            ]
        );

        // 4. Initial Audit Trail
        AuditTrail::log(
            eventType: 'SYSTEM_INITIALIZED',
            description: 'Sistem e-Perolehan Universal & Konfigurasi White-label dimulakan dengan jayanya.',
            userId: $admin->id
        );

        // 5. Tenders Across Categories
        // Tender 1: Bekalan (Active / Open)
        $tender1 = Tender::firstOrCreate(
            ['reference_no' => 'TDR-2026-0001'],
            [
                'title' => 'Pembekalan & Pemasangan Peralatan Pelayan Pusat Data dan Storan NVMe Berprestasi Tinggi',
                'slug' => 'pembekalan-pemasangan-pelayan-pusat-data-nvme',
                'category' => 'supply',
                'procurement_type' => 'open',
                'description' => 'Membekal, menghantar, memasang, menguji, dan mentauliahkan pelayan pusat data berkeupayaan tinggi serta storan rangkaian NVMe untuk menampung integrasi data raya perolehan.',
                'scope_of_work' => "1. Membekalkan 4 unit Pelayan Rack 2U Enterprise berspesifikasi pemproses 64-core.\n2. Membekalkan Storan Rangkaian Berkapasiti 200TB NVMe all-flash.\n3. Perkhidmatan sokongan teknikal perkakasan selama 36 bulan (24/7 on-site support).",
                'eligibility_criteria' => "1. Syarikat berdaftar SSM yang masih sah.\n2. Sijil Kementerian Kewangan (MOF) di bawah Kod Bidang 210101 (Hardware & Servers).\n3. Mempunyai modal berbayar sekurang-kurangnya RM 100,000.00.",
                'estimated_budget' => 450000.00,
                'document_price' => 50.00,
                'tender_deposit_amount' => 5000.00,
                'published_at' => now()->subDays(5),
                'closing_at' => now()->addDays(14),
                'status' => 'published',
                'min_technical_score' => 70,
                'created_by_user_id' => $urusSetia->id,
            ]
        );

        // Tender 2: Perkhidmatan (Box Opened / Evaluating Technical)
        $tender2 = Tender::firstOrCreate(
            ['reference_no' => 'TDR-2026-0002'],
            [
                'title' => 'Perkhidmatan Penyelenggaraan Sistem Aplikasi Awan dan Pusat Operasi Keselamatan Siber (SOC)',
                'slug' => 'penyelenggaraan-sistem-aplikasi-awan-keselamatan-siber-soc',
                'category' => 'service',
                'procurement_type' => 'open',
                'description' => 'Perkhidmatan pengurusan pemantauan keselamatan siber secara proaktif dan penyelenggaraan infrastruktur awan selama 24 bulan.',
                'scope_of_work' => "1. Pemantauan Pusat Operasi Keselamatan (SOC) 24x7x365 dengan SLA tindak balas 15 minit.\n2. Pengurusan tambalan sekuriti (patching) dan penentukuran WAF berkala.\n3. Laporan audit keselamatan berkala dwibulanan.",
                'eligibility_criteria' => "1. Berdaftar SSM dan MOF Kod Bidang 210103 / 210104 (Software & Security).\n2. Memiliki persijilan ISO/IEC 27001.\n3. Pasukan jurutera bertauliah (CISSP, CEH, atau setaraf).",
                'estimated_budget' => 380000.00,
                'document_price' => 100.00,
                'tender_deposit_amount' => 0.00,
                'published_at' => now()->subDays(20),
                'closing_at' => now()->subDays(2),
                'status' => 'box_opened',
                'dual_witness_opened_at' => now()->subDay(),
                'min_technical_score' => 75,
                'created_by_user_id' => $urusSetia->id,
            ]
        );

        // Tender 3: Kerja / Pembinaan (Awarded / Completed & Public Board)
        $tender3 = Tender::firstOrCreate(
            ['reference_no' => 'TDR-2026-0003'],
            [
                'title' => 'Kerja-kerja Naik Taraf Infrastruktur Rangkaian dan Kemudahan Pejabat Pintar Bersepadu',
                'slug' => 'kerja-naik-taraf-infrastruktur-rangkaian-pejabat-pintar',
                'category' => 'work',
                'procurement_type' => 'open',
                'description' => 'Kerja pengubahsuaian sivil dan pendawaian rangkaian gentian optik pejabat pintar merangkumi sistem automasi bilik mesyuarat dan kawalan capaian biometrik.',
                'scope_of_work' => "1. Pemasangan kabel optik OM4 dan 120 nod rangkaian Cat6A.\n2. Naik taraf kemudahan bilik mesyuarat pintar dengan sistem telepersidangan.\n3. Pemasangan sistem kawalan pintu pintar dan CCTV AI.",
                'eligibility_criteria' => "1. Berdaftar SSM dan CIDB sekurang-kurangnya Gred G3 (Kategori ME / CE).\n2. Mempunyai rekod menyiapkan projek serupa bernilai sekurang-kurangnya RM 200,000 dalam tempoh 3 tahun terkini.",
                'estimated_budget' => 280000.00,
                'document_price' => 50.00,
                'tender_deposit_amount' => 2500.00,
                'published_at' => now()->subDays(45),
                'closing_at' => now()->subDays(15),
                'status' => 'awarded',
                'dual_witness_opened_at' => now()->subDays(14),
                'awarded_at' => now()->subDays(5),
                'award_notes' => 'Lembaga Perolehan bersetuju sebulat suara menganugerahkan kontrak kepada Bina Maju Construction Sdn Bhd berasaskan pematuhan teknikal 100% dan tawaran harga paling wajar dan kompetitif.',
                'min_technical_score' => 70,
                'created_by_user_id' => $urusSetia->id,
            ]
        );

        // Tender 4: Perkhidmatan Perunding (Active / Open)
        $tender4 = Tender::firstOrCreate(
            ['reference_no' => 'TDR-2026-0004'],
            [
                'title' => 'Perkhidmatan Perunding Pengauditan Keselamatan Maklumat dan Tadbir Urus Data ISO/IEC 27001:2022',
                'slug' => 'perunding-pengauditan-keselamatan-maklumat-iso27001',
                'category' => 'consultancy',
                'procurement_type' => 'open',
                'description' => 'Kajian pengauditan, analisis jurang (gap analysis), dan penyediaan kerangka kerja tadbir urus perlindungan data organisasi.',
                'scope_of_work' => "1. Pelaksanaan gap analysis terhadap 93 kawalan keselamatan maklumat ISO 27001:2022.\n2. Bengkel pengurusan risiko dan latihan pematuhan kakitangan.\n3. Audit simulasi persijilan (mock certification audit).",
                'eligibility_criteria' => "1. Berdaftar dengan Kementerian Kewangan (MOF) di bawah Kod Perundingan Pengurusan.\n2. Mempunyai sekurang-kurangnya 2 juruaudit berdaftar IRCA ISO 27001 Lead Auditor.",
                'estimated_budget' => 120000.00,
                'document_price' => 0.00, // Percuma
                'tender_deposit_amount' => 0.00,
                'published_at' => now()->subDays(2),
                'closing_at' => now()->addDays(21),
                'status' => 'published',
                'min_technical_score' => 80,
                'created_by_user_id' => $urusSetia->id,
            ]
        );

        // 6. Purchases & Bids Setup for Tender 2 (Evaluating) & Tender 3 (Awarded)
        // Purchases for Tender 2
        $p2v1 = TenderPurchase::firstOrCreate(
            ['tender_id' => $tender2->id, 'vendor_id' => $vendor1->id],
            [
                'purchase_reference' => 'AP-2026-T2V1',
                'amount' => 100.00,
                'status' => 'paid',
                'affanpay_bill_id' => 'BILL-T2-001',
                'affanpay_payment_ref' => 'AFFAN-PAY-8811',
                'idempotency_key' => (string) Str::uuid(),
                'paid_at' => now()->subDays(10),
            ]
        );

        $p2v2 = TenderPurchase::firstOrCreate(
            ['tender_id' => $tender2->id, 'vendor_id' => $vendor2->id],
            [
                'purchase_reference' => 'AP-2026-T2V2',
                'amount' => 100.00,
                'status' => 'paid',
                'affanpay_bill_id' => 'BILL-T2-002',
                'affanpay_payment_ref' => 'AFFAN-PAY-8822',
                'idempotency_key' => (string) Str::uuid(),
                'paid_at' => now()->subDays(9),
            ]
        );

        // Bids for Tender 2 (Box opened with 2 witnesses)
        $bid2v1 = Bid::firstOrCreate(
            ['tender_id' => $tender2->id, 'vendor_id' => $vendor1->id],
            [
                'bid_reference' => 'BID-2026-T2-A',
                'blind_code' => 'PEMBIDA-A',
                'encrypted_bid_amount' => Crypt::encryptString('360000.00'),
                'decrypted_bid_amount' => 360000.00,
                'completion_duration_months' => 24,
                'technical_summary' => 'Cadangan komprehensif mengintegrasikan pasukan jurutera tempatan dengan ketersediaan sokongan 24/7.',
                'status' => 'opened',
                'sha256_package_hash' => hash('sha256', 'BID2V1-PACKAGE-SALT'),
                'submitted_at' => now()->subDays(3),
                'opened_at' => now()->subDay(),
                'technical_score' => 82.50,
            ]
        );

        $bid2v2 = Bid::firstOrCreate(
            ['tender_id' => $tender2->id, 'vendor_id' => $vendor2->id],
            [
                'bid_reference' => 'BID-2026-T2-B',
                'blind_code' => 'PEMBIDA-B',
                'encrypted_bid_amount' => Crypt::encryptString('345000.00'),
                'decrypted_bid_amount' => 345000.00,
                'completion_duration_months' => 24,
                'technical_summary' => 'Pusat operasi SOC berasaskan platform awan dengan AI SIEM detection automatik.',
                'status' => 'opened',
                'sha256_package_hash' => hash('sha256', 'BID2V2-PACKAGE-SALT'),
                'submitted_at' => now()->subDays(3),
                'opened_at' => now()->subDay(),
                'technical_score' => 88.00,
            ]
        );

        // Dual witnesses for Tender 2
        TenderBoxWitness::firstOrCreate(
            ['tender_id' => $tender2->id, 'user_id' => $saksi1->id],
            [
                'witness_role' => 'saksi_pembuka',
                'digital_signature_hash' => hash('sha256', "WITNESS|{$tender2->reference_no}|{$saksi1->id}"),
                'ip_address' => '10.0.0.15',
                'user_agent' => 'Mozilla/5.0 Government Portal',
                'witness_remarks' => 'Semua sampul bidaan digital didapati lengkap dan disahkan sebelum pembukaan.',
                'confirmed_at' => now()->subDay(),
            ]
        );

        TenderBoxWitness::firstOrCreate(
            ['tender_id' => $tender2->id, 'user_id' => $saksi2->id],
            [
                'witness_role' => 'saksi_pembuka',
                'digital_signature_hash' => hash('sha256', "WITNESS|{$tender2->reference_no}|{$saksi2->id}"),
                'ip_address' => '10.0.0.18',
                'user_agent' => 'Mozilla/5.0 Government Portal',
                'witness_remarks' => 'Saksi kedua mengesahkan integriti peti tender dan kunci kriptografi dinyahsulitkan.',
                'confirmed_at' => now()->subDay(),
            ]
        );

        // Technical Evaluations for Tender 2
        Evaluation::firstOrCreate(
            ['tender_id' => $tender2->id, 'bid_id' => $bid2v1->id, 'evaluator_user_id' => $penilaiTeknikal->id, 'evaluation_type' => 'technical'],
            [
                'score_criteria_1' => 35.0,
                'score_criteria_2' => 23.5,
                'score_criteria_3' => 24.0,
                'total_score' => 82.50,
                'is_passed' => true,
                'comments' => 'Memenuhi spesifikasi teknikal utama.',
                'completed_at' => now()->subHours(12),
                'digital_sign_hash' => hash('sha256', "EVAL|TECH|{$bid2v1->id}"),
            ]
        );

        Evaluation::firstOrCreate(
            ['tender_id' => $tender2->id, 'bid_id' => $bid2v2->id, 'evaluator_user_id' => $penilaiTeknikal->id, 'evaluation_type' => 'technical'],
            [
                'score_criteria_1' => 38.0,
                'score_criteria_2' => 25.0,
                'score_criteria_3' => 25.0,
                'total_score' => 88.00,
                'is_passed' => true,
                'comments' => 'Metodologi dan kepakaran pasukan keselamatan siber sangat cemerlang.',
                'completed_at' => now()->subHours(11),
                'digital_sign_hash' => hash('sha256', "EVAL|TECH|{$bid2v2->id}"),
            ]
        );

        // Purchases & Bids Setup for Tender 3 (Awarded)
        $p3v1 = TenderPurchase::firstOrCreate(
            ['tender_id' => $tender3->id, 'vendor_id' => $vendor1->id],
            [
                'purchase_reference' => 'AP-2026-T3V1',
                'amount' => 50.00,
                'status' => 'paid',
                'affanpay_bill_id' => 'BILL-T3-001',
                'affanpay_payment_ref' => 'AFFAN-PAY-9911',
                'idempotency_key' => (string) Str::uuid(),
                'paid_at' => now()->subDays(25),
            ]
        );

        $bid3v1 = Bid::firstOrCreate(
            ['tender_id' => $tender3->id, 'vendor_id' => $vendor1->id],
            [
                'bid_reference' => 'BID-2026-T3-A',
                'blind_code' => 'PEMBIDA-A',
                'encrypted_bid_amount' => Crypt::encryptString('265000.00'),
                'decrypted_bid_amount' => 265000.00,
                'completion_duration_months' => 6,
                'technical_summary' => 'Pelan pelaksanaan pengubahsuaian dengan jaminan bekalan bahan premium dan pematuhan jadual kerja.',
                'status' => 'awarded',
                'sha256_package_hash' => hash('sha256', 'BID3V1-PACKAGE-SALT'),
                'submitted_at' => now()->subDays(18),
                'opened_at' => now()->subDays(14),
                'technical_score' => 92.00,
                'financial_score' => 100.00,
                'total_score' => 94.40, // (92 * 0.7) + (100 * 0.3) = 64.4 + 30 = 94.4
            ]
        );

        // Link winning bid to Tender 3
        $tender3->update([
            'awarded_bid_id' => $bid3v1->id,
        ]);

        // Generate PDF reports for seeded documents
        $pdfService = app(PdfReportService::class);
        try {
            $pdfService->generatePurchaseReceiptPdf($p2v1);
            $pdfService->generatePurchaseReceiptPdf($p3v1);
            $pdfService->generateBidSubmissionSlipPdf($bid3v1);
            $pdfService->generateTenderOpeningSlipPdf($tender2);
            $pdfService->generateTenderOpeningSlipPdf($tender3);
            $pdfService->generateEvaluationReportPdf($tender3);
            $pdfService->generateSstLetterPdf($tender3);
            $pdfService->generateTenderNoticePdf($tender1);
            $pdfService->generateTenderNoticePdf($tender2);
            $pdfService->generateTenderNoticePdf($tender3);
            $pdfService->generateTenderNoticePdf($tender4);
        } catch (\Exception $e) {
            // PDF generation might omit font files in lightweight testing environments
        }

        // Final Audit Trail Log
        AuditTrail::log(
            eventType: 'DATABASE_SEED_COMPLETED',
            description: 'Pangkalan data telah diisi dengan data perolehan pelbagai kategori, pembekal sah, serta proses bidaan bertutup.',
            userId: $admin->id
        );
    }
}
