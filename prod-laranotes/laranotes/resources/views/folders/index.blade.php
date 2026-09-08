<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Folders') }}
            </h2>
            <a href="{{ route('folders.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('New Folder') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'folder-deleted')
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/50 border-l-4 border-green-400 text-green-700 dark:text-green-300 rounded">
                    {{ __('Folder deleted.') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if ($folders->isEmpty())
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ __('No folders yet. Create your first folder to organize your notes.') }}
                    </p>
                @else
                    <ul class="space-y-2">
                        @each('folders.partials.tree-item', $folders, 'folder')
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>