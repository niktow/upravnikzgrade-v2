// Pronađi oba vlasnika sa imenom "Košanin Dragan"
$owners = DB::table('owners')
    ->where('full_name', 'Košanin Dragan')
    ->orderBy('id')
    ->get();

if ($owners->count() !== 2) {
    echo "Greška: Očekivano 2 vlasnika, pronađeno " . $owners->count() . "\n";
    exit(1);
}

$keepId = $owners[0]->id;
$deleteId = $owners[1]->id;

echo "Spajanje vlasnika:\n";
echo "- Zadržavam ID: {$keepId}\n";
echo "- Brišem ID: {$deleteId}\n";

// Ažuriraj sve veze drugog vlasnika da pokazuju na prvog
$updated = DB::table('owner_unit')
    ->where('owner_id', $deleteId)
    ->update(['owner_id' => $keepId]);

echo "- Ažurirano {$updated} veza u owner_unit tabeli\n";

// Obriši drugog vlasnika
DB::table('owners')->where('id', $deleteId)->delete();

echo "✅ Spojeno! Košanin Dragan (ID {$keepId}) sada ima obe jedinice.\n";

// Proveri rezultat
$units = DB::table('owner_unit')
    ->join('units', 'owner_unit.unit_id', '=', 'units.id')
    ->where('owner_unit.owner_id', $keepId)
    ->select('units.identifier', 'units.type')
    ->get();

echo "\nJedinice vlasnika Košanin Dragan:\n";
foreach ($units as $unit) {
    echo "- {$unit->identifier} ({$unit->type})\n";
}
