<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $folder->name }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('folders.edit', $folder) }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Manage') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                @if ($folder->parent)
                    <a href="{{ route('folders.show', $folder->parent) }}" class="hover:underline">
                        {{ $folder->parent->name }}
                    </a>
                    <span>/</span>
                @endif
                <span class="text-gray-900 dark:text-gray-100">{{ $folder->name }}</span>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($notes->count() === 0)
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('No notes in this folder yet.') }}
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
                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    @foreach ($note->tags as $tag)
                                        <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded">
                                            #{{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $notes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>