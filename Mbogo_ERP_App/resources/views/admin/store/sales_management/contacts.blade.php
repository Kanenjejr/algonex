@extends('layouts.salesMaster')

@section('content')

<div class="wrapper wrapper-content">

    {{-- HEADER --}}
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-lg-12">
            <h2>Contacts Management</h2>
        </div>
    </div>

    <div class="row mt-3">

        {{-- ================= ADD CONTACT ================= --}}
        <div class="col-md-3">
            @can('Create-Contacts')
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    Add Contact
                </div>

                <div class="card-body">

                    {{-- ALERT --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contacts.store') }}" method="POST">
                        @csrf

                        <div class="mb-2">
                            <label>First Name</label>
                            <input type="text" name="first_name"
                                   value="{{ old('first_name') }}"
                                   class="form-control" required>
                        </div>

                        <div class="mb-2">
                            <label>Last Name</label>
                            <input type="text" name="last_name"
                                   value="{{ old('last_name') }}"
                                   class="form-control">
                        </div>

                        <div class="mb-2">
                            <label>Phone</label>
                            <input type="text" name="phone"
                                   value="{{ old('phone') }}"
                                   class="form-control">
                        </div>

                        <div class="mb-2">
                            <label>Email</label>
                            <input type="email" name="email"
                                   value="{{ old('email') }}"
                                   class="form-control">
                        </div>

                        <button class="btn btn-success w-100">
                            Save Contact
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>

        {{-- ================= CONTACT LIST ================= --}}
        <div class="col-md-9">
            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Contacts List</span>

                    <form method="GET" class="d-flex">
                        <input type="text" name="search"
                               value="{{ request('search') }}"
                               class="form-control me-2"
                               placeholder="Search...">
                        <button class="btn btn-primary">Search</button>
                    </form>
                </div>

                <div class="card-body">

                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($contacts as $key => $contact)
                            <tr>
                                <td>{{ $contacts->firstItem() + $key }}</td>

                                <td>
                                    {{ $contact->first_name }} {{ $contact->last_name }}
                                </td>

                                <td>{{ $contact->phone }}</td>
                                <td>{{ $contact->email }}</td>

                                <td>
                                    @can('Edit-Contacts')
                                    <button class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $contact->id }}">
                                        Edit
                                    </button>
                                    @endcan

                                    @can('Delete-Contacts')
                                    <form action="{{ route('contacts.delete',$contact->id) }}"
                                          method="POST"
                                          style="display:inline">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this contact?')"
                                                class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>

                            {{-- EDIT MODAL --}}
                            <div class="modal fade" id="editModal{{ $contact->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <form method="POST" action="{{ route('contacts.update',$contact->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header bg-warning">
                                                <h5>Edit Contact</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">

                                                <div class="mb-2">
                                                    <label>First Name</label>
                                                    <input type="text" name="first_name"
                                                           value="{{ $contact->first_name }}"
                                                           class="form-control" required>
                                                </div>

                                                <div class="mb-2">
                                                    <label>Last Name</label>
                                                    <input type="text" name="last_name"
                                                           value="{{ $contact->last_name }}"
                                                           class="form-control">
                                                </div>

                                                <div class="mb-2">
                                                    <label>Phone</label>
                                                    <input type="text" name="phone"
                                                           value="{{ $contact->phone }}"
                                                           class="form-control">
                                                </div>

                                                <div class="mb-2">
                                                    <label>Email</label>
                                                    <input type="email" name="email"
                                                           value="{{ $contact->email }}"
                                                           class="form-control">
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">
                                                    Update Contact
                                                </button>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>

                            @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    No contacts found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $contacts->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

{{-- AUTO HIDE ALERT --}}
<script>
document.querySelectorAll('.alert').forEach(el => {
    setTimeout(() => el.style.display = 'none', 3000);
});
</script>

@endsection