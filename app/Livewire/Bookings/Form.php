<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Repositories\BookingRepository;
use App\Services\BookingService;
use App\Rules\BookingRules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $modelId;
    public $name;
    public $status = 'draft';

    protected BookingRepository $repository;
    protected BookingService $service;

    public function boot(BookingRepository $repository, BookingService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    public function mount($id = null)
    {
        if ($id) {
            // $this->authorize('booking.read');
            $model = $this->repository->findWithDetails($id);
            $this->modelId = $model->id;
            $this->name = $model->name;
            $this->status = $model->status instanceof BookingStatus ? $model->status->value : $model->status;
        } else {
            // $this->authorize('booking.create');
        }
    }

    public function save()
    {
        $rules = $this->modelId ? BookingRules::updateRules($this->modelId) : BookingRules::createRules();
        $this->validate($rules);

        $model = $this->modelId ? $this->repository->findWithDetails($this->modelId) : null;
        
        $this->service->save([
            'name' => $this->name,
            'status' => $this->status,
        ], $model);

        session()->flash('success', 'Booking saved successfully!');
        return $this->redirect(route('bookings.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.bookings.form');
    }
}
