<div>
    <h2>Create Task</h2>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div style="color: green;">{{ session('message') }}</div>
    @endif

    <!-- Task Form -->
    <form wire:submit.prevent="store">
        <div>
            <label for="name">Task Name:</label>
            <input type="text" id="name" wire:model="name">
            @error('name') <span style="color: red;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="description">Description:</label>
            <textarea id="description" wire:model="description"></textarea>
            @error('description') <span style="color: red;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="deadline">Deadline:</label>
            <input type="date" id="deadline" wire:model="deadline">
            @error('deadline') <span style="color: red;">{{ $message }}</span> @enderror
        </div>

        <button type="submit">Create Task</button>
    </form>

    <hr>

    <!-- Task List -->
    <h3>Task List</h3>
    <table border="1">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Description</th>
                <th>Deadline</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $task)
                <tr>
                    <td>{{ $task->id }}</td>
                    <td>{{ $task->name }}</td>
                    <td>{{ $task->description }}</td>
                    <td>{{ $task->deadline }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
