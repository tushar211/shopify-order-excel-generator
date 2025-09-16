<x-layout>
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Shopify Orders</h1>

        <div class="flex flex-wrap gap-4 mb-6">
            <!-- Date Range & Fetch -->
            <form id="orderForm" action="{{ url('/') }}" method="GET"
                  class="flex flex-wrap items-center gap-4 bg-white p-4 rounded shadow">
                <label class="font-semibold">Date Range:</label>
                <input type="text" id="dateRange" class="border border-gray-300 rounded px-3 py-2 w-64"
                       placeholder="Select date range">
                <input type="hidden" name="startDate" id="startDate" value="{{ request('startDate') }}">
                <input type="hidden" name="endDate" id="endDate" value="{{ request('endDate') }}">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded">
                    Fetch Orders
                </button>
            </form>

            <!-- Download Excel -->
            <form action="{{ url('/orders/download') }}" method="GET" class="flex items-center">
                <input type="hidden" name="startDate" value="{{ request('startDate') }}">
                <input type="hidden" name="endDate" value="{{ request('endDate') }}">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded">
                    Download Excel
                </button>
            </form>
        </div>
            <a href="{{ route('products.fetch') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4 inline-block">
                Sync Products for cost per item
            </a>

        @if(isset($orders) && count($orders) > 0)
            <div class="bg-white rounded shadow p-4 mt-4">
                <h2 class="text-xl font-semibold mb-4">Orders Preview ({{ count($orders) }} orders)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Order Name</th>
                            <th class="px-4 py-2">SKU</th>
                            <th class="px-4 py-2">Payment Status</th>
                            <th class="px-4 py-2">Cost per item</th>
                            <th class="px-4 py-2">Sale Price</th>
                            <th class="px-4 py-2">Payment Receive</th>
                            <th class="px-4 py-2">Fulfillment Status</th>
                            <th class="px-4 py-2">Quantity</th>
                        </tr>
                        </thead>
                        <tbody class="text-gray-700">
                        @php $count = 1; @endphp
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-100">
                                <td class="border px-4 py-2">{{ $count++ }}</td>
                                <td class="border px-4 py-2">{{ $order['name'] }}</td>
                                <td class="border px-4 py-2">{{ $order['skus'] }}</td>
                                <td class="border px-4 py-2">{{ $order['financial_status'] }}</td>
                                <td class="border px-4 py-2">{{ $order['costs'] }}</td>
                                <td class="border px-4 py-2">{{ $order['prices'] }}</td>
                                <td class="border px-4 py-2">{{ $order['total_price'] }}</td>
                                <td class="border px-4 py-2">{{ $order['fulfillment_status'] }}</td>
                                <td class="border px-4 py-2">{{ $order['quantities'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded shadow p-4 mt-4 text-gray-600">
                No orders found for the selected date range.
            </div>
        @endif

    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: ["{{ $startDate }}", "{{ $endDate }}"],
            onClose: function (selectedDates, dateStr) {
                if (dateStr.includes(" to ")) {
                    const [start, end] = dateStr.split(" to ");
                    document.getElementById('startDate').value = start;
                    document.getElementById('endDate').value = end;
                } else if (dateStr) {
                    document.getElementById('startDate').value = dateStr;
                    document.getElementById('endDate').value = dateStr;
                }
            }
        });
    </script>
</x-layout>
