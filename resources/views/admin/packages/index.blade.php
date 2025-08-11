@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Filtrlər -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        @if(session('message'))
                            <div class="alert alert-success">{{ session('message') }}</div>
                        @endif
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Paketlər</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('packages.index') }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Müştəri id</label>
                                        <input type="text" name="customer_id" class="form-control"
                                               value="{{ request('customer_id') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Barkod</label>
                                        <input type="text" name="barcode" class="form-control"
                                               value="{{ request('barcode') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">TR Barkod</label>
                                        <input type="text" name="tr_barcode" class="form-control"
                                               value="{{ request('tr_barcode') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Başlanğıc tarix</label>
                                        <input type="date" name="start_date" class="form-control"
                                               value="{{ request('start_date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Bitmə tarixi</label>
                                        <input type="date" name="end_date" class="form-control"
                                               value="{{ request('end_date') }}">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Filtrlə</button>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <a href="{{ route('packages.index') }}"
                                           class="btn btn-secondary w-100">Sıfırla</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paketlər cədvəli -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Paketlər</h5>
                        </div>
                        <div class="card-body">
                            @if(session('message'))
                                <div class="alert alert-success">{{ session('message') }}</div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle table-hover table-striped">
                                    <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Barkod</th>
                                        <th>Çəki (kq)</th> 
                                        <th>Qeyd</th>
                                        <th>Sifariş sayı</th>
                                        <th>Məhsullar</th>
                                        <th>Waybill</th>
                                        <th>Yaradılma</th>
                                        <th>Topdelivery</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($packages as $package)
                                        <tr>

                                            <td>{{ $package->id }}</td>
                                            <td>{{ $package->barcode }}</td>
                                            <td>{{ $package->weight }} kq</td>
                                            {{-- Əgər kq istəsən: number_format($package->weight / 1000, 2) . ' kq' --}}
                                            <td>{{ $package->note }}</td>
                                            <td>
                                                {{ $package->order_items_count ?? $package->orderItems()->count() }}
                                            </td>
                                            <td>
                                                @foreach($package->orderItems as $orderItem)
                                                    <span class="badge bg-light text-dark">
                                                        M.id :{{$orderItem->customer?->id}}
                                                        {{ $orderItem->product?->title ?? 'Məhsul tapılmadı' }}
                                                    </span><br>
                                                @endforeach
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    @if($package->topdelivery_waybill_path)
                                                        <a style="width: max-content;" href="{{ $package->topdelivery_waybill_path }}"
                                                        target="_blank" class="d-block btn btn-sm btn-secondary">PDF Bax</a>
                                                        <button
                                                            onclick="window.open('{{ $package->topdelivery_waybill_path }}', '_blank').print()"
                                                            class="btn btn-sm btn-info">Print
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $package->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <form class="packageForm" action="{{route('check.status.topdelivery')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="top_delivery_order_id" value="{{$package->top_delivery_order_id}}">
                                                    <input type="hidden" name="barcode" value="{{$package->barcode}}">
                                                    <input type="hidden" name="webshop_number" value="{{$package->webshop_number}}">
                                                    <input type="hidden" name="package_id" value="{{$package->id}}">
                                                    {{$package->top_delivery_order_id}}<br>
                                                    {{$package->barcode}}<br>
                                                    {{$package->webshop_number}}<br>
                                                    <button class="btn btn-success btn-sm" type="submit">Yoxla</button>
                                                </form>
                                            </td>

                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
{{ $packages->withQueryString()->links('pagination::bootstrap-5') }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>



<div class="modal fade" id="packageDetailModal" tabindex="-1" aria-labelledby="packageDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Paket Məlumatları</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bağla"></button>
      </div>
      <div class="modal-body">
        <p><strong>Status:</strong> <span id="package-status"></span></p>
        <p><strong>Barkod:</strong> <span id="package-barcode"></span></p>
        <p><strong>Çəki:</strong> <span id="package-weight"></span> kg</p>
        <p><strong>Qeyd:</strong> <span id="package-note"></span></p>
        <p><strong>Sifariş sayı:</strong> <span id="package-count"></span></p>
        <p><strong>Sifarişlər:</strong> <span id="package-orders"></span></p>
        <p><strong>Təslim olunma tarixi:</strong> <span id="package-deliveryDate"></span></p>
        <p><strong>Təslim olunma tarixi Başlama saat:</strong> <span id="package-deliveryBeginHour"></span></p>
        <p><strong>Təslim olunma tarixi Bitmə saat:</strong> <span id="package-deliveryFinalHour"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bağla</button>
      </div>
    </div>
  </div>
</div>



@include('admin.includes.footer')

<!-- Select all checkbox JS -->
<script>
    $(function(){
        $(".packageForm").on("submit", function(e){
            e.preventDefault();
            let formData = new FormData(this)
            formData.append('_token','{{ csrf_token()}}')
            $.ajax({
                url:$(this).attr('action'),
                type:$(this).attr("method"),
                data:formData,
                processData: false,
                contentType: false,
                success:function(res){
                    $("#package-status").text(res.status ?? '—');
                    $("#package-barcode").text(res.barcode ?? '—');
                    $("#package-weight").text(res.weight ?? '—');
                    $("#package-note").text(res.note ?? '—');
                    $("#package-count").text(res.order_items_count ?? '0');
                    $("#package-orders").text(res.orders ?? '-');
                    $("#package-deliveryDate").text(res.deliveryDate ?? '-');
                    $("#package-deliveryBeginHour").text(res.fromDeliveryInterval ?? '-');
                    $("#package-deliveryFinalHour").text(res.toDeliveryInterval ?? '-');

                    
                    
                    const modal = new bootstrap.Modal(document.getElementById('packageDetailModal'));
                    modal.show();
                },
                error:function(e){
                    console.log(e);
                }
            })
        })
    })
    document.getElementById('checkAllPackages').addEventListener('change', function () {
        const checkboxes = document.querySelectorAll('table .package-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    
</script>
