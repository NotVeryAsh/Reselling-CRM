<?php

namespace App\Filament\Resources\Products;

use App\Enums\PurchasingPlatform;
use App\Enums\SellingPlatform;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        Product::query()
            ->each(function (Product $product) {
                $product->update(['id' => Str::uuid()]);
            });

        return $schema
            ->components([
                TextInput::make('code')
                    ->unique()
                    ->required(),
                TextInput::make('name')
                    ->autofocus()
                    ->required()
                    ->afterStateUpdatedJs(<<<'JS'
                        $set('code', ($state ?? '').replaceAll(' ', '-').toLowerCase())
                        JS),
                DatePicker::make('purchased_at')
                    ->default(now())
                    ->required(),
                TextInput::make('purchased_price')
                    ->required()
                    ->default(0)
                    ->numeric(),
                Select::make('purchased_platform')
                    ->options(PurchasingPlatform::class)
                    ->required(),
                DatePicker::make('sold_at'),
                TextInput::make('sold_price')
                    ->numeric(),
                Select::make('sold_platform')
                    ->options(SellingPlatform::class),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('code'),
                TextEntry::make('name'),
                TextEntry::make('purchased_at')
                    ->date(),
                TextEntry::make('purchased_price')
                    ->numeric(),
                TextEntry::make('purchased_platform'),
                TextEntry::make('sold_at')
                    ->date(),
                TextEntry::make('sold_price')
                    ->numeric(),
                TextEntry::make('sold_platform'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchased_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchased_price')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state === '0.00' ? 'Free' : $state),
                TextColumn::make('purchased_platform')
                    ->searchable(),
                TextColumn::make('sold_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('sold_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sold_platform')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Listed At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(ProductImporter::class),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
