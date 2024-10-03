<x-layout>
    <div class="text-center text-gray-600">
        <div class="border border-gray-200 shadow-lg rounded-xl p-8 bg-white max-w-lg mx-auto">
            <h1 class="text-3xl font-semibold mb-8 text-gray-800">Daily Attendance</h1>

            <!-- Success Message -->
            @if (session('success'))
                <div class="text-green-500 mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Clock In Form -->
            <form method="POST" action="/daily">
                @csrf
                <button type="submit"
                    class="text-white bg-green-600 hover:bg-green-500 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-base px-6 py-3 mb-4 transition duration-300 ease-in-out"
                    @if ($isClockedIn) disabled @endif>
                    Clock In
                </button>
            </form>

            <!-- Clock Out Form -->
            <form method="POST" action="/daily">
                @csrf
                <button type="submit"
                    class="text-white bg-red-600 hover:bg-red-500 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-base px-6 py-3 mb-4 transition duration-300 ease-in-out"
                    @if (!$isClockedIn) disabled @endif>
                    Clock Out
                </button>
            </form>

            <!-- Meeting Button -->
            <button type="button"
                class="text-white bg-gray-800 hover:bg-gray-700 focus:ring-4 focus:outline-none focus:ring-gray-500 font-medium rounded-lg text-base px-6 py-3 transition duration-300 ease-in-out"
                onclick="toggleClockElements()">
                Meeting
            </button>

            <!-- Hidden Clock-In, Clock-Out, and Submit Elements -->
            <div id="clock-elements" class="hidden space-y-4 mt-6">
                <!-- Clock In Button Inside -->
                <button
                    class="text-white bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-base px-6 py-3 transition duration-300 ease-in-out">
                    Clock In
                </button>

                <!-- Clock Out Button Inside -->
                <button
                    class="text-white bg-red-600 hover:bg-red-500 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-base px-6 py-3 transition duration-300 ease-in-out">
                    Clock Out
                </button>

                <!-- Text Box with Submit Button -->
                <div>
                    <input type="text" placeholder="Enter details"
                        class="w-full p-4 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-base focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400 transition duration-300 ease-in-out" />
                    <button
                        class="text-white bg-green-600 hover:bg-green-500 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-base px-6 py-3 mt-4 transition duration-300 ease-in-out">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-layout>
