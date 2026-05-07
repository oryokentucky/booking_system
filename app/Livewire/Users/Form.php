<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Enums\UserStatus;
use App\Repositories\UserRepository;
use App\Services\UserService;
use App\Rules\UserRules;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $modelId;
    public $name;
    public $email;
    public $password;
    public $status = 'draft';

    protected UserRepository $repository;
    protected UserService $service;

    public function boot(UserRepository $repository, UserService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }

    public function mount($id = null)
    {
        if ($id) {
            // $this->authorize('user.read');
            $model = $this->repository->findWithDetails($id);
            $this->modelId = $model->id;
            $this->name = $model->name;
            $this->email = $model->email;
            $this->status = $model->status instanceof UserStatus ? $model->status->value : $model->status;
        } else {
            // $this->authorize('user.create');
        }
    }

    public function save()
    {
        $rules = $this->modelId ? UserRules::updateRules($this->modelId) : UserRules::createRules();
        $this->validate($rules);

        $model = $this->modelId ? $this->repository->findWithDetails($this->modelId) : null;
        
        $this->service->save([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'status' => $this->status,
        ], $model);

        session()->flash('success', 'User saved successfully!');
        return $this->redirect(route('users.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.users.form');
    }
}
