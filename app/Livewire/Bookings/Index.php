<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use App\Trait\HasPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests, HasPagination;

    public $keyword;
    public $status;
    public $created_at;
    public $remarks;
    
    public $bookings;

    protected BookingRepository $repository;
    protected BookingService $service;

    public function boot(BookingRepository $repository, BookingService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    public function mount()
    {
        // $this->authorize('booking.read');

        $data = $this->repository->getPaginatedData([
            'keyword' => $this->keyword,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'perPage' => $this->perPage,
            'page' => $this->paginatorInfo['currentPage'],
        ]);
        $this->bookings = $data->items();
        $this->paginate($data);
    }

    public function search()
    {
        $this->paginatorInfo['currentPage'] = 1;
        $this->mount();
    }

    public function resetFilters()
    {
        $this->reset(['keyword', 'status', 'created_at']);
        $this->mount();
    }

    public function showForm()
    {
        // $this->authorize('booking.create');
        $this->redirectRoute('bookings.form', navigate: true);
    }

    public function filterByTab($tab)
    {
        if ($tab === 'all') {
            $this->status = null;
        } else {
            $this->status = $tab;
        }
        $this->paginatorInfo['currentPage'] = 1;
        $this->mount();
    }

    public function statusCount($status = null)
    {
        return $this->repository->getStatusCount($status, [
            'keyword' => $this->keyword,
            'created_at' => $this->created_at,
        ]);
    }

    public function bookingStatus($id, $action)
    {
        // $this->authorize('booking.update');
        $this->service->updateStatus($id, $action, Auth::id(), $this->remarks ?? null);
        $this->remarks = null;
        $this->dispatch('show-toast', type: 'success', title: 'Success', message: 'Booking status updated successfully!');
        $this->mount();
    }
    
    public function delete($id)
    {
        // $this->authorize('booking.delete');
        $model = $this->repository->findWithDetails($id);
        $this->service->delete($model);
        $this->dispatch('show-toast', type: 'success', title: 'Success', message: 'Booking deleted successfully!');
        $this->mount();
    }

    public function render()
    {
        $statusCounts = [
            'all' => $this->statusCount(),
            'draft' => $this->statusCount('draft'),
            'active' => $this->statusCount('active'),
            'deactivated' => $this->statusCount('deactivated'),
        ];

        return view('livewire.bookings.index', [
            'statusCounts' => $statusCounts,
        ]);
    }
}
