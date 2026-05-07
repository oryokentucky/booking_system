<div class="flex flex-col gap-6 w-full pb-3">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ $user->name }}</flux:heading>
                <div class="inline-flex align-middle ml-1">
                    <flux:badge color="{{ $user->status?->color() }}" size="sm">
                        {{ $user->status?->label() }}
                    </flux:badge>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:button href="{{ route('users.form', $user->id) }}" variant="primary" icon="pencil">Edit</flux:button>
            </div>
        </div>

        <flux:card class="w-full flex flex-col gap-4 p-6!">
            <div class="flex items-center justify-between mb-2">
                <flux:heading size="lg">Basic Information</flux:heading>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div>
                    <flux:text class="text-sm font-semibold">Name</flux:text>
                    <flux:text class="font-medium">{{ $user->name ?? '-' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-semibold">Email</flux:text>
                    <flux:text class="font-medium">{{ $user->email ?? '-' }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm font-semibold mb-1">Status</flux:text>
                    <div class="font-medium">
                        <flux:badge color="{{ $user->status?->color() }}" size="sm">
                            {{ $user->status?->label() }}
                        </flux:badge>
                    </div>
                </div>
                <div>
                    <flux:text class="text-sm font-semibold">Created At</flux:text>
                    <flux:text class="font-medium">{{ $user->created_at->format('d M Y, h:i A') }}</flux:text>
                </div>
            </div>
        </flux:card>
</div>
