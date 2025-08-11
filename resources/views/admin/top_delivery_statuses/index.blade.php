@include('admin.includes.header')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif

                    <h4 class="card-title">Top Delivery Statuses</h4>
                    <a href="{{ route('top-delivery-statuses.create') }}" class="btn btn-primary mb-3">Create</a>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Status IDs</th>
                                    <th>Title (EN)</th>
                                    <th>Title (RU)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($statuses as $status)
                                    <tr>
                                        <td>{{ $status->id }}</td>
                                        <td>{{ implode(', ', $status->status_id ?? []) }}</td>
                                        <td>{{ $status->title_en }}</td>
                                        <td>{{ $status->title_ru }}</td>
                                        <td>
                                            <a href="{{ route('top-delivery-statuses.edit', $status->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                            <form action="{{ route('top-delivery-statuses.destroy', $status->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $statuses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.includes.footer')
