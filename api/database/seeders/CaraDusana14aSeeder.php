<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaraDusana14aSeeder extends Seeder
{
    /**
     * Run the database seeds - Cara Dušana 14a, Lamela 2
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Pronađi postojeću zajednicu (ne brišemo je)
        $communityId = DB::table('housing_communities')
            ->where('name', 'Stambena zajednica  Cara Dusana 14a, Lamela 2')
            ->value('id');

        if (!$communityId) {
            $this->command->error('Stambena zajednica nije pronađena!');
            return;
        }

        // Obriši sve postojeće jedinice i vlasnike
        $this->command->info('Brisanje postojećih podataka...');
        
        // Prvo obriši relacije u pivot tabeli
        DB::table('owner_unit')->delete();
        
        // Zatim obriši jedinice
        DB::table('units')->where('housing_community_id', $communityId)->delete();
        
        // I na kraju vlasnike
        DB::table('owners')->delete();

        $this->command->info('Kreiranje vlasnika stanara...');

        // STANARI - Lista sa brojem članova
        $stanari = [
            ['ime' => 'Stojiljković Slobodan', 'broj_stana' => '4', 'clanovi' => 1],
            ['ime' => 'Pavlović Milica', 'broj_stana' => '2', 'clanovi' => 1],
            ['ime' => 'Košanin Dragan', 'broj_stana' => '3', 'clanovi' => 2],
            ['ime' => 'Popović Obrad', 'broj_stana' => '4', 'clanovi' => 2],
            ['ime' => 'Lazarević Stanica', 'broj_stana' => '5', 'clanovi' => 1],
            ['ime' => 'Vojinović Kosana', 'broj_stana' => '6', 'clanovi' => 1],
            ['ime' => 'Aleksijević Milena', 'broj_stana' => '7', 'clanovi' => 4],
            ['ime' => 'Spasojević Vera', 'broj_stana' => '8', 'clanovi' => 1],
            ['ime' => 'Radovanović Bojana', 'broj_stana' => '9', 'clanovi' => 2],
            ['ime' => 'Pavlović Slobodan', 'broj_stana' => '10', 'clanovi' => 3],
            ['ime' => 'Radović Radica', 'broj_stana' => '11', 'clanovi' => 2],
            ['ime' => 'Ilić Marija', 'broj_stana' => '12', 'clanovi' => 2],
            ['ime' => 'Tomić Ljiljana', 'broj_stana' => '13', 'clanovi' => 4],
            ['ime' => 'Arsenović Arsen', 'broj_stana' => '14', 'clanovi' => 2],
            ['ime' => 'Lazarević Rosa', 'broj_stana' => '15', 'clanovi' => 2],
            ['ime' => 'Gajić Valentina', 'broj_stana' => '16', 'clanovi' => 1],
            ['ime' => 'Biberčić Davor', 'broj_stana' => '17', 'clanovi' => 3],
            ['ime' => 'Vuković Andrija', 'broj_stana' => '18', 'clanovi' => 2],
            ['ime' => 'Nedić Miladim', 'broj_stana' => '19', 'clanovi' => 2],
            ['ime' => 'Tanasijević Boško', 'broj_stana' => '20', 'clanovi' => 2],
            ['ime' => 'Popov Mirjana', 'broj_stana' => '21', 'clanovi' => 1],
        ];

        // Kreiraj stanare i stanove
        foreach ($stanari as $stanar) {
            // Kreiraj vlasnika
            $ownerId = DB::table('owners')->insertGetId([
                'full_name' => $stanar['ime'],
                'email' => null,
                'phone' => null,
                'address' => 'Cara Dušana 14a, Lamela 2, Aranđelovac',
                'national_id' => null,
                'date_of_birth' => null,
                'notes' => 'Broj članova domaćinstva: ' . $stanar['clanovi'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Kreiraj stan
            $unitId = DB::table('units')->insertGetId([
                'housing_community_id' => $communityId,
                'identifier' => 'STAN-' . $stanar['broj_stana'],
                'type' => 'stan',
                'floor' => null,
                'area' => null,
                'occupant_count' => $stanar['clanovi'],
                'is_active' => true,
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Poveži vlasnika sa stanom (100% vlasništvo)
            DB::table('owner_unit')->insert([
                'owner_id' => $ownerId,
                'unit_id' => $unitId,
                'ownership_share' => 100.00,
                'starts_at' => Carbon::create(2020, 1, 1),
                'ends_at' => null,
                'obligation_notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Kreirano ' . count($stanari) . ' stanara i stanova.');

        // LOKALI - Lista vlasnika lokala
        $this->command->info('Kreiranje vlasnika lokala...');

        $lokali = [
            ['ime' => 'Lazarević Branko', 'adresa' => 'Aranđelovac'],
            ['ime' => 'Košanin Dragan', 'adresa' => 'Aranđelovac, Cara Dušana A 2/3'],
            ['ime' => 'Tešić Saša', 'adresa' => 'Beograd, Sime Milošević 3'],
            ['ime' => 'Pavlović Nenad', 'adresa' => 'Topola, Božurnja'],
            ['ime' => 'Igrutinović Živorad', 'adresa' => 'Cara Dušana, Aranđelovac'],
            ['ime' => 'Jakovljević Dragan', 'adresa' => 'Darosava'],
            ['ime' => 'Lukić Borislav', 'adresa' => 'Aranđelovac, Stefana Nemanje 17'],
            ['ime' => 'Stojiljković Nela', 'adresa' => 'Aranđelovac, Cara Dušana A 2/1'],
            ['ime' => 'Antonijević Mirko', 'adresa' => 'Aranđelovac'],
            ['ime' => 'Riznić Bojana', 'adresa' => 'Aranđelovac, Ilije Garašanina 2/36'],
        ];

        $lokalBroj = 1;
        foreach ($lokali as $lokal) {
            // Kreiraj vlasnika lokala
            $ownerId = DB::table('owners')->insertGetId([
                'full_name' => $lokal['ime'],
                'email' => null,
                'phone' => null,
                'address' => $lokal['adresa'],
                'national_id' => null,
                'date_of_birth' => null,
                'notes' => 'Vlasnik lokala',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Kreiraj lokal
            $unitId = DB::table('units')->insertGetId([
                'housing_community_id' => $communityId,
                'identifier' => 'LOKAL-' . $lokalBroj,
                'type' => 'lokal',
                'floor' => 'Prizemlje',
                'area' => null,
                'occupant_count' => 0,
                'is_active' => true,
                'notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Poveži vlasnika sa lokalom (100% vlasništvo)
            DB::table('owner_unit')->insert([
                'owner_id' => $ownerId,
                'unit_id' => $unitId,
                'ownership_share' => 100.00,
                'starts_at' => Carbon::create(2020, 1, 1),
                'ends_at' => null,
                'obligation_notes' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $lokalBroj++;
        }

        $this->command->info('Kreirano ' . count($lokali) . ' vlasnika lokala i lokala.');
        $this->command->info('Ukupno: ' . count($stanari) . ' stanova + ' . count($lokali) . ' lokala = ' . (count($stanari) + count($lokali)) . ' jedinica');
    }
}
