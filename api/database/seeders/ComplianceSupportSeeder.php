<?php

namespace Database\Seeders;

use App\Models\HousingCommunity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceSupportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $communityId = DB::table('housing_communities')->value('id');
        if (!$communityId) {
            return;
        }

        $now = Carbon::now();

        $inspectionDocumentId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Rešenje o inspekcijskom nadzoru',
            'category' => 'inspekcija',
            'storage_path' => 'storage/documents/inspekcija_pozar_2024.pdf',
            'issued_at' => Carbon::create(2024, 2, 12),
            'metadata' => json_encode(['inspectorate' => 'Sekretarijat za inspekcijske poslove']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $inspectionId = DB::table('inspections')->insertGetId([
            'housing_community_id' => $communityId,
            'inspection_type' => 'protivpožarna inspekcija',
            'conducted_by' => 'Gradska uprava Beograda',
            'scheduled_at' => Carbon::create(2024, 3, 1),
            'conducted_at' => Carbon::create(2024, 3, 7),
            'status' => 'completed',
            'findings' => 'Utvrđene nepravilnosti u označavanju evakuacionih puteva i istekao sertifikat hidrantske mreže.',
            'document_id' => $inspectionDocumentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $orderId = DB::table('inspection_orders')->insertGetId([
            'inspection_id' => $inspectionId,
            'order_reference' => '03-INS-2024/127',
            'description' => 'Otkloniti nepravilnosti i dostaviti dokaz o izvršenoj reviziji hidrantske mreže.',
            'issued_at' => Carbon::create(2024, 3, 12),
            'deadline' => Carbon::create(2024, 4, 30),
            'status' => 'open',
            'document_id' => $inspectionDocumentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('compliance_actions')->insert([
            [
                'inspection_order_id' => $orderId,
                'housing_community_id' => $communityId,
                'action_type' => 'Revizija hidrantske mreže',
                'description' => 'Ugovoriti ovlašćenu firmu i izvršiti reviziju hidrantske mreže.',
                'responsible_party' => 'Upravnik Miloš Perić',
                'due_date' => Carbon::create(2024, 4, 15),
                'completed_at' => null,
                'status' => 'in_progress',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'inspection_order_id' => $orderId,
                'housing_community_id' => $communityId,
                'action_type' => 'Označavanje evakuacionih puteva',
                'description' => 'Postaviti nove fotoluminiscentne table i plan evakuacije na svakoj etaži.',
                'responsible_party' => 'Odbor zgrade',
                'due_date' => Carbon::create(2024, 4, 30),
                'completed_at' => Carbon::create(2024, 4, 22),
                'status' => 'completed',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $programId = DB::table('housing_support_programs')->insertGetId([
            'name' => 'Program energetske sanacije fasada 2024',
            'program_type' => 'subvencija',
            'authority' => 'Grad Beograd',
            'funding_source' => 'Budžetska sredstva i EU fondovi',
            'application_window_start' => Carbon::create(2024, 2, 20),
            'application_window_end' => Carbon::create(2024, 4, 20),
            'description' => 'Subvencije do 50% troškova za unapređenje energetske efikasnosti višeporodičnih stambenih zgrada.',
            'status' => 'open',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $applicationId = DB::table('support_applications')->insertGetId([
            'housing_support_program_id' => $programId,
            'housing_community_id' => $communityId,
            'applicant_name' => 'Stambena zajednica Nemanjina 12',
            'applicant_contact' => 'uprava@nemanjina12.rs',
            'household_size' => 24,
            'household_income' => 0,
            'housing_status' => 'stambena zajednica',
            'submitted_at' => Carbon::create(2024, 3, 25),
            'status' => 'review',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('eligibility_reviews')->insert([
            'support_application_id' => $applicationId,
            'reviewer_name' => 'Ana Radosavljević',
            'review_date' => Carbon::create(2024, 4, 5),
            'decision' => 'preliminarily_approved',
            'notes' => 'Potrebno dostaviti dokaz o sopstvenom učešću od najmanje 30%.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $nonprofitLeaseId = DB::table('nonprofit_leases')->insertGetId([
            'housing_support_program_id' => $programId,
            'unit_id' => null,
            'tenant_name' => 'Udruženje stanara Nemanjina 12',
            'lease_start' => Carbon::create(2023, 9, 1),
            'lease_end' => Carbon::create(2025, 8, 31),
            'rent_amount' => 1.00,
            'status' => 'active',
            'document_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $tenancyId = DB::table('tenancies')->value('id');

        DB::table('support_subsidies')->insert([
            'support_application_id' => $applicationId,
            'tenancy_id' => $tenancyId,
            'owner_id' => DB::table('owners')->where('full_name', 'Jovana Petrović')->value('id'),
            'subsidy_type' => 'energetska_sanacija',
            'amount' => 410000,
            'start_date' => Carbon::create(2024, 5, 1),
            'end_date' => Carbon::create(2024, 12, 31),
            'status' => 'planned',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
