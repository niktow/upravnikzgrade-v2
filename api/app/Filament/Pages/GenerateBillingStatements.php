<?php

namespace App\Filament\Pages;

use App\Models\HousingCommunity;
use App\Models\Unit;
use App\Models\UnitBillingStatement;
use App\Models\UnitLedger;
use App\Services\BillingStatementGenerator;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class GenerateBillingStatements extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static string $view = 'filament.pages.generate-billing-statements';

    protected static ?string $navigationLabel = 'Generiši račune';

    protected static ?string $title = 'Generisanje mesečnih računa';

    protected static ?string $navigationGroup = 'Finansije';

    protected static ?int $navigationSort = 10;

    // Sakrij iz navigacije - dostupno je i iz HousingCommunity i Unit resursa
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'period' => now()->startOfMonth()->format('Y-m'),
            'housing_community_id' => null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Parametri generisanja')
                    ->description('Odaberite period i stambenu zajednicu za koju želite da generišete račune')
                    ->schema([
                        DatePicker::make('period')
                            ->label('Period (mesec/godina)')
                            ->native(false)
                            ->displayFormat('m/Y')
                            ->format('Y-m')
                            ->default(now()->startOfMonth())
                            ->required()
                            ->helperText('Izaberite mesec za koji želite da generišete račune'),
                        
                        Select::make('housing_community_id')
                            ->label('Stambena zajednica')
                            ->options(HousingCommunity::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Ostavite prazno za generisanje računa za sve zajednice'),
                        
                        Select::make('unit_id')
                            ->label('Jedinica (opciono)')
                            ->options(function (callable $get) {
                                $communityId = $get('housing_community_id');
                                if ($communityId) {
                                    return Unit::where('housing_community_id', $communityId)
                                        ->where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(fn ($unit) => [
                                            $unit->id => "{$unit->identifier} ({$unit->housingCommunity->name})"
                                        ]);
                                }
                                return Unit::where('is_active', true)
                                    ->with('housingCommunity')
                                    ->get()
                                    ->mapWithKeys(fn ($unit) => [
                                        $unit->id => "{$unit->identifier} ({$unit->housingCommunity->name})"
                                    ]);
                            })
                            ->searchable()
                            ->nullable()
                            ->helperText('Ostavite prazno za generisanje računa za sve jedinice u zajednici'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function generateStatements(): void
    {
        $data = $this->form->getState();
        $generator = app(BillingStatementGenerator::class);
        
        try {
            $period = $data['period'];
            $generatedCount = 0;
            $pdfs = [];
            $periodDate = Carbon::parse($period . '-01');

            // Ako je odabrana specifična jedinica
            if (!empty($data['unit_id'])) {
                $unit = Unit::find($data['unit_id']);
                if ($unit) {
                    $pdf = $generator->generateForUnit($unit, $period);
                    $filename = "racun_{$unit->identifier}_" . str_replace('-', '_', $period) . ".pdf";
                    
                    response()->streamDownload(
                        fn () => print($pdf->output()),
                        $filename
                    )->send();
                    
                    $generatedCount = 1;
                }
            }
            // Ako je odabrana zajednica
            elseif (!empty($data['housing_community_id'])) {
                $units = Unit::where('housing_community_id', $data['housing_community_id'])
                    ->where('is_active', true)
                    ->get();
                
                foreach ($units as $unit) {
                    $pdf = $generator->generateForUnit($unit, $period);
                    $generator->saveStatement($pdf, $period, $unit->identifier);
                    
                    $generatedCount++;
                }
            }
            // Ako nije odabrano ništa, generiši za sve
            else {
                $units = Unit::where('is_active', true)->get();
                
                foreach ($units as $unit) {
                    $pdf = $generator->generateForUnit($unit, $period);
                    $generator->saveStatement($pdf, $period, $unit->identifier);
                    
                    $generatedCount++;
                }
            }

            Notification::make()
                ->title('Računi uspešno generisani')
                ->body("Generisano {$generatedCount} računa za period {$period}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Greška pri generisanju računa')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('generate')
                ->label('Generiši račune')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->action('generateStatements')
                ->requiresConfirmation()
                ->modalHeading('Potvrda generisanja računa')
                ->modalDescription('Da li ste sigurni da želite da generišete račune za odabrani period?'),
        ];
    }
}
