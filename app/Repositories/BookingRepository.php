<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;

class BookingRepository
{
    public function getPaginatedData(?array $data, int $perPage = 10, int $page = 1)
    {
        return Booking::query()
            ->when($data['keyword'] ?? null, function (Builder $q, $keyword) {
                // Adjust this depending on your columns
                $q->where('name', 'like', '%' . $keyword . '%');
            })
            ->when($data['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($data['created_at'] ?? null, fn (Builder $q, $createdAt) => $q->whereDate('created_at', \Carbon\Carbon::parse($createdAt)))
            ->latest()
            ->paginate(perPage: $perPage, page: $page);
    }

    public function getStatusCount(?string $status = null, ?array $filters = []): int
    {
        return Booking::query()
            ->when($status, fn (Builder $q) => $q->where('status', $status))
            ->when($filters['keyword'] ?? null, function (Builder $q, $keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            })
            ->when($filters['created_at'] ?? null, fn (Builder $q, $createdAt) => $q->whereDate('created_at', \Carbon\Carbon::parse($createdAt)))
            ->count();
    }

    public function findWithDetails(int|string $id): Booking
    {
        return Booking::findOrFail($id);
    }
}
