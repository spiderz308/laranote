<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Trash') }}
            </h2>
            <a href="{{ route('notes.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Back to notes') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'note-restored')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Note restored.') }}
                </div>
            @elseif (session('status') === 'note-purged')
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/50 border-l-4 border-red-400 text-red-700 dark:text-red-300 rounded">
                    {{ __('Note permanently deleted.') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($notes->count() === 0)
                        <p class="text-gray-600 dark:text-gray-400">
                            {{ __('Trash is empty.') }}
                        </p>
                    @else
                        <div class="space-y-4">
                            @foreach ($notes as $note)
                                <div class="flex items-center justify-between border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                                            {{ $note->title }}
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ __('Deleted') }}: {{ $note->deleted_at->format('M j, Y H:i') }}</span>
                                            @if ($note->folder)
                                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                                                    {{ $note->folder->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 ms-4 shrink-0">
                                        <form method="POST" action="{{ route('notes.restore', $note) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                {{ __('Restore') }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('notes.forceDelete', $note) }}"
                                              onsubmit="return confirm('{{ __('Permanently delete this note? This cannot be undone.') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                {{ __('Delete Forever') }}
                                            </button>
                                        </form>
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
    </div>
</x-app-layout>