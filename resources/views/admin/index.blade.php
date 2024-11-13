<x-layout>
    @php
        function formatHoursMinutes($decimalHours) {
            $hours = floor($decimalHours); // Extract hours
            $minutes = round(($decimalHours - $hours) * 60); // Extract minutes
            return $hours . ' hours ' . $minutes . ' minutes';
        }
    @endphp
    <div class="p-6 bg-gray-100">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Total Users -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Select User</h2>
                <select id="userDropdown"
                        class="block w-full p-3 border border-gray-300 rounded-md text-gray-700 focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">-- Select a user --</option>
                    @foreach ($userData as $user)
                        @php
                            $leaveHours = $user->leave_hours ?? 0;
                            $deductedHours = 36 - ($leaveHours * 8); // Subtract leave hours from total
                        @endphp
                        <option value="{{ $user->id }}"
                                data-weekly-hours="{{ $user->weekly_hours }}"
                                data-monthly-hours="{{ $user->monthly_hours }}"
                                data-remaining-weekly-hours="{{ $user->remaining_weekly_hours }}"
                                data-remaining-monthly-hours="{{ $user->remaining_monthly_hours }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Display Weekly and Monthly Hours for Selected User -->
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">User Hours</h2>
                <div class="mt-4">
                    <p class="text-lg"><strong>Weekly Hours:</strong> <span id="userWeeklyHours"
                                                                            class="text-indigo-600 font-medium">--</span></p>
                    <p class="text-lg mt-2"><strong>Remaining Weekly Hours:</strong> <span id="userRemainingWeeklyHours"
                                                                                           class="text-indigo-600 font-medium">--</span></p>
                </div>
                <div class="mt-4">
                    <p class="text-lg"><strong>Monthly Hours:</strong> <span id="userMonthlyHours"
                                                                             class="text-indigo-600 font-medium">--</span></p>
                    <p class="text-lg mt-2"><strong>Remaining Monthly Hours:</strong> <span
                            id="userRemainingMonthlyHours" class="text-indigo-600 font-medium">--</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Attendance Stats -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Admin Attendance</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <!-- Today's Hours Card -->
            <div class="flex items-center bg-white border rounded-lg overflow-hidden shadow-lg transition-transform transform hover:scale-105">
                <div class="p-4 bg-green-500 rounded-l-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="px-4 py-6 text-gray-800">
                    <h3 class="text-sm font-semibold tracking-wider">Today's Hours</h3>
                    <p class="text-3xl font-bold">
                        @if($adminAttendance->today_hours)
                            @php
                                $hours = floor($adminAttendance->today_hours);
                                $minutes = floor(($adminAttendance->today_hours - $hours) * 60);
                                $seconds = floor((($adminAttendance->today_hours - $hours) * 60 - $minutes) * 60);
                            @endphp
                            {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>

            <!-- Weekly Hours Card -->
            <div class="flex items-center bg-white border rounded-lg overflow-hidden shadow-lg transition-transform transform hover:scale-105">
                <div class="p-4 bg-green-500 rounded-l-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="px-4 py-6 text-gray-800">
                    <h3 class="text-sm font-semibold tracking-wider">Weekly Hours</h3>
                    <p class="text-3xl font-bold">
                        @if($adminAttendance->weekly_hours)
                            @php
                                $hours = floor($adminAttendance->weekly_hours);
                                $minutes = floor(($adminAttendance->weekly_hours - $hours) * 60);
                                $seconds = floor((($adminAttendance->weekly_hours - $hours) * 60 - $minutes) * 60);
                            @endphp
                            {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>

            <!-- Monthly Hours Card -->
            <div class="flex items-center bg-white border rounded-lg overflow-hidden shadow-lg transition-transform transform hover:scale-105">
                <div class="p-4 bg-purple-500 rounded-l-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="px-4 py-6 text-gray-800">
                    <h3 class="text-sm font-semibold tracking-wider">Monthly Hours</h3>
                    <p class="text-3xl font-bold">
                        @if($adminAttendance->monthly_hours)
                            @php
                                $hours = floor($adminAttendance->monthly_hours);
                                $minutes = floor(($adminAttendance->monthly_hours - $hours) * 60);
                                $seconds = floor((($adminAttendance->monthly_hours - $hours) * 60 - $minutes) * 60);
                            @endphp
                            {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>

            <!-- Remaining Weekly Hours Card -->
            <div class="flex items-center bg-white border rounded-lg overflow-hidden shadow-lg transition-transform transform hover:scale-105">
                <div class="p-4 bg-red-500 rounded-l-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="px-4 py-6 text-gray-800">
                    <h3 class="text-sm font-semibold tracking-wider">Remaining Weekly Hours</h3>
                    <p class="text-3xl font-bold">
                        @if($adminAttendance->remaining_weekly_hours)
                            @php
                                $hours = floor($adminAttendance->remaining_weekly_hours);
                                $minutes = floor(($adminAttendance->remaining_weekly_hours - $hours) * 60);
                                $seconds = floor((($adminAttendance->remaining_weekly_hours - $hours) * 60 - $minutes) * 60);
                            @endphp
                            {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>

            <!-- Remaining Monthly Hours Card -->
            <div class="flex items-center bg-white border rounded-lg overflow-hidden shadow-lg transition-transform transform hover:scale-105">
                <div class="p-4 bg-red-500 rounded-l-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="px-4 py-6 text-gray-800">
                    <h3 class="text-sm font-semibold tracking-wider">Remaining Monthly Hours</h3>
                    <p class="text-3xl font-bold">
                        @if($adminAttendance->remaining_monthly_hours)
                            @php
                                $hours = floor($adminAttendance->remaining_monthly_hours);
                                $minutes = floor(($adminAttendance->remaining_monthly_hours - $hours) * 60);
                                $seconds = floor((($adminAttendance->remaining_monthly_hours - $hours) * 60 - $minutes) * 60);
                            @endphp
                            {{ sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds) }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript to handle the user selection and format hours and minutes -->
    <script>
        document.getElementById('userDropdown').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const weeklyHours = parseFloat(selectedOption.getAttribute('data-weekly-hours'));
            const remainingWeeklyHours = parseFloat(selectedOption.getAttribute('data-remaining-weekly-hours'));
            const monthlyHours = parseFloat(selectedOption.getAttribute('data-monthly-hours'));
            const remainingMonthlyHours = parseFloat(selectedOption.getAttribute('data-remaining-monthly-hours'));


            // Convert hours to hours and minutes format
            const formatHoursMinutes = (totalHours) => {
                const hours = Math.floor(totalHours);
                const minutes = Math.round((totalHours - hours) * 60);
                return `${hours} hours ${minutes} minutes`;
            };

            document.getElementById('userWeeklyHours').textContent = weeklyHours ? formatHoursMinutes(weeklyHours) : '--';
            document.getElementById('userRemainingWeeklyHours').textContent = remainingWeeklyHours ? formatHoursMinutes(remainingWeeklyHours) : '--';
            document.getElementById('userMonthlyHours').textContent = monthlyHours ? formatHoursMinutes(monthlyHours) : '--';
            document.getElementById('userRemainingMonthlyHours').textContent = remainingMonthlyHours ? formatHoursMinutes(remainingMonthlyHours) : '--';
        });
    </script>

</x-layout>
