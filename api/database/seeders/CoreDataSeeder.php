<?php

namespace Database\Seeders;

use App\Models\HousingCommunity;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $communityId = DB::table('housing_communities')->insertGetId([
            'name' => 'Stambena zajednica Nemanjina 12',
            'address_line' => 'Nemanjina 12',
            'city' => 'Beograd',
            'postal_code' => '11000',
            'registry_number' => 'BG-2024-001',
            'tax_id' => '109876543',
            'bank_account_number' => '205-0000001234567-89',
            'contact_email' => 'uprava@nemanjina12.rs',
            'contact_phone' => '+381601234567',
            'established_at' => Carbon::create(2018, 5, 15),
            'status' => 'active',
            'metadata' => json_encode([
                'building_type' => 'stambeno-poslovna zgrada',
                'units_total' => 24,
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $lawDocumentId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Zakon o stanovanju i održavanju zgrada',
            'category' => 'zakon',
            'storage_path' => 'doc/Zakon_o_stanovanju_i_odrzavanju_zgrada.pdf',
            'issued_at' => Carbon::create(2020, 2, 1),
            'metadata' => json_encode(['source' => 'Službeni glasnik RS 104/2016, 9/2020']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $houseRuleDocId = DB::table('documents')->insertGetId([
            'documentable_type' => HousingCommunity::class,
            'documentable_id' => $communityId,
            'title' => 'Kućni red – Nemanjina 12',
            'category' => 'kucni_red',
            'storage_path' => 'storage/documents/house_rules_nemanjina12.pdf',
            'issued_at' => Carbon::now()->toDateString(),
            'metadata' => json_encode(['drafted_by' => 'Upravnik', 'legal_reference' => 'član 76 ZOSOZ']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('community_rules')->insert([
            'housing_community_id' => $communityId,
            'version' => '1.0.0',
            'status' => 'adopted',
            'adopted_at' => Carbon::create(2023, 11, 30),
            'content_summary' => 'Pravila vlasnika sa obavezama iz čl. 17 ZOSOZ i evidencijom zajedničkih delova.',
            'document_id' => $lawDocumentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('house_rules')->insert([
            'housing_community_id' => $communityId,
            'status' => 'effective',
            'effective_from' => Carbon::create(2024, 1, 1),
            'published_at' => Carbon::create(2023, 12, 15, 9, 0),
            'description' => 'Opšta pravila kućnog reda usklađena sa odlukom grada Beograda (član 76 ZOSOZ).',
            'document_id' => $houseRuleDocId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('notice_board_posts')->insert([
            'housing_community_id' => $communityId,
            'title' => 'Najava skupštine stambene zajednice',
            'category' => 'obaveštenje',
            'body' => 'Sednica skupštine biće održana 25. januara u 19h u holu prizemlja. Dnevni red uključuje usvajanje programa održavanja i izbor profesionalnog upravnika.',
            'author_name' => 'Upravnik',
            'posted_at' => Carbon::now()->subDays(2),
            'expires_at' => Carbon::now()->addDays(10),
            'is_published' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $owners = [
            [
                'full_name' => 'Jovana Petrović',
                'email' => 'jovana.petrovic@example.com',
                'phone' => '+38164111222',
                'address' => 'Nemanjina 12, Beograd',
                'national_id' => '0101990712345',
            ],
            [
                'full_name' => 'Marko Ilić',
                'email' => 'marko.ilic@example.com',
                'phone' => '+381631234567',
                'address' => 'Bulevar oslobođenja 45, Beograd',
                'national_id' => '1205986712345',
            ],
            [
                'full_name' => 'Lokali D.O.O.',
                'email' => 'kontakt@lokali.rs',
                'phone' => '+381113456789',
                'address' => 'Savska 5, Beograd',
                'national_id' => 'SRB-PIB-108765432',
            ],
        ];

        $ownerIds = [];
        foreach ($owners as $owner) {
            $ownerIds[] = DB::table('owners')->insertGetId(array_merge($owner, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $units = [
            [
                'identifier' => 'A1',
                'type' => 'stan',
                'floor' => 'I',
                'area' => 72.50,
                'occupant_count' => 3,
            ],
            [
                'identifier' => 'A2',
                'type' => 'stan',
                'floor' => 'II',
                'area' => 64.20,
                'occupant_count' => 2,
            ],
            [
                'identifier' => 'L1',
                'type' => 'lokal',
                'floor' => 'Prizemlje',
                'area' => 110.00,
                'occupant_count' => 0,
            ],
        ];

        $unitIds = [];
        foreach ($units as $unit) {
            $unitIds[] = DB::table('units')->insertGetId(array_merge($unit, [
                'housing_community_id' => $communityId,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $ownerships = [
            ['owner_id' => $ownerIds[0], 'unit_id' => $unitIds[0], 'ownership_share' => 100.00],
            ['owner_id' => $ownerIds[1], 'unit_id' => $unitIds[1], 'ownership_share' => 100.00],
            ['owner_id' => $ownerIds[2], 'unit_id' => $unitIds[2], 'ownership_share' => 100.00],
        ];

        foreach ($ownerships as $ownership) {
            DB::table('owner_unit')->insert(array_merge($ownership, [
                'starts_at' => Carbon::create(2020, 1, 1),
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }
}
