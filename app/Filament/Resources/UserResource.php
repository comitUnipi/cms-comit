<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  protected static ?string $navigationIcon = null;
  protected static ?string $navigationGroup = 'Manajemen Anggota';
  protected static ?string $label = 'Semua Anggota Aktif';
  protected static ?int $navigationSort = 1;

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        TextInput::make('name')
          ->required()
          ->label('Nama Anggota')
          ->columnSpanFull()
          ->minLength(3),
        Grid::make()->schema([
          TextInput::make('npm')
            ->label('NPM')
            ->required(),
          TextInput::make('password')
            ->label('Kata Sandi')
            ->password()
            ->revealable()
            ->dehydrated(fn($state) => filled($state))
            ->required(fn(string $context): bool => $context === 'create')
            ->minLength(6),
          Select::make('role')->options([
            'user' => 'User',
            'guest' => 'Guest',
            'admin' => 'Admin',
            'keuangan' => 'Keuangan',
            'super admin' => 'Super Admin',
          ])->required()->default('user'),
          Select::make('position')->options([
            'ketua umum' => 'User',
            'wakil ketua' => 'Guest',
            'sekretaris' => 'Admin',
            'bendahara' => 'Super Admin',
            'koordinator sdm' => 'Koordinator SDM',
            'koordinator humas' => 'Koordinator Humas',
            'koordinator akademik' => 'Koordinator Akademik',
            'sdm' => 'Sdm',
            'humas internal' => 'Humas Internal',
            'humas eksternal' => 'Humas Eksternal',
            'sdm' => 'Programming',
            'comp dan network' => 'Comp dan Network',
            'design grafis' => 'Design Grafis',
            'microsoft' => 'Microsoft',
            'prasarana' => 'Prasarana',
            'kominfo' => 'Kominfo',
            'anggota' => 'Anggota',
            'calon anggota' => 'Calon Anggota',
          ])->required()->default('calon anggota'),
        ])->columns(3),
        Toggle::make('is_active')
          ->label('Status Aktif')
          ->default(false),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('npm')
          ->label('NPM')
          ->searchable(),
        TextColumn::make('name')
          ->label('Nama Anggota')
          ->searchable(),
        TextColumn::make('position')
          ->sortable()
          ->searchable(),
        IconColumn::make('is_active')
          ->label('Status Aktif')
          ->alignCenter()
          ->boolean(),
        TextColumn::make('role')
          ->sortable()
          ->searchable(),
      ])
      ->filters([
        //
      ])
      ->recordUrl(function ($record) {
        return Pages\ViewUser::getUrl([$record->id]);
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
        ])->label('Hapus Anggota'),
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
      'index' => Pages\ListUsers::route('/'),
      'create' => Pages\CreateUser::route('/create'),
      'edit' => Pages\EditUser::route('/{record}/edit'),
      'view' => Pages\ViewUser::route('/{record}'),
    ];
  }
}
