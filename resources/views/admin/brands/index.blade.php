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
                            <h4 class="card-title">Brendlər</h4>
                            @can('create-brands')
                               <a href="{{route('brands.create')}}" class="btn btn-primary">+</a>
                               @endcan
                            <br>
                            <br>
                            <div class="table-responsive">
                                <table class="table table-centered mb-0 align-middle table-hover table-nowrap">

                                    <thead>
                                        <tr>
                                            <th>№</th>
                                            <th>Başlıq</th>
                                            <th>Əməliyyat</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($brands as $brand)

                                        <tr>
                                            <th scope="row">{{$brand->id}}</th>
                                            <th scope="row">{{$brand->title}}</th>
                                            {{--                                                <td><img src="{{asset('storage/'.$brand->image)}}" style="width: 100px; height: 50px" alt=""></td>--}}
                                            <td>
                                                @can('edit-brands')
                                                <a href="{{route('brands.edit',$brand->id)}}" class="btn btn-primary"
                                                   style="margin-right: 15px">Edit</a>
                                                   @endcan
                                                @can('delete-brands')

                                                <form action="{{route('brands.destroy', $brand->id)}}" method="post" style="display: inline-block">
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
                                {{ $brands->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('admin.includes.footer')
