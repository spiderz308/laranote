<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Share Note') }}: {{ $note->title }}
            </h2>
            <div class="flex items-center gap-3">
                @if ($note->is_public)
                    <a href="{{ route('notes.public', $note) }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('Public Link') }}
                    </a>
                @endif
                <a href="{{ route('notes.show', $note) }}"
                   class="text-sm text-gray-600 dark:text-gray-400">{{ __('Back to note') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if ($note->is_public)
                <div class="mb-4 p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Public share link') }}:</span>
                        <input type="text" readonly value="{{ route('notes.public', $note) }}"
                               class="text-sm flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                               onfocus="this.select()">
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ __('Anyone with this link can view') }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('notes.collaborators.sync', $note) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            {{ __('People with access') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                            {{ __('Select users and their role. Roles: viewer (read-only), editor (edit), admin (manage + delete).') }}
                        </p>

                        <div class="border border-gray-200 dark:border-gray-700 rounded-md divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($users as $user)
                                @php
                                    $roleId = $note->collaborators->firstWhere('id', $user->id)?->pivot->role_id;
                                @endphp
                                <div class="flex items-center gap-3 px-4 py-2">
                                    <input type="checkbox"
                                           name="collaborators[{{ $user->id }}][user_id]"
                                           value="{{ $user->id }}"
                                           @checked($user->id === $note->user_id || $roleId !== null)
                                           @disabled($user->id === $note->user_id)
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700">
                                    <div class="flex-1 text-sm">
                                        <span class="text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                                        <span class="text-gray-400 dark:text-gray-500">{{ $user->email }}</span>
                                        @if ($user->id === $note->user_id)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ __('owner') }})</span>
                                        @endif
                                    </div>
                                    <select name="collaborators[{{ $user->id }}][role_id]"
                                            @disabled($user->id === $note->user_id)
                                            class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" @selected($roleId == $role->id)>
                                                {{ ucfirst($role->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('collaborators')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('notes.show', $note) }}"
                           class="text-sm text-gray-600 dark:text-gray-400">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>