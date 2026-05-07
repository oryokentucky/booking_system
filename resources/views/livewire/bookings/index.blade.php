<div class="flex flex-col gap-6">
    <div class="flex justify-between items-center mb-4">
            <div class="flex flex-col gap-1">
                <flux:heading size="xl">Bookings</flux:heading>
            </div>
            <div class="flex gap-2">
                <flux:button variant="primary" icon="plus" wire:click="showForm" class="hidden md:flex">New Booking</flux:button>
                <flux:button variant="primary" icon="plus" wire:click="showForm" class="md:hidden">New</flux:button>
            </div>
        </div>

        <div class="w-full flex flex-col gap-5 pb-3">
            @if(true)
            <flux:card class="w-full gap-2 p-4!">
                <form wire:submit.prevent="search" class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <flux:input label="Search Keyword" name="keyword" placeholder="Search Keyword" wire:model.defer="keyword" icon="magnifying-glass" />
                        <flux:input label="Created Date" name="created_at" type="date" placeholder="DD-MM-YYYY" wire:model.defer="created_at" icon="calendar" />
                    </div>
                    <div class="flex gap-2">
                        <flux:button type="submit" variant="primary" wire:click="search" class="w-26 justify-center">Search</flux:button>
                        <flux:button variant="ghost" wire:click="resetFilters" class="w-26 justify-center">Reset</flux:button>
                    </div>
                </form>
            </flux:card>
            @endif

            <flux:card class="w-full p-0!">
                <flux:navbar class="px-4 border-b border-zinc-200 dark:border-zinc-700">
                    <flux:navbar.item wire:click="filterByTab('all')" :current="$status === null">All</flux:navbar.item>
                    <flux:navbar.item wire:click="filterByTab('draft')" :current="$status === 'draft'">Draft</flux:navbar.item>
                    <flux:navbar.item wire:click="filterByTab('active')" :current="$status === 'active'">Active</flux:navbar.item>
                    <flux:navbar.item wire:click="filterByTab('deactivated')" :current="$status === 'deactivated'">Deactivated</flux:navbar.item>
                </flux:navbar>

                <flux:table :pagination="true">
                    <flux:table.columns>
                        <flux:table.column sticky class="w-[1%]" />
                        <flux:table.column sticky>NAME</flux:table.column>
                        <flux:table.column>STATUS</flux:table.column>
                        <flux:table.column>CREATED AT</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                    @foreach ($this->bookings as $booking)
                        <flux:table.row>
                            <flux:table.cell sticky class="text-center align-middle pr-2! pl-3!">
                                @include('livewire.bookings.components.action-button', ['booking' => $booking])
                            </flux:table.cell>

                            <flux:table.cell sticky>
                                <div class="flex items-center gap-2">
                                    <flux:link href="{{ route('bookings.form', $booking->id) }}" variant="subtle" class="font-bold">
                                        {{ $booking->name }}
                                    </flux:link>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge color="{{ $booking->status?->color() }}" size="sm" class="whitespace-nowrap">
                                    {{ $booking->status?->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:text class="text-sm">
                                    {{ \Carbon\Carbon::parse($booking->created_at)->format('d-m-Y, g:iA') }}
                                </flux:text>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>
</div>
