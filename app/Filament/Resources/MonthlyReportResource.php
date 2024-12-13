<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonthlyReportResource\Pages;
use App\Filament\Resources\MonthlyReportResource\RelationManagers;
use App\Models\MonthlyReport;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class MonthlyReportResource extends Resource
{
    protected static ?string $model = MonthlyReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Manajemen Laporan';
    protected static ?string $label = 'Keuangan Bulanan';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    DatePicker::make('report_date')
                        ->label('Bulan Laporan')
                        ->default(now())
                        ->native(false)
                        ->displayFormat('F Y')
                        ->required(),
                    DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->reactive()
                        ->native(false)
                        ->displayFormat('d F Y')
                        ->afterStateUpdated(function (callable $set, $state) use ($form) {
                            $startDate = $state;
                            $endDate = $form->getState()['end_date'];

                            if ($startDate && $endDate) {
                                $totalKas = DB::table('kas')
                                    ->whereBetween('date', [$startDate, $endDate])
                                    ->sum('amount');

                                $totalIncome = DB::table('incomes')
                                    ->whereBetween('date', [$startDate, $endDate])
                                    ->sum('amount');

                                $totalExpense = DB::table('expenses')
                                    ->whereBetween('date', [$startDate, $endDate])
                                    ->sum('amount');
                            
                                $remainingBalance = $totalKas + $totalIncome - $totalExpense;

                                $set('total_kas', $totalKas);
                                $set('total_income', $totalIncome);
                                $set('total_expense', $totalExpense);
                                $set('remaining_balance', $remainingBalance);
                            }
                        }),
                    DatePicker::make('end_date')
                        ->label('Tanggal Akhir')
                        ->required()
                        ->reactive()
                        ->native(false)
                        ->displayFormat('d F Y')
                        ->afterStateUpdated(function (callable $set, $state) use ($form) {
                            $endDate = $state;
                            $startDate = $form->getState()['start_date'];

                            if ($startDate && $endDate) {
                                $totalKas = DB::table('kas')
                                    ->whereBetween('date', [$startDate, $endDate])
                                    ->sum('amount');

                                $totalIncome = DB::table('incomes')
                                    ->whereBetween('date', [$startDate, $endDate])
                                    ->sum('amount');

                                $totalExpense = DB::table('expenses')
                                    ->whereBetween('date', [$startDate, $endDate])
                                    ->sum('amount');
                            
                                $remainingBalance = $totalKas + $totalIncome - $totalExpense;

                                $set('total_kas', $totalKas);
                                $set('total_income', $totalIncome);
                                $set('total_expense', $totalExpense);
                                $set('remaining_balance', $remainingBalance);
                            }
                        }),  
                    ])->columns(3),
                TextInput::make('total_kas')
                    ->label('Total Pemasukan KAS')
                    ->prefix('Rp ')
                    ->readonly(),
                TextInput::make('total_income')
                    ->label('Total Pemasukan Lain')
                    ->prefix('Rp ')
                    ->readonly(),
                TextInput::make('total_expense')
                    ->label('Total Pengeluaran')
                    ->prefix('Rp ')
                    ->readonly(),
                TextInput::make('remaining_balance')
                    ->label('Sisa Uang KAS')
                    ->prefix('Rp ')
                    ->readonly(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_date')
                    ->label('Bulan Laporan')
                    ->dateTime('F Y'),
                TextColumn::make('total_kas')
                    ->label('Total Pemasukan KAS')
                    ->money('IDR'),
                TextColumn::make('total_income')
                    ->label('Total Pemasukan Lain')
                    ->money('IDR'),
                TextColumn::make('total_expense')
                    ->label('Total Pengeluaran KAS')
                    ->money('IDR'),
                TextColumn::make('remaining_balance')
                    ->label('Sisa Uang KAS')
                    ->money('IDR')
                    ->summarize(
                        Summarizer::make()
                            ->using(function ($query) {
                                return $query->sum(DB::raw('remaining_balance'));
                            })
                            ->money('IDR')
                    ),
            ])
            ->filters([
                //
            ])
            ->recordUrl(function ($record) {
                return Pages\ViewMonthlyReport::getUrl([$record->id]);
            })
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus Semua'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonthlyReports::route('/'),
            'create' => Pages\CreateMonthlyReport::route('/create'),
            'edit' => Pages\EditMonthlyReport::route('/{record}/edit'),
            'view' => Pages\ViewMonthlyReport::route('/{record}'),
        ];
    }
}