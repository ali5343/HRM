<x-layout>
    <div class="container">
        <h1>Admin Dashboard - Pending Requests</h1>

        <!-- Display success or error messages -->
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        @endif

        <!-- Assuming you have a collection of requests to display -->

        @if ($requests->isEmpty())
        <p>No pending requests at the moment.</p>
        @else
        <table class="table">
            <thead>
                <tr>
                    <th>Request Type</th>
                    <th>User ID</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Total Hours</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($requests as $request)
                <tr>
                    <td>{{ ucfirst($request->type) }}</td>
                    <td>{{ $request->user_id }}</td>
                    <td>{{ $request->start_time }}</td>
                    <td>{{ $request->end_time }}</td>
                    <td>{{ $request->total_hours }}</td>
                    <td>
                        <form action="{{ route('admin.requests.approve', $request->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</x-layout>