<flux:dropdown>
    <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
    <flux:menu>
        {{-- @can('booking.read') --}}
        @if($booking->status?->value === 'draft')
            <flux:menu.item href="{{ route('bookings.form', $booking->id) }}" icon="pencil">
                Edit
            </flux:menu.item>

            <flux:modal.trigger name="delete-booking-{{ $booking->id }}">
                <flux:menu.item class="text-red-500" icon="trash">Delete</flux:menu.item>
            </flux:modal.trigger>
        @else
            @if(true)
            <flux:menu.item href="{{ route('bookings.detail', $booking->id) }}" icon="document-magnifying-glass">
                View Details
            </flux:menu.item>
            @endif

            <flux:menu.item href="{{ route('bookings.form', $booking->id) }}" icon="pencil">
                Edit
            </flux:menu.item>
        @endif
        {{-- @endcan --}}

        {{-- @can('booking.update') --}}
        @if(in_array($booking->status?->value, ['draft', 'deactivated']))
            <flux:modal.trigger name="activate-booking-{{ $booking->id }}">
                <flux:menu.item icon="check-circle" class="text-green-600">Activate</flux:menu.item>
            </flux:modal.trigger>
        @elseif($booking->status?->value === 'active')
            <flux:modal.trigger name="deactivate-booking-{{ $booking->id }}">
                <flux:menu.item icon="x-circle" class="text-red-500">Deactivate</flux:menu.item>
            </flux:modal.trigger>
        @endif
        {{-- @endcan --}}
    </flux:menu>
</flux:dropdown>

@if($booking->status?->value === 'draft')
<flux:modal name="delete-booking-{{ $booking->id }}" class="md:w-lg">
    @include('livewire.bookings.components.status-modal', [
        'action' => 'delete',
        'booking' => $booking,
    ])
</flux:modal>
@endif

@if(in_array($booking->status?->value, ['draft', 'deactivated']))
<flux:modal name="activate-booking-{{ $booking->id }}" class="md:w-lg">
    @include('livewire.bookings.components.status-modal', [
        'action' => 'activate',
        'booking' => $booking,
    ])
</flux:modal>
@elseif($booking->status?->value === 'active')
<flux:modal name="deactivate-booking-{{ $booking->id }}" class="md:w-lg">
    @include('livewire.bookings.components.status-modal', [
        'action' => 'deactivate',
        'booking' => $booking,
    ])
</flux:modal>
@endif
