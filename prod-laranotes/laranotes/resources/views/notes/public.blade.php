<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-2xl bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-8">
            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
                @foreach ($note->tags as $tag)
                    <span class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded">
                        #{{ $tag->name }}
                    </span>
                @endforeach
            </div>

            <h1 class="font-semibold text-2xl text-gray-900 dark:text-gray-100 mb-4">
                {{ $note->title }}
            </h1>

            <div class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">
                {{ $note->content }}
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400">
                {{ __('Published') }}: {{ $note->created_at->format('M j, Y H:i') }}
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('Login to view your notes') }}
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>