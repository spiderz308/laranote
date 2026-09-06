<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Stats --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('notes.index') }}"
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('My Notes') }}</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $ownNoteCount }}</p>
                </a>
                <a href="{{ route('notes.index') }}"
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Shared with me') }}</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $sharedWithMeCount }}</p>
                </a>
                <a href="{{ route('folders.index') }}"
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Folders') }}</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $folderCount }}</p>
                </a>
                <a href="{{ route('notes.trash') }}"
                   class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Trash') }}</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $trashedCount }}</p>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Recent notes --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ __('Recent Notes') }}</h3>
                            <a href="{{ route('notes.index') }}"
                               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">{{ __('View all') }}</a>
                        </div>

                        @if ($recentNotes->count() === 0)
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ __('No notes yet. Create your first note!') }}
                            </p>
                            <a href="{{ route('notes.create') }}"
                               class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('New Note') }}
                            </a>
                        @else
                            <ul class="space-y-3">
                                @foreach ($recentNotes as $note)
                                    <li>
                                        <a href="{{ route('notes.show', $note) }}"
                                           class="block border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition">
                                            <div class="flex items-center justify-between">
                                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $note->title }}
                                                </h4>
                                                <span class="text-xs text-gray-400 dark:text-gray-500 ms-3 shrink-0">
                                                    {{ $note->updated_at->format('M j, Y H:i') }}
                                                </span>
                                            </div>
                                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                                @if ($note->folder)
                                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                                                        {{ $note->folder->name }}
                                                    </span>
                                                @endif
                                                @foreach ($note->tags as $tag)
                                                    <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded">
                                                        #{{ $tag->name }}
                                                    </span>
                                                @endforeach
                                                @if ($note->user_id !== Auth::id())
                                                    <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 rounded">
                                                        {{ $note->owner->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- Top tags --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('Top Tags') }}</h3>

                        @if ($topTags->count() === 0)
                            <p class="text-gray-600 dark:text-gray-400">{{ __('No tags yet.') }}</p>
                        @else
                            <ul class="space-y-2">
                                @foreach ($topTags as $tag)
                                    <li>
                                        <a href="{{ route('notes.index', ['tag' => $tag->id]) }}"
                                           class="flex items-center justify-between text-sm">
                                            <span class="text-indigo-600 dark:text-indigo-400 hover:underline">#{{ $tag->name }}</span>
                                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs text-gray-600 dark:text-gray-400">
                                                {{ $tag->total }}
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>