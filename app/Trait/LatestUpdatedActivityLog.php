<?php

namespace App\Trait;

use App\Support\DiscrepancyAudit;
use App\Support\InboundAudit;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

trait LatestUpdatedActivityLog
{
    public function modelActivities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    public function getLatestUpdatedActivityAttribute(): ?array
    {
        $activityLog = $this->modelActivities()
            ->with('causer')
            ->latest('created_at')
            ->first();

        if (!$activityLog) {
            return null;
        }

        return [
            'event' => $activityLog->event,
            'causer' => $activityLog->causer ? [
                'id' => $activityLog->causer->id,
                'name' => $activityLog->causer->name,
                'role' => $activityLog->causer->role->name ?? null,
            ] : null,

            'updated_at' => $activityLog->created_at,
        ];
    }

    public function loadActivities(Model $model, int $perPage = 10): array
    {
        $logNames = array_values(array_filter($this->resolveLogNamesForModelClass(get_class($model))));

        $activities = Activity::where('subject_type', get_class($model))
            ->where('subject_id', $model->id)
            ->when(! empty($logNames), fn ($q) => $q->whereIn('log_name', $logNames))
            ->with('causer')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(perPage: $perPage, page: $this->paginatorInfo['currentPage'] ?? 1);

            $this->paginate($activities);

        return $this->decorateActivitiesForDisplay(collect($activities->items()))->all();
    }

    /**
     * Activity log for one model without touching the host page paginator (e.g. modal on an index).
     */
    public function loadActivitiesLimited(Model $model, int $limit = 100): array
    {
        $logNames = array_values(array_filter($this->resolveLogNamesForModelClass(get_class($model))));

        $rows = Activity::query()
            ->where('subject_type', get_class($model))
            ->where('subject_id', $model->id)
            ->when(! empty($logNames), fn ($q) => $q->whereIn('log_name', $logNames))
            ->with('causer')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $this->decorateActivitiesForDisplay($rows)->all();
    }

    private function decorateActivitiesForDisplay(\Illuminate\Support\Collection $activities): \Illuminate\Support\Collection
    {
        return $activities->each(function (Activity $activity): void {
            $activity->setAttribute('event', match ($activity->log_name) {
                'inbound' => InboundAudit::actionHeading($activity->event),
                'discrepancy' => DiscrepancyAudit::actionHeading($activity->event),
                default => ucfirst((string) $activity->event),
            });
        });
    }

    private function resolveLogNamesForModelClass(string $modelClass): array
    {
        $logNameMap = [
            'App\Models\CostCenter' => 'cost_center',
            'App\Models\User' => 'user',
            'App\Models\Inbound' => 'inbound',
            'App\Models\InboundItem' => 'inbound_item',
            'App\Models\GoodsReceipt' => 'goods_receipt',
            'App\Models\GoodsReceiptItem' => 'goods_receipt_item',
            'App\Models\CostAllocation'   => 'cost_allocation',
            'App\Models\CostCenter'       => 'cost_center',
            'App\Models\User'             => 'user',
            'App\Models\Project'          => 'project',
            'App\Models\Department'       => 'department',
            'App\Models\Material'         => 'material',
            'App\Models\BinLocation'      => 'bin_location',
            'App\Models\Role'             => 'role',
            'App\Models\MaterialCategory' => 'material_category',
            'App\Models\EmailTemplate'    => 'email_template',
            'App\Models\PaymentTerm'      => 'payment_term',
            'App\Models\Branch'           => 'branch',
            'App\Models\AssetClass'       => 'asset_class',
            'App\Models\MasterSetting'    => 'master_setting',
            'App\Models\Discrepancy' => 'discrepancy',
            'App\Models\Warehouse' => 'warehouse',
        ];

        $logName = $logNameMap[$modelClass] ?? null;

        return $logName ? [$logName] : ['default'];
    }
}
