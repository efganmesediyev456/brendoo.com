@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if(session('message'))
                                <div class="alert alert-success">{{session('message')}}</div>
                            @endif
                            <h4 class="card-title">Influserlər</h4>
                            <!-- @can('create-blogs')
                               <a href="{{route('blogs.create')}}" class="btn btn-primary">+</a>
                               @endcan -->
                            <br>
                            <br>
                            <div class="table-responsive">


                            <form action="{{ route('influencers.index') }}">

                                

                            </form>

                                <table class="table table-centered mb-0 align-middle table-hover table-nowrap">

                                    <thead>
                                        <tr>
                                            <th>№</th>
                                            <th>Ad</th>
                                            <th>Telefon</th>
                                            <th>Email</th>
                                            <th>Sosial profil linki</th>
                                            @can('changeStatus-influencers')
                                            <th>Status</th>
                                            @endcan
                                            <th>Təsdiqlə</th>
                                            <th>Ətraflı</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($influencers as $influencer)
                                        <tr>
                                            <th scope="row">{{$influencer->id}}</th>
                                            <th scope="row">
                                                <!-- <a href="{{ route('influencers.collections.index',['influencer'=>$influencer->id]) }}"> -->
                                                    {{$influencer->name}}
                                                <!-- </a> -->
                                            </th>
                                            <th scope="row">{{$influencer->phone}}</th>
                                            <th scope="row">{{$influencer->email}}</th>
                                            <th scope="row">{{$influencer->social_profile}}</th>
                                            <td>{{$influencer->status == 'rejected' ? 'Rədd edildi' : ($influencer->status == 'accepted' ? 'Təsdiqlənib':($influencer->status == 'pending' ? 'Gözləmədə':''))}}</td>
                                            <td>
                                            @can('changeStatus-influencers')
                                            <div class="form-check form-switch">
                                                <input @checked($influencer->status == 'accepted') data-id="{{ $influencer->id }}" class="form-check-input switchCheckbox" type="checkbox">
                                            </div>
                                            @endcan
                                            </td>
                                            <td>
                                                <a href="{{ route('influencers.collections.index',['influencer'=>$influencer->id]) }}" class="btn btn-success btn-sm">
                                                Kolleksiyalar [{{ $influencer->collections()->count() }}]
                                                </a>
                                                <a href="{{ route('coupons.index',['influencer'=>$influencer->id]) }}" class="btn btn-success btn-sm">
                                                Promokodlar [{{ $influencer->coupons()->count() }}] 
                                                </a>

                                                <a href="{{ route('influencers.stories.index',['influencer'=>$influencer->id]) }}" class="btn btn-success btn-sm">
                                                Storiyalar [{{ $influencer->stories()->count() }}] 
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
                                <br>
                                {{ $influencers->links('admin.vendor.pagination.bootstrap-5') }}
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



