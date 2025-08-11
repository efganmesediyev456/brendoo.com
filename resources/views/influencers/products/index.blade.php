@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if(session('success'))
                                <div class="alert alert-success">{{session('success')}}</div>
                            @endif

                            
                            <div class="d-flex justify-content-between align-items-center">
                                
                                <h4 class="card-title">Məhsullar</h4> 
                                <a href="{{ route('influencers.collections.index',['influencer'=>request()->influencer]) }}" class="btn btn-primary">Geri Qayıt</a>
                            
                            </div>

                           

                            
                            <!-- @can('create-blogs')
                               <a href="{{route('blogs.create')}}" class="btn btn-primary">+</a>
                               @endcan -->
                            <br>
                            <br>
                            <div class="table-responsive">


                            
                                <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                               
                                <th width="100">Kod</th>
                                <th width="80">Şəkil</th>
                                <th>Başlıq</th>
                                <th width="150">Brend</th>
                                <th width="150">Kateqoriya</th>
                                <th width="120">Qiymət</th>
                                <th width="100">Status</th>
                                <th width="120">Əməliyyat</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $key => $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                  
                                    <td><span class="badge bg-secondary">{{ $product->code }}</span></td>
                                    <td>
                                        <img width="200" src="{{ $product->image }}" class="product-img" alt="{{ $product->title }}">
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;" title="{{ $product->title }}">
                                            {{ $product->title }}
                                        </div>
                                    </td>
                                    <td>{{ $product->brand?->title ?? '-' }}</td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 150px;">
                                            {{ $product->category?->title }}
                                            @if($product->sub_category)
                                                <small class="text-muted d-block">{{ $product->sub_category?->title }}</small>
                                            @endif
                                            @if($product->third_category)
                                                <small class="text-muted d-block">{{ $product->third_category?->title }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="{{ $product->discount > 0 ? 'text-decoration-line-through text-muted' : 'price-highlight' }}">
                                                {{ number_format($product->price, 2) }} ₽
                                            </span>
                                            @if($product->discount > 0)
                                                <br>
                                                <span class="price-highlight">
                                                    {{ number_format($product->discounted_price, 2) }} ₽
                                                </span>
                                                <span class="badge bg-danger ms-1">-{{ $product->discount }} %</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill status-badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                            {{ $product->is_active ? 'Aktiv' : 'Deaktiv' }}
                                        </span>
                                    </td>

                                    <td>
                                       <div class="  d-flex gap-2 align-items-center justify-content-center">
                                        
                                       <a href="{{  route('influencers.collections.products.earnings.index', ['influencer'=>request()->influencer,'collection'=>request()->collection,'product'=>$product->id]) }}" class="w-100 btn btn-success btn-sm">Balansa bax <i class="fas fa-eye"></i></a>

                                       <form action="{{ route('influencers.collections.products.delete', ['influencer' => request()->influencer, 'collection' => request()->collection, 'product' => $product->id]) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Bu məhsulu kolleksiyadan silmək istədiyinə əminsən?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Kolleksiyadan sil!">
                                                    <i class="fas fa-trash"></i> Sil
                                                </button>
                                            </form>
                                       </div>
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                                <br>
                                {{ $products->links('admin.vendor.pagination.bootstrap-5') }}
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
    $(function(){
       $(".switchCheckbox").on("change", function(e){
            var el = $(this);
            var value = "pending";
            if(el.is(":checked")){
                value="accepted";
            }else{
                value="rejected";
            }
            var id = $(this).attr('data-id');
            $.ajax({
                url:"{{ route('influencers.changeStatus') }}",
                data:{
                    id,
                    _token:'{{ csrf_token() }}',
                    "status":value
                },
                success:function(e){
                    toastr.success(e.message)
                    setTimeout(function(){
                        window.location.reload()
                    },2000);
                },
                error:function(e){
                    toastr.error(e.responseJSON.message)
                }


            })
       })
    })
</script>



