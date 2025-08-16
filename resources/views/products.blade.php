<x-layout>
    <div class="max-w-7xl mx-auto p-8">
        <h1 class="text-3xl font-bold mb-6">Products</h1>

        <a href="{{ route('products.fetch') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block">
            Fetch Products from Shopify
        </a>

        @if(session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">SKU</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Cost</th>
                </tr>
                </thead>
                <tbody class="text-gray-700">
                @php $count = 1; @endphp
                @foreach($products as $product)
                    <tr class="hover:bg-gray-100">
                        <td class="border px-4 py-2">{{ $count++ }}</td>
                        <td class="border px-4 py-2">{{ $product->title }}</td>
                        <td class="border px-4 py-2">{{ $product->sku }}</td>
                        <td class="border px-4 py-2">{{ $product->price }}</td>
                        <td class="border px-4 py-2">{{ $product->cost }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-layout>