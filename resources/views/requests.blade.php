<x-layout>
    <form action="{{ route('request.create') }}" method="POST">
        @csrf
        <select name="type" required>
            <option value="meeting">Meeting</option>
            <option value="weekend">Weekend Work</option>
            <option value="wfh">Work from Home</option>
            <option value="overtime">Overtime</option>
            <option value="leave">Leave</option>
        </select>

        <input type="datetime-local" name="start_time" placeholder="Start Time">
        <input type="datetime-local" name="end_time" placeholder="End Time">
        <textarea name="reason" placeholder="Reason (Optional)"></textarea>

        <button type="submit">Submit Request</button>
    </form>


</x-layout>