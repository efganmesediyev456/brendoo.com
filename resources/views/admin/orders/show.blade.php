@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Sifariş Detalları #{{ $order->order_number }}</h3>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Geri
                    </a>
                </div>
            </div>


            <!-- Müştəri Məlumatları -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-info text-white fw-bold">
                            <i class="fas fa-user me-2"></i> Müştəri Məlumatları
                        </div>

                       
                        <div class="card-body">
                            <p><strong>👤 Adı:</strong> {{ $order->customer?->name }}</p>
                            <p><strong>🆔 ID:</strong> {{ $order->customer?->id }}</p>
                            <p><strong>📍 Ünvan:</strong> {{ $order->address ?? 'Ünvan yoxdur' }}</p>
                            <p><strong>📍 Region:</strong> {{ $order->region()->latest()->first()?->regionName}}</p>
                            <p><strong>📍 Şəhər:</strong> {{ $order->city()->latest()->first()?->cityName}}</p>
                            <p><strong>📝 Əlavə Məlumat:</strong> {{ $order->additional_info ?? 'Əlavə məlumat yoxdur' }}</p>
                            @php 
                            $status = new \App\Models\Status;
                            $isFrontCancel = $status->cancelFront();
                            $checkCancel = $order->order_items->filter(function($tt) use($isFrontCancel){
                                return $tt->lastStatus?->status_id == $isFrontCancel->id;
                            });
                            @endphp
                            @if($order->orderCancellation()->exists() and count($checkCancel))
                                <p><strong>📝 Sifariş ləğv etmə səbəbi:</strong> {{ $order->orderCancellation?->first()?->reason ? $order->orderCancellation?->first()->reason?->title : $order->orderCancellation?->first()?->text  }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if(session()->has('error'))
            <div class="alert alert-danger">{{ session()->get('error') }}</div>
            @endif

            @if(session()->has('success'))
            <div class="alert alert-success">{{ session()->get('success') }}</div>
            @endif

            <!-- Sifariş Məhsulları -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-dark text-white fw-bold">
                            <i class="fas fa-boxes me-2"></i> Sifariş Məhsulları
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Kodu</th>
                                        <th>Şəkil</th>
                                        <th>Məhsul</th>
                                        <th>Brend</th>
                                        <th>Miqdar</th>
                                        <th>Seçilib</th>
                                        <th>Status</th>
                                        <th>Admin status</th>
                                        <th>Topdelivery status</th>
                                        <th>Qiymət</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($orderItems as $index => $product)
                                        <tr @if(!$product->order_item_status) style="text-decoration: line-through" @endif>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $product->product?->code }}</td>
                                            <td>
                                                <img src="{{ $product->product?->image }}"
                                                     alt="Məhsul Şəkli"
                                                     class="img-thumbnail"
                                                     style="width: 70px; height: 90px; object-fit: cover;">
                                            </td>
                                            <td>
                                                @if($product->product)
                                                    <a href="{{ route('products.edit', $product->product?->id) }}"
                                                       class="text-primary text-decoration-none">
                                                        {{ $product->product?->title }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Silinmiş məhsul</span>
                                                @endif
                                            </td>
                                            <td>{{ $product->product?->brand?->title }}</td>
                                            <td>{{ $product->quantity }}</td>
                                            <td>
                                                @foreach($product->options as $option)
                                                    <span class="badge bg-secondary me-1">
                                                            {{ $option->filter?->title }}: {{ $option->option?->title }}
                                                        </span>
                                                @endforeach
                                            </td>

                                            <!-- Status dəyişdirmək -->
                                            <td>
                                             <form action="{{ route('orders.updateStatus', $product->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="">Seçin</option>
                                                        @foreach(\App\Models\Status::where('type',1)->get() as $status)
                                                            <option value="{{ $status->id }}" {{ $product->lastStatus?->status_id == $status->id ? 'selected' : '' }}>
                                                                {{ $status->title_ru }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </td>

                                            <!-- Admin Status -->
                                            <td>
                                                <form action="{{ route('admin_orders.updateStatus', $product->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <select name="admin_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="">Seçin</option>
                                                        @foreach(\App\Models\Status::where('type',0)->get() as $status)
                                                            <option value="{{ $status->id }}" {{ $product->admin_status == $status->id ? 'selected' : '' }}>
                                                                {{ $status->title_ru }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </td>

                                            

                                          <td>
                                            @if($product->topdelivery_status)
                                                <span class="badge bg-primary">
                                                    {{ $product->topdelivery_status['status'] }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">Yoxdur</span>
                                            @endif
                                        </td>



                                            

                                            <!-- Price -->
                                            <td>{{ number_format($product->price, 2) }} ₽</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-end bg-light d-flex justify-content-end align-items-center gap-3">
                            <h5 class="mb-0"><strong>Yekun Məbləğ:</strong> {{ number_format($order->final_price, 2) }} ₽</h5>

                            @foreach($order->payments->where('status','APPROVED') as $payment)
                            <form action="{{ route('refund-payment') }}" method="POST"> 
                                <input type="hidden" value="{{ $payment->operation_id }}" name="operationId" >
                                <input type="hidden" value="{{ $payment->amount }}" name="amount" >
                                 @csrf
                                 @method('POST')
                                <button type="submit" class="btn btn-danger">İadə et</button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin.includes.footer')
