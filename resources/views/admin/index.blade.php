<x-layout>
    <div>
        <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Users</h2>
                <p class="text-3xl font-bold">{{$allUsers}}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Requests</h2>
                <p class="text-3xl font-bold">50</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-bold">Total Leaves</h2>
                <p class="text-3xl font-bold">10</p>
            </div>
        </div>
    </div>
</x-layout>