<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function updateStatus(int $id, string $action, int $updatedBy, ?string $remarks = null): void
    {
        $model = Booking::findOrFail($id);
        
        $newStatus = match ($action) {
            'activate', 'active' => 'active',
            'deactivate', 'deactivated' => 'deactivated',
            default => 'draft',
        };

        $model->update([
            'status' => $newStatus,
            // 'updated_by' => $updatedBy, // uncomment if used
        ]);

        // Uncomment if you use ActivityLogger
        // $model->logActivity($newStatus, "Booking status updated to {$newStatus}", [
        //     'remark' => $remarks
        // ]);
    }

    public function save(array $formData, ?Booking $model = null): Booking
    {
        DB::beginTransaction();

        try {
            $data = $this->prepareData($formData);

            if ($model) {
                $model->update($data);
            } else {
                $model = Booking::create($data);
            }

            // Save sub-relationships here...

            DB::commit();

            return $model;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(Booking $model): void
    {
        $model->delete();
    }

    protected function prepareData(array $formData): array
    {
        return [
            'name' => $formData['name'] ?? null,
            'status' => $formData['status'] ?? 'draft',
        ];
    }
}
