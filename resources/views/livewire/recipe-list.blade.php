<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Search and Filter -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1">
                    <input wire:model.debounce.300ms="search" type="text"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        placeholder="Cari resep...">
                </div>
                <div class="w-full md:w-48">
                    <select wire:model="category"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Recipe Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($recipes as $recipe)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                @if($recipe->image_url)
                <img src="{{ Storage::url($recipe->image_url) }}"
                    alt="{{ $recipe->title }}"
                    class="w-full h-48 object-cover">
                @endif
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $recipe->title }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ Str::limit($recipe->description, 100) }}
                    </p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            {{ $recipe->cooking_time }} menit
                        </span>
                        <span class="px-2 py-1 text-xs font-semibold text-indigo-600 bg-indigo-100 rounded-full">
                            {{ $categories[$recipe->category] }}
                        </span>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('recipes.show', $recipe) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Lihat Resep
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $recipes->links() }}
        </div>
    </div>
</div>