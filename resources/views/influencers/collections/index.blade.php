@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                        
                        
                        <div class="mt-3 mb-3">
                            <h5 class="text-muted">Balans: 
                                <span class="badge bg-info">
                                    {{ number_format($influencer->typeBalancesValue('collection')?->balance, 2) }} ₼
                                </span>
                            </h5>
                        </div>

                            @if(session('message'))
                                <div class="alert alert-success">{{session('message')}}</div>
                            @endif

                            
                            <div class="d-flex justify-content-between align-items-center">
                                
                                <h4 class="card-title">Kolleksiyalar</h4>
                                <a href="{{ route('influencers.index') }}" class="btn btn-primary">Geri Qayıt</a>
                            
                            </div>

                           

                            
                            <!-- @can('create-blogs')
                               <a href="{{route('blogs.create')}}" class="btn btn-primary">+</a>
                               @endcan -->
                            <br>
                            <br>
                            <div class="table-responsive">


                            
                                <table class="table table-centered mb-0 align-middle table-hover table-nowrap">

                                    <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Başlıq</th>
                                        <th>Haqqında</th>
                                        <th>Slug</th>
                                        <th>İnfluser</th>
                                        <th>Məhsul sayı</th>
                                        <th>Status</th>
                                        <th>Əməliyyatlar</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($collections as $collection)

                                        <tr>
                                            <td scope="row">{{$loop->iteration}}</td>
                                            <td scope="row">{{$collection->title}}</td>
                                            <td scope="row">{{$collection->description}}</td>
                                            <td scope="row">{{$collection->slug}}</td>
                                            <td scope="row">{{$collection->influencer?->fullName}}</td>
                                            <td scope="row">{{$collection->products?->count()}}</td>
                                            <td>
                                                @if($collection->status == 1)
                                                    <span class="badge bg-success">Aktiv</span>
                                                @elseif($collection->status == 0)
                                                    <span class="badge bg-danger">Deaktiv</span>
                                                @else
                                                    <span class="badge bg-secondary">Bilinməyən</span>
                                                @endif
                                            </td>
                                            <td> 
                                                <a class="btn btn-sm btn-success" href="{{ route('influencers.collections.edit',['influencer'=>request()->influencer,'collection'=>$collection->id]) }}">
                                                <i class="fas fa-edit"></i>    
                                                Edit</a>

                                                <a class="btn btn-sm btn-warning text-white" href="{{ route('influencers.collections.products.index',['influencer'=>request()->influencer,'collection'=>$collection->id]) }}">
                                                <i class="fas fa-list"></i>    
                                                Məhsullar</a> 

                                              <form action="{{ route('influencers.collections.delete', ['influencer' => request()->influencer, 'collection' => $collection->id]) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Silmək istədiyinə əminsən?')">
                                                @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                              </form>


                                            </td>
                                        </tr>

                                    @endforeach

                                    </tbody>
                                </table>
                                <br>
                                {{ $collections->links('admin.vendor.pagination.bootstrap-5') }}
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



