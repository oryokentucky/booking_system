<div class="w-full flex flex-col gap-6 pb-3">
        <div class="flex justify-between items-center">
            <flux:heading size="xl">{{ $modelId ? 'Edit' : 'New' }} User</flux:heading>
        </div>

        <div class="w-full flex flex-col gap-5">
            <flux:card class="w-full flex flex-col gap-6">
                <flux:heading size="lg">Basic Details</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input label="Name" name="name" wire:model.defer="name" required />

                    <flux:input label="Email" name="email" type="email" wire:model.defer="email" required />

                    <flux:input label="Password" name="password" type="password" wire:model.defer="password" :required="!$modelId" placeholder="{{ $modelId ? 'Leave blank to keep current password' : '' }}" />

                    <flux:select label="Status" name="status" wire:model.defer="status" required>
                        <flux:select.option value="draft">Draft</flux:select.option>
                        <flux:select.option value="active">Active</flux:select.option>
                        <flux:select.option value="deactivated">Deactivated</flux:select.option>
                    </flux:select>
                </div>
            </flux:card>

            {{-- Action Buttons --}}
            <div class="flex justify-between gap-4 mt-2">
                <flux:button href="{{ route('users.index') }}" variant="ghost">Cancel</flux:button>
                <flux:button wire:click="save" variant="primary" class="w-[110px]">Save</flux:button>
            </div>
        </div>
</div>
