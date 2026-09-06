<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('My Notes') }}
            </h2>
            <div class="flex items-center gap-3">
            <a href="{{ route('notes.trash') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Trash') }}
            </a>
            <a href="{{ route('notes.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('New Note') }}
            </a>
        </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'note-created')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Note created successfully.') }}
                </div>
            @elseif (session('status') === 'note-updated')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Note updated successfully.') }}
                </div>
            @elseif (session('status') === 'note-deleted')
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400 text-red-700 dark:text-red-300 rounded">
                    {{ __('Note deleted.') }}
                </div>
            @endif

            {{-- Search & filter --}}
            <form method="GET" action="{{ route('notes.index') }}"
                  class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" class="mt-1 block w-full" type="text" name="search"
                                      :value="request('search')" placeholder="{{ __('Search by title or content...') }}" />
                    </div>
                    <div>
                        <x-input-label for="tag" :value="__('Filter by tag')" />
                        <select id="tag" name="tag"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="">All tags</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}" @selected(request('tag') == $tag->id)>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="folder" :value="__('Filter by folder')" />
                        <select id="folder" name="folder"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="">All folders</option>
                            @foreach ($folders as $folder)
                                <option value="{{ $folder->id }}" @selected(request('folder') == $folder->id)>
                                    {{ $folder->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="sort" :value="__('Sort by')" />
                        <select id="sort" name="sort"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="updated_desc" @selected($sort === 'updated_desc')>
                                {{ __('Recently updated') }}
                            </option>
                            <option value="updated_asc" @selected($sort === 'updated_asc')>
                                {{ __('Oldest updated') }}
                            </option>
                            <option value="title_asc" @selected($sort === 'title_asc')>
                                {{ __('Title A–Z') }}
                            </option>
                            <option value="title_desc" @selected($sort === 'title_desc')>
                                {{ __('Title Z–A') }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <x-primary-button>{{ __('Search') }}</x-primary-button>
                    @if (request('search') || request('tag') || request('folder') || request('sort'))
                        <a href="{{ route('notes.index') }}" class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Clear filters') }}
                        </a>
                    @endif
                </div>
            </form>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($notes->count() === 0)
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ __('No notes found. Create your first note!') }}
                        </p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($notes as $note)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                                    <a href="{{ route('notes.show', $note) }}" class="block">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $note->title }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                                            {{ $note->content }}
                                        </p>
                                    </a>

                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                        @if ($note->folder)
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                                                {{ $note->folder->name }}
                                            </span>
                                        @endif

                                        @foreach ($note->tags as $tag)
                                            <a href="{{ route('notes.index', ['tag' => $tag->id]) }}"
                                               class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded hover:bg-indigo-200 dark:hover:bg-indigo-800">
                                                #{{ $tag->name }}
                                            </a>
                                        @endforeach

                                        @if ($note->is_public)
                                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded">
                                                {{ __('Public') }}
                                            </span>
                                        @endif

                                        @if ($note->user_id !== Auth::id())
                                            <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded">
                                                {{ $note->owner->name }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                                        {{ $note->updated_at->format('M j, Y H:i') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $notes->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
