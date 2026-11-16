<?php

namespace App\Filament\Vendor\Resources\Tenders\Pages;

use App\Filament\Vendor\Resources\Tenders\TenderResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class ViewTender extends ViewRecord
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function content(Schema $schema): Schema
    {
        $bottomActions = $this->getBottomActions();

        $actionsComponent = ! empty($bottomActions)
            ? Actions::make($bottomActions)
                ->alignment(Alignment::Center)
                ->fullWidth()
            : null;

        if ($this->hasCombinedRelationManagerTabsWithContent()) {
            return $schema
                ->components(array_filter([
                    $this->getRelationManagersContentComponent(),
                    $actionsComponent,
                ]));
        }

        return $schema
            ->components(array_filter([
                $this->getFormContentComponent(),
                $this->getRelationManagersContentComponent(),
                $actionsComponent,
            ]));
    }

    /**
     * @return array<Action>
     */
    protected function getBottomActions(): array
    {
        $tender = $this->getRecord();
        $vendor = auth()->user()?->vendor;

        $hasPurchased = $vendor ? $vendor->hasPurchasedTender($tender->id) : false;
        $isFree = (float) $tender->document_price <= 0;
        $canAccessDocs = $hasPurchased || $isFree;

        $existingBid = $vendor ? $tender->bids()->where('vendor_id', $vendor->id)->first() : null;

        $actions = [];

        if (! $canAccessDocs && $tender->isOpenForBidding()) {
            $actions[] = Action::make('purchase')
                ->label('Beli Dokumen Tender (AffanPay)')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->button()
                ->size('lg')
                ->extraAttributes(['class' => 'w-full justify-center'])
                ->url(route('tenders.purchase', $tender->id));
        }

        if ($tender->isOpenForBidding() && $canAccessDocs) {
            if ($existingBid) {
                $actions[] = Action::make('edit_bid')
                    ->label($existingBid->status === 'draft' ? 'Kemaskini Bidaan' : 'Lihat Bidaan Dihantar')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->button()
                    ->size('lg')
                    ->extraAttributes(['class' => 'w-full justify-center'])
                    ->url(route('filament.vendor.resources.bids.edit', ['record' => $existingBid->id]));
            } else {
                $actions[] = Action::make('create_bid')
                    ->label('Masuk Bidaan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->button()
                    ->size('lg')
                    ->extraAttributes(['class' => 'w-full justify-center'])
                    ->url(route('filament.vendor.resources.bids.create', ['tender_id' => $tender->id]));
            }
        }

        return $actions;
    }
}
