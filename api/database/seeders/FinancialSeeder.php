<?php

namespace Database\Seeders;

use App\Models\HousingCommunity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinancialSeeder extends Seeder
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

        $vendorId = DB::table('vendors')->insertGetId([
            'name' => 'Eko Čistač d.o.o.',
            'tax_number' => '108899441',
            'registration_number' => '21344567',
            'contact_name' => 'Sanja Nikolić',
            'email' => 'sanja@ekocistac.rs',
            'phone' => '+38111255888',
            'address' => 'Vojvode Stepe 220, Beograd',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $contractDocumentId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Ugovor o održavanju higijene zajedničkih prostorija',
            'category' => 'ugovor',
            'storage_path' => 'storage/documents/ugovor_odrzavanje_higijene_2024.pdf',
            'issued_at' => Carbon::create(2024, 1, 10),
            'metadata' => json_encode(['duration_months' => 12]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $contractId = DB::table('contracts')->insertGetId([
            'vendor_id' => $vendorId,
            'housing_community_id' => $communityId,
            'title' => 'Usluge čišćenja i održavanja zajedničkih prostorija',
            'description' => 'Čišćenje stubišta, lifta i podzemne garaže dva puta nedeljno.',
            'start_date' => Carbon::create(2024, 1, 15),
            'end_date' => Carbon::create(2024, 12, 31),
            'amount' => 150000,
            'payment_interval' => 'monthly',
            'status' => 'active',
            'document_id' => $contractDocumentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $expenseCategories = [
            'Redovno održavanje' => 'Troškovi čišćenja, održavanja lifta i komunalnih usluga.',
            'Investiciona ulaganja' => 'Radovi veće vrednosti - fasada, krov, instalacije.',
        ];

        $categoryIds = [];
        foreach ($expenseCategories as $name => $description) {
            $categoryIds[$name] = DB::table('expense_categories')->insertGetId([
                'name' => $name,
                'description' => $description,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $cleaningExpenseId = DB::table('expenses')->insertGetId([
            'housing_community_id' => $communityId,
            'expense_category_id' => $categoryIds['Redovno održavanje'],
            'contract_id' => $contractId,
            'unit_id' => null,
            'type' => 'recurring',
            'status' => 'approved',
            'amount' => 150000,
            'incurred_on' => Carbon::create(2024, 2, 1),
            'due_date' => Carbon::create(2024, 2, 15),
            'description' => 'Mesečna faktura za usluge čišćenja februarski ciklus.',
            'document_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('expenses')->insert([
            'housing_community_id' => $communityId,
            'expense_category_id' => $categoryIds['Investiciona ulaganja'],
            'contract_id' => null,
            'unit_id' => null,
            'type' => 'one_time',
            'status' => 'pending',
            'amount' => 820000,
            'incurred_on' => Carbon::create(2024, 3, 18),
            'due_date' => Carbon::create(2024, 4, 10),
            'description' => 'Avans za sanaciju fasade po programu održavanja.',
            'document_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $bankAccountId = DB::table('bank_accounts')->insertGetId([
            'housing_community_id' => $communityId,
            'bank_name' => 'Komercijalna banka',
            'account_number' => '205-0000001234567-89',
            'currency' => 'RSD',
            'opening_balance' => 250000,
            'current_balance' => 470000,
            'metadata' => json_encode(['account_type' => 'tekuci račun stambene zajednice']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('bank_transactions')->insert([
            [
                'bank_account_id' => $bankAccountId,
                'owner_id' => DB::table('owners')->where('full_name', 'Jovana Petrović')->value('id'),
                'unit_id' => DB::table('units')->where('identifier', 'A1')->value('id'),
                'expense_id' => null,
                'direction' => 'credit',
                'amount' => 15000,
                'transaction_date' => Carbon::create(2024, 2, 5),
                'value_date' => Carbon::create(2024, 2, 5),
                'reference_number' => '97-2024-00045',
                'purpose_code' => 'upi',
                'counterparty_name' => 'Jovana Petrović',
                'status' => 'recorded',
                'raw_payload' => json_encode(['payment_model' => '97', 'call_number' => '20240205-001']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'bank_account_id' => $bankAccountId,
                'owner_id' => null,
                'unit_id' => null,
                'expense_id' => $cleaningExpenseId,
                'direction' => 'debit',
                'amount' => 150000,
                'transaction_date' => Carbon::create(2024, 2, 16),
                'value_date' => Carbon::create(2024, 2, 16),
                'reference_number' => 'MP-2024-00089',
                'purpose_code' => 'ost',
                'counterparty_name' => 'Eko Čistač d.o.o.',
                'status' => 'recorded',
                'raw_payload' => json_encode(['payment_linked' => 'faktura 02/2024']),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $insuranceDocId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Polisa osiguranja zajedničkih delova 2024-2025',
            'category' => 'osiguranje',
            'storage_path' => 'storage/documents/polisa_osiguranje_2024.pdf',
            'issued_at' => Carbon::create(2024, 1, 5),
            'metadata' => json_encode(['insurer' => 'Dunav osiguranje']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $policyId = DB::table('insurance_policies')->insertGetId([
            'housing_community_id' => $communityId,
            'insurer_name' => 'Dunav osiguranje',
            'policy_number' => 'POL-2024-7781',
            'coverage_description' => 'Osiguranje od požara, poplava i odgovornosti prema trećim licima.',
            'start_date' => Carbon::create(2024, 1, 15),
            'end_date' => Carbon::create(2025, 1, 14),
            'premium_amount' => 195000,
            'document_id' => $insuranceDocId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('liability_claims')->insert([
            'housing_community_id' => $communityId,
            'insurance_policy_id' => $policyId,
            'incident_date' => Carbon::create(2023, 12, 5),
            'description' => 'Šteta na parkiranom vozilu usled odpadanja dela fasade.',
            'claim_amount' => 280000,
            'status' => 'in_review',
            'document_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $localUnitId = DB::table('units')->where('identifier', 'L1')->value('id');

        $tenancyDocumentId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Ugovor o zakupu lokala L1',
            'category' => 'zakup',
            'storage_path' => 'storage/documents/ugovor_zakup_L1.pdf',
            'issued_at' => Carbon::create(2023, 11, 20),
            'metadata' => json_encode(['tenant' => 'Studio Move d.o.o.']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $tenancyId = DB::table('tenancies')->insertGetId([
            'unit_id' => $localUnitId,
            'tenant_name' => 'Studio Move d.o.o.',
            'tenant_contact' => '+38111456001',
            'rent_amount' => 950.00,
            'lease_start' => Carbon::create(2023, 12, 1),
            'lease_end' => Carbon::create(2025, 11, 30),
            'status' => 'active',
            'document_id' => $tenancyDocumentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('rental_payments')->insert([
            [
                'tenancy_id' => $tenancyId,
                'due_date' => Carbon::create(2024, 2, 1),
                'amount' => 950.00,
                'paid_at' => Carbon::create(2024, 1, 30),
                'status' => 'paid',
                'reference' => 'ZAKUP-2024-02',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'tenancy_id' => $tenancyId,
                'due_date' => Carbon::create(2024, 3, 1),
                'amount' => 950.00,
                'paid_at' => null,
                'status' => 'overdue',
                'reference' => 'ZAKUP-2024-03',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('eviction_cases')->insert([
            'tenancy_id' => $tenancyId,
            'reason' => 'Kašnjenje sa uplatom zakupnine preko 30 dana',
            'opened_at' => Carbon::create(2024, 4, 5),
            'status' => 'monitoring',
            'decision_date' => null,
            'outcome' => null,
            'document_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
