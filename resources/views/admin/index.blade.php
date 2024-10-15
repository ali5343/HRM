<x-layout>
    <div>
        <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
            <!-- Total Users -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Users</h2>
                <p class="text-3xl font-bold">{{ $usersWithAttendance->count() }}</p>
            </div>

            <!-- User Dropdown to Select Total Hours -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Select User</h2>
                <select id="userDropdown" class="block w-full p-2 border rounded">
                    <option value="">-- Select a user --</option>
                    @foreach ($usersWithAttendance as $user)
                        <option value="{{ $user->id }}" data-hours="{{ $user->total_hours }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Display Total Hours for Selected User -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Hours</h2>
                <p id="userHours" class="text-3xl font-bold">--</p>
            </div>
        </div>
    </div>

    <!-- Add JavaScript to handle the user selection -->
    <script>
        document.getElementById('userDropdown').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const totalHours = selectedOption.getAttribute('data-hours');
            document.getElementById('userHours').textContent = totalHours ? totalHours + ' hours' : '--';
        });
    </script>
</x-layout>
