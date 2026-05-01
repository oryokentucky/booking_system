<?php

namespace App\Trait;

use App\Models\RunningNo;

trait HasRunningNo
{
    /**
     * Generate a running number for the given source
     *
     * @param  string  $source  The source type (supplier, user, role, project)
     * @param  int|null  $year  The year for year-based running numbers
     */
    public function generateRunningNo(string $source, ?int $year = null): string
    {
        // Supplier, User, Role, Purchase Requisition use simple incremental code without year
        if (in_array($source, ['supplier', 'user', 'role', 'purchase_requisition', 'purchase_order', 'outbound', 'outbound_damage_report', 'listing_import', 'shipping_term', 'inbound', 'goods_receipt', 'discrepancy', 'shipping_request'])) {
            $latest_running_no = RunningNo::where('source', $source)
                ->lockForUpdate()
                ->latest('id')->first();

            if ($latest_running_no) {
                $number = intval($latest_running_no->running_no) + 1;
            } else {
                $number = 1;
            }

            $new_running_no = str_pad($number, 4, '0', STR_PAD_LEFT);

            // Format based on source
            $formatted_running_no = match ($source) {
                'supplier' => "S{$new_running_no}",
                'user' => "US-{$new_running_no}",
                'role' => "RL-{$new_running_no}",
                'purchase_requisition' => "PR-{$new_running_no}",
                'purchase_order' => "PO-{$new_running_no}",
                'outbound' => "OUT-{$new_running_no}",
                'outbound_damage_report' => "RP-{$new_running_no}",
                'listing_import' => "IMP-{$new_running_no}",
                'shipping_term' => "ST-{$new_running_no}",
                'inbound' => "INB-{$new_running_no}",
                'goods_receipt' => "GRN-{$new_running_no}",
                'discrepancy' => "DSP-{$new_running_no}",
                'shipping_request' => "SR-{$new_running_no}",
                default => $new_running_no,
            };

            RunningNo::create([
                'source' => $source,
                'year' => null,
                'running_no' => $new_running_no,
            ]);

            return $formatted_running_no;
        }

        // Project and others use year-based format
        $latest_running_no = RunningNo::where('source', $source)
            ->where('year', $year)
            ->lockForUpdate()
            ->latest('id')->first();

        if ($latest_running_no) {
            $number = intval($latest_running_no->running_no) + 1;
        } else {
            $number = 1;
        }

        $new_running_no = str_pad($number, 2, '0', STR_PAD_LEFT);

        if ($source === 'project') {
            $formatted_running_no = "RND-{$year}-{$new_running_no}";
        } else {
            $formatted_running_no = "{$source}-{$year}-{$new_running_no}";
        }

        RunningNo::create([
            'source' => $source,
            'year' => $year,
            'running_no' => $new_running_no,
        ]);

        return $formatted_running_no;
    }
}
