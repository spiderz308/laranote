<li class="border border-gray-200 dark:border-gray-700 rounded-lg">
    <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50">
        <a href="{{ route('folders.show', $folder) }}" class="flex items-center gap-2 font-medium text-gray-900 dark:text-gray-100">
            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M2 6a2 2 0 012-2h4l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
            </svg>
            {{ $folder->name }}
            @if ($folder->notes_count ?? null)
                <span class="text-xs text-gray-500 dark:text-gray-400">({{ $folder->notes_count }})</span>
            @endif
        </a>
        <div class="flex items-center gap-2 text-xs">
            @if (($folder->notes_count ?? 0) > 0)
                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-400">
                    {{ $folder->notes_count }} {{ Str::plural('note', $folder->notes_count) }}
                </span>
            @endif
            <a href="{{ route('folders.edit', $folder) }}"
               class="px-2 py-1 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded hover:bg-indigo-200 dark:hover:bg-indigo-800">
                {{ __('Manage') }}
            </a>
            <a href="{{ route('notes.create', ['folder_id' => $folder->id]) }}"
               class="px-2 py-1 bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 rounded hover:bg-green-200 dark:hover:bg-green-800">
                {{ __('New Note') }}
            </a>
        </div>
    </div>

    @if ($folder->children->isNotEmpty())
        <ul class="ms-6 space-y-2 p-2">
            @each('folders.partials.tree-item', $folder->children, 'folder')
        </ul>
    @endif
</li>