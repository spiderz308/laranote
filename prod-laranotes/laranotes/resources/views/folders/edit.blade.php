<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manage Folder') }}: {{ $folder->name }}
            </h2>
            <a href="{{ route('folders.show', $folder) }}"
               class="text-sm text-gray-600 dark:text-gray-400">{{ __('Back to folder') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'folder-created')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Folder created. Assign groups and users to control visibility.') }}
                </div>
            @elseif (session('status') === 'folder-updated')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Folder updated.') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('folders.update', $folder) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" :value="__('Folder name')" />
                        <x-text-input id="name" class="mt-1 block w-full" type="text" name="name"
                                      :value="old('name', $folder->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="parent_id" :value="__('Parent folder')" />
                        <select id="parent_id" name="parent_id"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="">No parent (top level)</option>
                            @foreach ($parentFolders as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('parent_id', $folder->parent_id) == $candidate->id)>
                                    {{ $candidate->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                    </div>

                    {{-- Group access --}}
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            {{ __('Group access') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                            {{ __('Users in these groups can see folders and notes.') }}
                        </p>

                        @forelse ($groups as $group)
                            @php
                                $assignment = $folder->groups->firstWhere('id', $group->id);
                            @endphp
                            <div class="flex items-center gap-3 py-2 border-b border-gray-100 dark:border-gray-700">
                                <input type="checkbox"
                                       name="group_assignments[{{ $group->id }}][group_id]"
                                       value="{{ $group->id }}"
                                       @checked($assignment !== null)
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700">
                                <span class="flex-1 text-sm text-gray-900 dark:text-gray-100">{{ $group->name }}</span>
                                <select name="group_assignments[{{ $group->id }}][role_id]"
                                        class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected($assignment?->pivot->role_id == $role->id)>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No groups defined yet.') }}</p>
                        @endforelse
                    </div>

                    {{-- User access --}}
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            {{ __('Individual user access') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                            {{ __('Grant a single user access to this folder.') }}
                        </p>

                        @foreach ($users as $user)
                            @php
                                $assignment = $folder->users->firstWhere('id', $user->id);
                            @endphp
                            <div class="flex items-center gap-3 py-2 border-b border-gray-100 dark:border-gray-700">
                                <input type="checkbox"
                                       name="user_assignments[{{ $user->id }}][user_id]"
                                       value="{{ $user->id }}"
                                       @checked($assignment !== null || $user->id === $folder->user_id)
                                       @disabled($user->id === $folder->user_id)
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700">
                                <span class="flex-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $user->name }}
                                    @if ($user->id === $folder->user_id)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ __('owner') }})</span>
                                    @endif
                                </span>
                                <select name="user_assignments[{{ $user->id }}][role_id]"
                                        @disabled($user->id === $folder->user_id)
                                        class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected($assignment?->pivot->role_id == $role->id)>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        <a href="{{ route('folders.show', $folder) }}"
                           class="text-sm text-gray-600 dark:text-gray-400">{{ __('Cancel') }}</a>
                    </div>
                </form>

                <form method="POST" action="{{ route('folders.destroy', $folder) }}" class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700"
                      onsubmit="return confirm('{{ __('Delete this folder? Notes inside will be kept but unassigned.') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('Delete Folder') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>