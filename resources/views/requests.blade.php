<x-layout>
<form action="{{ route('request.create') }}" method="POST" class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg">
    @csrf
    <div class="mb-4">
        <label for="type" class="block text-gray-700 font-medium mb-2">Request Type</label>
        <select name="type" id="type" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="meeting">Meeting</option>
            <option value="weekend">Weekend Work</option>
            <option value="wfh">Work from Home</option>
            <option value="overtime">Overtime</option>
            <option value="leave">Leave</option>
        </select>
    </div>

    <div class="mb-4">
        <label for="start_time" class="block text-gray-700 font-medium mb-2">Start Time</label>
        <input type="datetime-local" name="start_time" id="start_time" placeholder="Start Time" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="mb-4">
        <label for="end_time" class="block text-gray-700 font-medium mb-2">End Time</label>
        <input type="datetime-local" name="end_time" id="end_time" placeholder="End Time" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>

    <div class="mb-4">
        <label for="reason" class="block text-gray-700 font-medium mb-2">Reason (Optional)</label>
        <textarea name="reason" id="reason" rows="3" placeholder="Reason (Optional)" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            Submit Request
        </button>
    </div>
</form>

<script>
    document.getElementById('type').addEventListener('change', function () {
        const startTime = document.getElementById('start_time');
        const endTime = document.getElementById('end_time');
        
        if (this.value === 'leave') {
            startTime.type = 'date'; // Only show date picker for leave
            endTime.type = 'date';
        } else {
            startTime.type = 'datetime-local'; // Show time picker for other options
            endTime.type = 'datetime-local';
        }
    });
</script>




</x-layout>