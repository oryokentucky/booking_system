<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function updateStatus(int $id, string $action, int $updatedBy, ?string $remarks = null): void
    {
        $model = User::findOrFail($id);
        
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
        // $model->logActivity($newStatus, "User status updated to {$newStatus}", [
        //     'remark' => $remarks
        // ]);
    }

    public function save(array $formData, ?User $model = null): User
    {
        DB::beginTransaction();

        try {
            $data = $this->prepareData($formData);

            if ($model) {
                $model->update($data);
            } else {
                $model = User::create($data);
            }

            // Save sub-relationships here...

            DB::commit();

            return $model;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(User $model): void
    {
        $model->delete();
    }

    protected function prepareData(array $formData): array
    {
        $data = [
            'name' => $formData['name'] ?? null,
            'email' => $formData['email'] ?? null,
            'status' => $formData['status'] ?? 'draft',
        ];

        if (!empty($formData['password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($formData['password']);
        }

        return $data;
    }
}
