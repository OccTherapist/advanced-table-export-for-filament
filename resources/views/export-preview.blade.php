<div class="space-y-3">
    <div class="text-sm text-gray-600 dark:text-gray-300">
        {{ __('advanced-table-export-for-filament::export.preview_summary', [
            'from' => $from,
            'to' => $to,
            'total' => $total,
        ]) }}
        <span class="mx-2">·</span>
        {{ __('advanced-table-export-for-filament::export.preview_page', [
            'page' => $page,
            'lastPage' => $lastPage,
        ]) }}
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 text-xs dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    @foreach ($headers as $header)
                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-200">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($rows as $row)
                    <tr>
                        @foreach (array_keys($headerKeys) as $key)
                            <td class="px-3 py-2 text-gray-800 dark:text-gray-100">
                                {{ $row[$key] ?? '' }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ max(count($headerKeys), 1) }}" class="px-3 py-4 text-center text-gray-500">
                            {{ __('advanced-table-export-for-filament::export.preview_empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
