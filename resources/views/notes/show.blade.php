<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $note->title }}
            </h2>
            <div class="flex items-center gap-3">
                @if (Auth::user() && $note->user_id === Auth::id())
                    <a href="{{ route('notes.share', $note) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('Share') }}
                    </a>
                    <a href="{{ route('notes.edit', $note) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('Edit') }}
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (request()->routeIs('notes.public'))
                <div class="mb-4 p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('This is a public note.') }}
                    </p>
                </div>
            @endif

            @if (session('status') === 'note-updated')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Note updated successfully.') }}
                </div>
            @elseif (session('status') === 'collaborators-synced')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Collaborators updated.') }}
                </div>
            @elseif (session('status') === 'version-restored')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Note restored to that version.') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap items-center gap-2 text-sm mb-4">
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

                        @if ($note->is_public)
                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded">
                                {{ __('Public') }}
                            </span>
                        @endif
                    </div>

                    <div class="prose prose-sm max-w-none text-gray-800 dark:text-gray-200 whitespace-pre-wrap">
                        {{ $note->content }}
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Created') }}: {{ $note->created_at->format('M j, Y H:i') }} ·
                        {{ __('Last updated') }}: {{ $note->updated_at->format('M j, Y H:i') }}
                    </div>

                    @if (Auth::user() && $note->user_id === Auth::id())
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-4">
                            @if ($note->is_public)
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('Share link') }}:</span>
                                    <input type="text" readonly value="{{ route('notes.public', $note) }}"
                                           class="w-64 text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                           onfocus="this.select()">
                                </div>
                            @endif

                            <form method="POST" action="{{ route('notes.destroy', $note) }}" class="ms-auto"
                                  onsubmit="return confirm('{{ __('Delete this note?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    @can('update', $note)
                        @if ($note->versions->count() > 0)
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                    {{ __('Version History') }}
                                </h3>
                                <ul class="space-y-2">
                                    @foreach ($note->versions as $index => $version)
                                        <li class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-3">
                                            <a href="{{ route('notes.versions.show', [$note, $version]) }}"
                                               class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                v{{ $note->versions->count() - $index }}
                                            </a>
                                            ·
                                            {{ $version->created_at->format('M j, Y H:i') }}
                                            @if ($version->user)
                                                · {{ $version->user->name }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>