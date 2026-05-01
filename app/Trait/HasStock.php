<?php

namespace App\Trait;

use App\Enums\InventoryMovementType;
use App\Models\Inbound;
use App\Models\InboundItem;
use App\Models\Outbound;
use App\Models\OutboundItem;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait HasStock
{
    /**
     * Standardized stock addition with audit logging.
     */
    public function addStock(int $quantity, array $meta = [], string $pool = 'available'): bool
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than zero.');
        }

        if (! in_array($pool, ['available', 'quarantined'])) {
            throw new Exception("Invalid stock pool: {$pool}");
        }

        return DB::transaction(function () use ($quantity, $meta, $pool) {
            $model = $this->lockForUpdate()->find($this->getKey());

            $before = $model->{$pool};
            $model->increment($pool, $quantity);

            $this->recordStockHistory($quantity, $before, $before + $quantity, array_merge($meta, ['pool' => $pool]));

            return true;
        });
    }

    /**
     * Standardized stock deduction with audit logging.
     */
    public function deductStock(int $quantity, array $meta = []): bool
    {
        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($quantity, $meta) {
            $model = $this->lockForUpdate()->find($this->getKey());

            if ($model->available < $quantity) {
                throw new Exception('Insufficient available quantity.');
            }

            $before = $model->available;
            $model->decrement('available', $quantity);

            $this->recordStockHistory(-$quantity, $before, $before - $quantity, $meta);

            return true;
        });
    }

    public function hasEnoughStock(int $quantity): bool
    {
        return $this->available >= $quantity;
    }

    /**
     * Unified audit logging for all stock movements.
     * Migrated from InventoryService for model-direct integration.
     */
    protected function recordStockHistory(int $change, int $before, int $after, array $meta = [])
    {
        // Trait-aware creation of history records
        if (! method_exists($this, 'inventoryHistories')) {
            return;
        }

        $source = $this->extractSource($meta['source_item'] ?? null);
        $pool = $meta['pool'] ?? 'available';
        $remark = $meta['remark'] ?? null;

        if ($pool === 'quarantined') {
            $remark = '[QUARANTINED] '.($remark ?? '');
        }

        $this->inventoryHistories()->create([
            'movement_type' => $meta['movement_type'] ?? InventoryMovementType::ADJUSTMENT,
            'movement_reference' => $this->extractReference($meta['source_item'] ?? null),
            'source_id' => $source['source_id'],
            'source_type' => $source['source_type'],
            'quantity' => $change,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'serial_no' => (! empty($meta['serial_no']) && is_array($meta['serial_no'])) ? $meta['serial_no'] : null,
            'remark' => trim($remark ?? ''),
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);
    }

    /**
     * Extracts reference numbers (e.g. Inbound No) from various source models.
     */
    protected function extractReference($sourceItem): ?string
    {
        if (! $sourceItem) {
            return null;
        }

        if ($sourceItem instanceof InboundItem) {
            return $sourceItem->inbound?->inbound_no;
        }

        if ($sourceItem instanceof OutboundItem) {
            return $sourceItem->outbound?->outbound_no;
        }

        if (is_object($sourceItem) && isset($sourceItem->reference_no)) {
            return $sourceItem->reference_no;
        }

        return null;
    }

    /**
     * Extracts polymorphic source details from various source items.
     */
    protected function extractSource($sourceItem): array
    {
        if (! $sourceItem) {
            return [
                'source_id' => null,
                'source_type' => null,
            ];
        }

        if ($sourceItem instanceof InboundItem) {
            return [
                'source_id' => $sourceItem->inbound_id,
                'source_type' => Inbound::class,
            ];
        }

        if ($sourceItem instanceof OutboundItem) {
            return [
                'source_id' => $sourceItem->outbound_id,
                'source_type' => Outbound::class,
            ];
        }

        return [
            'source_id' => null,
            'source_type' => null,
        ];
    }
}
