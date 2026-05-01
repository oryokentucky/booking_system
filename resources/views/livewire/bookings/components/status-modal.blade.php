<div class="flex flex-col gap-6">
@switch ($action)
    @case('activate')
        <div>
            <flux:heading size="lg">Activate Booking</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-600">Are you sure you want to activate <strong>{{ $booking->name }}</strong>?</flux:text>
        </div>
        <flux:textarea label="Remarks (Optional)" wire:model.defer="remarks" placeholder="Enter remarks" />
        <div class="flex justify-end gap-2 mt-4">
            <flux:modal.close>
                <flux:button variant="ghost" class="w-26 justify-center">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" class="w-26 justify-center bg-green-600 hover:bg-green-700 text-white"
                wire:click="bookingStatus({{ $booking->id }}, 'activate')">Confirm</flux:button>
        </div>
    @break

    @case('deactivate')
        <div>
            <flux:heading size="lg">Deactivate Booking</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-600">Are you sure you want to deactivate <strong>{{ $booking->name }}</strong>?</flux:text>
        </div>
        <flux:textarea label="Remarks (Optional)" wire:model.defer="remarks" placeholder="Enter remarks" />
        <div class="flex justify-end gap-2 mt-4">
            <flux:modal.close>
                <flux:button variant="ghost" class="w-26 justify-center">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" class="w-26 justify-center"
                wire:click="bookingStatus({{ $booking->id }}, 'deactivate')">Confirm</flux:button>
        </div>
    @break

    @case('delete')
        <div>
            <flux:heading size="lg">Delete Booking</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-600">
                Are you sure you want to delete <strong>{{ $booking->name }}</strong>? This action cannot be undone.
            </flux:text>
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <flux:modal.close>
                <flux:button variant="ghost" class="w-26 justify-center">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" class="w-26 justify-center"
                wire:click="delete({{ $booking->id }})">Confirm</flux:button>
        </div>
    @break

    @default
@endswitch
</div>
