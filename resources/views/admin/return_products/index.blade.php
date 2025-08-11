@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">İadələr</h4>
                        </div>
                        <div class="card-body">
                            @if(session('message'))
                                <div class="alert alert-success">{{ session('message') }}</div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered text-center align-middle">
                                    <thead class="table-dark">
                                    <tr>
                                        <th>Müştəri ID</th>
                                        <th>Müştəri</th>
                                        <th>Məhsul</th>
                                        <th>Ad</th>
                                        <th>Email</th>
                                        <th>Adres</th>
                                        <th>Səbəb</th>
                                        <th>Əməliyyat</th>
                                        <th>Front Status</th>
                                        <th>Admin Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($return_products as $return_product)
                                        <tr>
                                            <td class="align-middle">
                                                {{ $return_product->customer?->id ?? 'Müştəri silinib' }}
                                            </td>
                                            <td class="align-middle">
                                                {{ $return_product->customer?->name ?? 'Müştəri silinib' }}
                                            </td>
                                            <td class="align-middle">
                                                @if($return_product->orderItem?->product)
                                                    <a href="{{ route('products.edit', $return_product->orderItem->product->id) }}" class="text-decoration-none">
                                                        <p class="mb-1 fw-bold">{{ $return_product->orderItem->product->code }}</p>
                                                        <img src="{{ $return_product->orderItem->product->image }}"
                                                             class="img-thumbnail"
                                                             style="width: 70px; height: 90px"
                                                             alt="Product Image">
                                                        <p class="mb-0">{{ $return_product->orderItem->product->title }}</p>
                                                    </a>
                                                @else
                                                    <span class="text-danger">Məhsul silinib</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $return_product->name }}
                                            </td>
                                            <td>
                                                {{ $return_product->email }}
                                            </td>
                                            <td>
                                                {{ $return_product->address }}
                                            </td>
                                            <td class="align-middle">
                                                {{ $return_product->notes }}
                                            </td>
                                            <td class="align-middle">
                                                @can('delete-return_products')
                                                <form action="{{ route('return_products.destroy', $return_product->id) }}" method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button onclick="return confirm('Məlumatın silinməyin təsdiqləyin')"
                                                            type="submit" class="btn btn-danger">
                                                        Sil
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                            <td>
                                                <form action="{{ route('orders.updateStatusRedirect', $return_product->orderItem->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="">Seçin</option>
                                                        @foreach(\App\Models\Status::where('type',1)->where('is_return_status',1)->get() as $status)
                                                            <option value="{{ $status->id }}" {{ $return_product->orderItem->lastStatus?->status_id == $status->id ? 'selected' : '' }}>
                                                                {{ $status->title_ru }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </td>

                                             <td>
                                                <form action="{{ route('admin_orders.updateStatus', $return_product->orderItem->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <select name="admin_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="">Seçin</option>
                                                        @foreach(\App\Models\Status::where('type',0)->get() as $status)
                                                            <option value="{{ $status->id }}" {{ $return_product->orderItem->admin_status == $status->id ? 'selected' : '' }}>
                                                                {{ $status->title_ru }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $return_products->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.includes.footer')

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function () {
                let productId = this.dataset.id;
                let newStatus = this.value;

                fetch(`/admin/return-products/${productId}/update-status`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ status: newStatus })
                }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Status dəyişdirildi!");
                        } else {
                            alert("Xəta baş verdi!");
                        }
                    }).catch(error => console.log(error));
            });
        });
    });
</script>
