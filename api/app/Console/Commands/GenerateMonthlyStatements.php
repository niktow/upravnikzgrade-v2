<?php

namespace App\Console\Commands;

use App\Models\HousingCommunity;
use App\Services\BillingStatementGenerator;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateMonthlyStatements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate-statements 
                            {--period= : Period in YYYY-MM format (default: current month)}
                            {--community= : Housing community ID (default: all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly billing statements for housing communities';

    protected BillingStatementGenerator $generator;

    public function __construct(BillingStatementGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period') ?? Carbon::now()->format('Y-m');
        $communityId = $this->option('community');

        $this->info("Generisanje mesečnih obračuna za period: {$period}");

        // Validacija perioda
        try {
            $periodDate = Carbon::parse($period . '-01');
        } catch (\Exception $e) {
            $this->error("Neispravan format perioda. Koristite format: YYYY-MM");
            return 1;
        }

        // Dohvati zajednice
        $query = HousingCommunity::query()->where('status', 'active');
        
        if ($communityId) {
            $query->where('id', $communityId);
        }

        $communities = $query->get();

        if ($communities->isEmpty()) {
            $this->error('Nisu pronađene aktivne stambene zajednice.');
            return 1;
        }

        $this->info("Pronađeno {$communities->count()} stambenih zajednica.");

        $totalStatements = 0;
        $successCount = 0;
        $failedCount = 0;

        foreach ($communities as $community) {
            $this->line("Obrađujem: {$community->name}");

            $units = $community->units()->where('is_active', true)->get();
            
            if ($units->isEmpty()) {
                $this->warn("  Nema aktivnih stanova/lokala u ovoj zajednici.");
                continue;
            }

            $bar = $this->output->createProgressBar($units->count());
            $bar->start();

            foreach ($units as $unit) {
                try {
                    $pdf = $this->generator->generateForUnit($unit, $period);
                    $filepath = $this->generator->saveStatement($pdf, $period, $unit->identifier);
                    
                    $successCount++;
                    $totalStatements++;
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->error("  Greška za stan {$unit->identifier}: " . $e->getMessage());
                    $failedCount++;
                    $totalStatements++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info("Generisanje završeno!");
        $this->table(
            ['Ukupno', 'Uspešno', 'Neuspešno'],
            [[$totalStatements, $successCount, $failedCount]]
        );

        if ($failedCount > 0) {
            return 1;
        }

        return 0;
    }
}
