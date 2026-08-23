@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-6">Role Permissions</h1>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>  
        @endif

        <!-- Search Box -->
        <div class="mb-4">
            <input type="text" id="permissionSearch" placeholder="Feature खोज्नुहोस्..." class="border p-2 rounded w-full text-sm" onkeyup="filterPermissions()">
        </div>

        <form action="{{ route('permissions.update') }}" method="POST">
            @csrf
            <table id="permissionTable" class="w-full border-collapse border border-gray-200 mb-6">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Feature</th>  
                        @foreach($roles as $role)
                            <th class="border p-2 text-center capitalize">{{ $role }}</th>  
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($permissions as $key => $label)
                    <tr class="perm-row">
                        <td class="border p-2 feature-name">{{ $label }}</td>  
                        @foreach($roles as $role)
                            <td class="border p-2 text-center">
                                <input type="checkbox"
                                       name="permissions[{{ $role }}][{{ $key }}]"
                                       class="w-5 h-5"
                                       {{ in_array($role . '|' . $key, $existing) ? 'checked' : '' }}>  
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Save Permissions
            </button>
        </form>
    </div>

    <script>
    function filterPermissions() {
        let input = document.getElementById('permissionSearch').value.toLowerCase();
        let rows = document.querySelectorAll('.perm-row');
        rows.forEach(row => {
            let text = row.querySelector('.feature-name').innerText.toLowerCase();
            row.style.display = text.includes(input) ? '' : 'none';
        });
    }
    </script>
@endsection