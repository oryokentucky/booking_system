<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Repositories\BookingRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Detail extends Component
{
    use AuthorizesRequests;

    public ?Booking $booking;

    protected BookingRepository $repository;

    public function boot(BookingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function mount($id)
    {
        // $this->authorize('booking.read');
        $this->booking = $this->repository->findWithDetails($id);
    }

    public function render()
    {
        return view('livewire.bookings.detail');
    }
}
