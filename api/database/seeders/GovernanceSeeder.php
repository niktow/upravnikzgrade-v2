<?php

namespace Database\Seeders;

use App\Models\HousingCommunity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernanceSeeder extends Seeder
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
        $owners = DB::table('owners')->get();
        $units = DB::table('units')->get()->keyBy('identifier');

        $meetingDocumentId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Poziv za skupštinu - januar 2024',
            'category' => 'poziv',
            'storage_path' => 'storage/documents/poziv_skupstina_jan2024.pdf',
            'issued_at' => Carbon::create(2023, 12, 20),
            'metadata' => json_encode(['legal_basis' => 'Član 48 ZOSOZ']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $assemblyMeetingId = DB::table('assembly_meetings')->insertGetId([
            'housing_community_id' => $communityId,
            'scheduled_for' => Carbon::create(2024, 1, 25, 19, 0),
            'location' => 'Hol prizemlja, Nemanjina 12',
            'agenda' => "1. Usvajanje programa održavanja\n2. Izbor profesionalnog upravnika\n3. Donošenje odluke o visini rezervnog fonda",
            'status' => 'scheduled',
            'called_by' => 'Odbor zgrade',
            'document_id' => $meetingDocumentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($owners as $index => $owner) {
            DB::table('meeting_attendees')->insert([
                'assembly_meeting_id' => $assemblyMeetingId,
                'owner_id' => $owner->id,
                'unit_id' => $index < $units->count() ? $units->values()[$index]->id : null,
                'representative_name' => $owner->full_name,
                'attendance_type' => 'owner',
                'is_present' => $index !== 2,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $decisionDocumentId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Zapisnik sa sednice - januar 2024',
            'category' => 'zapisnik',
            'storage_path' => 'storage/documents/zapisnik_sednica_jan2024.pdf',
            'issued_at' => Carbon::create(2024, 1, 26),
            'metadata' => json_encode(['notary' => 'Jelena Vuković']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $decisionId = DB::table('assembly_decisions')->insertGetId([
            'assembly_meeting_id' => $assemblyMeetingId,
            'title' => 'Usvajanje programa tekućeg i investicionog održavanja 2024',
            'description' => 'Program obuhvata sanaciju fasade, zamenu interfona i redovno servisiranje lifta.',
            'legal_basis' => 'Član 50 st. 1 i 2 ZOSOZ',
            'required_majority' => 'qualified',
            'votes_for' => 180,
            'votes_against' => 20,
            'votes_abstained' => 0,
            'status' => 'adopted',
            'effective_from' => Carbon::create(2024, 2, 1),
            'document_id' => $decisionDocumentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($owners as $owner) {
            DB::table('decision_votes')->insert([
                'assembly_decision_id' => $decisionId,
                'owner_id' => $owner->id,
                'unit_id' => DB::table('owner_unit')->where('owner_id', $owner->id)->value('unit_id'),
                'vote_value' => $owner->full_name === 'Lokali D.O.O.' ? 'against' : 'for',
                'weight' => $owner->full_name === 'Lokali D.O.O.' ? 20 : 90,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $managerId = DB::table('professional_managers')->insertGetId([
            'full_name' => 'Miloš Perić',
            'email' => 'milos.peric@prof-upravnik.rs',
            'phone' => '+381605551111',
            'company_name' => 'Profi Upravnik d.o.o.',
            'address' => 'Kraljice Natalije 45, Beograd',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $licenseId = DB::table('manager_licenses')->insertGetId([
            'professional_manager_id' => $managerId,
            'license_number' => 'UPR-2024-1199',
            'issued_by' => 'Ministarstvo građevinarstva',
            'issued_at' => Carbon::create(2022, 6, 10),
            'expires_at' => Carbon::create(2025, 6, 10),
            'status' => 'valid',
            'insurance_policy_number' => 'DDOR-3678891',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $assignmentId = DB::table('manager_assignments')->insertGetId([
            'housing_community_id' => $communityId,
            'professional_manager_id' => $managerId,
            'starts_at' => Carbon::create(2024, 2, 1),
            'contract_reference' => 'Ugovor 02-2024',
            'appointment_basis' => 'Odluka skupštine 01/2024',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('manager_reports')->insert([
            'manager_assignment_id' => $assignmentId,
            'period_start' => Carbon::create(2024, 2, 1),
            'period_end' => Carbon::create(2024, 3, 1),
            'summary' => 'Upravnik izvršio popis rezervnog fonda, ugovorio servis lifta i pripremio plan invertiranja.',
            'financial_overview' => json_encode(['reserve_balance' => 450000, 'planned_expenses' => 180000]),
            'submitted_at' => Carbon::create(2024, 3, 5),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $contractorId = DB::table('maintenance_contractors')->insertGetId([
            'name' => 'Servis Lift Beograd',
            'contact_person' => 'Ivana Lukić',
            'email' => 'ivana@servislift.rs',
            'phone' => '+38111222333',
            'license_number' => 'SLB-7782',
            'service_types' => 'servis lifta, hitne intervencije',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $programDocId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Program održavanja 2024',
            'category' => 'program_odrzavanja',
            'storage_path' => 'storage/documents/program_odrzavanja_2024.pdf',
            'issued_at' => Carbon::create(2024, 1, 26),
            'metadata' => json_encode(['assembly_decision' => '01-2024']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $maintenanceProgramId = DB::table('maintenance_programs')->insertGetId([
            'housing_community_id' => $communityId,
            'title' => 'Program održavanja 2024',
            'period_start' => Carbon::create(2024, 2, 1),
            'period_end' => Carbon::create(2024, 12, 31),
            'scope' => 'Sanacija fasade, servis lifta, revizija hidrantske mreže, redovno čišćenje oluka.',
            'budget_amount' => 950000,
            'status' => 'adopted',
            'document_id' => $programDocId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('maintenance_tasks')->insert([
            [
                'maintenance_program_id' => $maintenanceProgramId,
                'maintenance_contractor_id' => $contractorId,
                'title' => 'Godišnji servis lifta',
                'category' => 'redovno održavanje',
                'description' => 'Obavezni godišnji servis lifta u skladu sa Pravilnikom o održavanju liftova.',
                'planned_date' => Carbon::create(2024, 3, 15),
                'cost' => 120000,
                'status' => 'planned',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'maintenance_program_id' => $maintenanceProgramId,
                'maintenance_contractor_id' => null,
                'title' => 'Revizija hidrantske mreže',
                'category' => 'zakonska obaveza',
                'description' => 'Provera ispravnosti hidrantske mreže radi zaštite od požara.',
                'planned_date' => Carbon::create(2024, 4, 10),
                'cost' => 80000,
                'status' => 'planned',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('emergency_incidents')->insert([
            'housing_community_id' => $communityId,
            'reported_at' => Carbon::create(2023, 12, 5, 22, 45),
            'reported_by' => 'Stan A2',
            'description' => 'Puknuće vodovodne cevi u vertikali kupatila.',
            'severity' => 'high',
            'resolved_at' => Carbon::create(2023, 12, 6, 10, 30),
            'actions_taken' => 'Hitna intervencija vodoinstalatera i privremena sanacija zida.',
            'document_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $reserveDocId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Odluka o visini rezervnog fonda',
            'category' => 'rezervni_fond',
            'storage_path' => 'storage/documents/rezervni_fond_odluka_2024.pdf',
            'issued_at' => Carbon::create(2024, 1, 26),
            'metadata' => json_encode(['legal_basis' => 'Član 58 ZOSOZ']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $reserveFundId = DB::table('reserve_funds')->insertGetId([
            'housing_community_id' => $communityId,
            'name' => 'Rezervni fond Nemanjina 12',
            'minimum_monthly_contribution' => 120.00,
            'currency' => 'RSD',
            'bank_account_reference' => '205-0000001234567-89',
            'document_id' => $reserveDocId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($owners as $owner) {
            $unitId = DB::table('owner_unit')->where('owner_id', $owner->id)->value('unit_id');
            DB::table('reserve_contributions')->insert([
                'reserve_fund_id' => $reserveFundId,
                'owner_id' => $owner->id,
                'unit_id' => $unitId,
                'due_date' => Carbon::create(2024, 2, 10),
                'amount' => $owner->full_name === 'Lokali D.O.O.' ? 2400 : 1800,
                'paid_at' => $owner->full_name === 'Lokali D.O.O.' ? null : Carbon::create(2024, 2, 5),
                'status' => $owner->full_name === 'Lokali D.O.O.' ? 'pending' : 'paid',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('reserve_transactions')->insert([
            [
                'reserve_fund_id' => $reserveFundId,
                'transaction_type' => 'credit',
                'amount' => 3600,
                'occurred_on' => Carbon::create(2024, 2, 5),
                'reference' => 'RF-PRILIV-2024-02',
                'description' => 'Uplate vlasnika stanova za februar.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'reserve_fund_id' => $reserveFundId,
                'transaction_type' => 'debit',
                'amount' => 120000,
                'occurred_on' => Carbon::create(2024, 3, 20),
                'reference' => 'RF-ISPLATA-LIFT',
                'description' => 'Plaćanje servisa lifta po programu održavanja.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $offerId = DB::table('common_area_transfer_offers')->insertGetId([
            'housing_community_id' => $communityId,
            'description' => 'Ponuda za davanje u zakup zajedničke prostorije (bivša portirnica).',
            'proposed_to' => 'Lokali D.O.O.',
            'offer_date' => Carbon::create(2024, 1, 15),
            'expiry_date' => Carbon::create(2024, 2, 15),
            'price' => 450.00,
            'status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('right_of_first_refusal_events')->insert([
            'common_area_transfer_offer_id' => $offerId,
            'owner_id' => $owners->firstWhere('full_name', 'Jovana Petrović')->id ?? null,
            'notified_at' => Carbon::create(2024, 1, 16),
            'responded_at' => Carbon::create(2024, 1, 20),
            'response' => 'waived',
            'notes' => 'Vlasnik se odrekao prava preče kupovine u skladu sa članom 61 ZOSOZ.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
