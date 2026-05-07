<flux:dropdown>
    <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
    <flux:menu>
        {{-- @can('user.read') --}}
        @if($user->status?->value === 'draft')
            <flux:menu.item href="{{ route('users.form', $user->id) }}" icon="pencil">
                Edit
            </flux:menu.item>

            <flux:modal.trigger name="delete-user-{{ $user->id }}">
                <flux:menu.item class="text-red-500" icon="trash">Delete</flux:menu.item>
            </flux:modal.trigger>
        @else
            @if(true)
            <flux:menu.item href="{{ route('users.detail', $user->id) }}" icon="document-magnifying-glass">
                View Details
            </flux:menu.item>
            @endif

            <flux:menu.item href="{{ route('users.form', $user->id) }}" icon="pencil">
                Edit
            </flux:menu.item>
        @endif
        {{-- @endcan --}}

        {{-- @can('user.update') --}}
        @if(in_array($user->status?->value, ['draft', 'deactivated']))
            <flux:modal.trigger name="activate-user-{{ $user->id }}">
                <flux:menu.item icon="check-circle" class="text-green-600">Activate</flux:menu.item>
            </flux:modal.trigger>
        @elseif($user->status?->value === 'active')
            <flux:modal.trigger name="deactivate-user-{{ $user->id }}">
                <flux:menu.item icon="x-circle" class="text-red-500">Deactivate</flux:menu.item>
            </flux:modal.trigger>
        @endif
        {{-- @endcan --}}
    </flux:menu>
</flux:dropdown>

@if($user->status?->value === 'draft')
<flux:modal name="delete-user-{{ $user->id }}" class="md:w-[32rem]">
    @include('livewire.users.components.status-modal', [
        'action' => 'delete',
        'user' => $user,
    ])
</flux:modal>
@endif

@if(in_array($user->status?->value, ['draft', 'deactivated']))
<flux:modal name="activate-user-{{ $user->id }}" class="md:w-[32rem]">
    @include('livewire.users.components.status-modal', [
        'action' => 'activate',
        'user' => $user,
    ])
</flux:modal>
@elseif($user->status?->value === 'active')
<flux:modal name="deactivate-user-{{ $user->id }}" class="md:w-[32rem]">
    @include('livewire.users.components.status-modal', [
        'action' => 'deactivate',
        'user' => $user,
    ])
</flux:modal>
@endif
