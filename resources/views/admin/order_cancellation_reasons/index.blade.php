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
                            <h4 class="card-title">Ləğv etmə səbəbi</h4>
                            @can('create-order_cancellation_reasons')
                               <a href="{{route('order_cancellation_reasons.create')}}" class="btn btn-primary">+</a>
                               @endcan
                            <br>
                            <br>
                            <div class="table-responsive">
                                @if(session()->has('success'))
                                <p class="alert alert-success">{{ session()->get('success') }}</p>
                                @endif
                                <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                    <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Başlıq</th>
                                        <th>Əməliyyat</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($cancels as $blog)

                                        <tr>
                                            <th scope="row">{{$blog->id}}</th>
                                            <th scope="row">{{$blog->title}}</th>
                                            {{--                                                <td><img src="{{asset('storage/'.$blog->image)}}" style="width: 100px; height: 50px" alt=""></td>--}}
                                            <td>
                                     @can('edit-order_cancellation_reasons')

                                                <a href="{{route('order_cancellation_reasons.edit',$blog->id)}}" class="btn btn-primary"
                                                   style="margin-right: 15px">Edit</a>
                                                   @endcan
                                                   @can('delete-order_cancellation_reasons')
                                                <form action="{{route('order_cancellation_reasons.destroy', $blog->id)}}" method="post" style="display: inline-block">
                                                    {{ method_field('DELETE') }}
                                                    @csrf
                                                    <button onclick="return confirm('Məlumatın silinməyin təsdiqləyin')" type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>

                                    @endforeach

                                    </tbody>
                                </table>
                                <br>
                                {{ $cancels->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('admin.includes.footer')
