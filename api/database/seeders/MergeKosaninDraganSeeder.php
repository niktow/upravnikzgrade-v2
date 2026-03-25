<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MergeKosaninDraganSeeder extends Seeder
{
    public function run(): void
    {
        // Pronađi oba vlasnika
        $owners = DB::table('owners')
            ->where('full_name', 'Košanin Dragan')
            ->orderBy('id')
            ->get();

        if ($owners->count() !== 2) {
            $this->command->error("Greška: Očekivano 2 vlasnika, pronađeno " . $owners->count());
            return;
        }

        $keepId = $owners[0]->id;
        $deleteId = $owners[1]->id;

        $this->command->info("Spajanje vlasnika:");
        $this->command->info("- Zadržavam ID: {$keepId}");
        $this->command->info("- Brišem ID: {$deleteId}");

        // Ažuriraj sve veze drugog vlasnika
        $updated = DB::table('owner_unit')
            ->where('owner_id', $deleteId)
            ->update(['owner_id' => $keepId]);

        $this->command->info("- Ažurirano {$updated} veza u owner_unit tabeli");

        // Obriši drugog vlasnika
        DB::table('owners')->where('id', $deleteId)->delete();

        $this->command->info("✅ Spojeno! Košanin Dragan (ID {$keepId}) sada ima obe jedinice.");

        // Proveri rezultat
        $units = DB::table('owner_unit')
            ->join('units', 'owner_unit.unit_id', '=', 'units.id')
            ->where('owner_unit.owner_id', $keepId)
            ->select('units.identifier', 'units.type')
            ->get();

        $this->command->info("\nJedinice vlasnika Košanin Dragan:");
        foreach ($units as $unit) {
            $this->command->info("- {$unit->identifier} ({$unit->type})");
        }
    }
}
