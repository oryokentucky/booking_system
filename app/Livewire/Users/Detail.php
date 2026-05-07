<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Detail extends Component
{
    use AuthorizesRequests;

    public ?User $user;

    protected UserRepository $repository;

    public function boot(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function mount($id)
    {
        // $this->authorize('user.read');
        $this->user = $this->repository->findWithDetails($id);
    }

    public function render()
    {
        return view('livewire.users.detail');
    }
}
