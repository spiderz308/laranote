<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Note') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('notes.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="mt-1 block w-full" type="text" name="title"
                                          :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="content" :value="__('Content')" />
                            <textarea id="content" name="content" rows="10" required
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('content') }}</textarea>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="folder_id" :value="__('Folder')" />
                            <select id="folder_id" name="folder_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">No folder</option>
                                @foreach ($folders as $folder)
                                    <option value="{{ $folder->id }}" @selected(old('folder_id', request('folder_id')) == $folder->id)>
                                        {{ $folder->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('folder_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="tags" :value="__('Tags')" />
                            <x-text-input id="tags" class="mt-1 block w-full" type="text" name="tags"
                                          :value="old('tags')" placeholder="{{ __('Comma separated, e.g. todo, idea') }}" />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Existing tags') }}: @foreach ($tags as $tag)
                                    <span class="px-1 py-0.5 bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded">{{ $tag->name }}</span>
                                @endforeach
                            </p>
                            <x-input-error :messages="$errors->get('tags')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-3">
                            <input id="is_public" type="checkbox" name="is_public" value="1"
                                   @checked(old('is_public'))
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700">
                            <x-input-label for="is_public" :value="__('Make this note public')" />
                        </div>

                        <div class="flex items-center gap-3">
                            <x-primary-button>{{ __('Save Note') }}</x-primary-button>
                            <a href="{{ route('notes.index') }}"
                               class="text-sm text-gray-600 dark:text-gray-400">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
