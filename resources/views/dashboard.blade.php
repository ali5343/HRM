<x-layout>
    <div class="grid grid-cols-1 gap-4 px-4 mt-8 sm:grid-cols-4 sm:px-8">
        <div class="flex flex-col bg-white border rounded-lg shadow-md overflow-hidden">
            <div class="flex items-center p-4 bg-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
                <h3 class="ml-4 text-xl font-semibold text-white">Attendance Records</h3>
            </div>
            <div class="p-4">
                @if ($attendances->isEmpty())
                    <p class="text-gray-600">No attendance records found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($attendances as $attendance)
                                    <tr>
                                        <td class="py-2 px-4 whitespace-nowrap text-gray-700">
                                            {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('Y-m-d H:i:s') : 'N/A' }}
                                        </td>
                                        <td class="py-2 px-4 whitespace-nowrap text-gray-700">
                                            {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('Y-m-d H:i:s') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4 px-4 mt-8 sm:grid-cols-4 sm:px-8">
        <div class="flex flex-col bg-white border rounded-lg shadow-md overflow-hidden">
            <div class="flex items-center p-4 bg-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
                <h3 class="ml-4 text-xl font-semibold text-white">Weekly  Records</h3>
            </div>
            <div class="p-4">
                @if ($attendances->isEmpty())
                    <p class="text-gray-600">No attendance records found.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Clock In</th>
                                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">Clock Out</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($attendances as $attendance)
                                    <tr>
                                        <td class="py-2 px-4 whitespace-nowrap text-gray-700">
                                            {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('Y-m-d H:i:s') : 'N/A' }}
                                        </td>
                                        <td class="py-2 px-4 whitespace-nowrap text-gray-700">
                                            {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('Y-m-d H:i:s') : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
