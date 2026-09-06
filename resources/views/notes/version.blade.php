<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Version') }}: {{ $note->title }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('notes.show', $note) }}"
                   class="text-sm text-gray-600 dark:text-gray-400">{{ __('Back to note') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Saved') }}: {{ $version->created_at->format('M j, Y H:i') }}
                        @if ($version->user)
                            · {{ __('by') }} {{ $version->user->name }}
                        @endif
                    </p>
                    <form method="POST" action="{{ route('notes.versions.restore', [$note, $version]) }}"
                          onsubmit="return confirm('{{ __('Restore this version? The current content will be kept as a version.') }}')">
                        @csrf
                        <x-primary-button>{{ __('Restore this version') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 mb-4">
                        {{ $version->title }}
                    </h3>

                    <div class="prose prose-sm max-w-none text-gray-800 dark:text-gray-200 whitespace-pre-wrap">
                        {{ $version->content }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>