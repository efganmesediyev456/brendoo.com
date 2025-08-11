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
                            <h4 class="card-title">Şəhərlər</h4>
                            @can('create-cities')
                            <a href="{{route('cities.create')}}" class="btn btn-primary">+</a>
                            <br>
                            <br>
                            @endcan
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">

                                    <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Başlıq</th>
                                        <th>Id</th>
                                        <th>Region</th>
                                        <th>Əməliyyat</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($cities as $city)

                                        <tr>
                                            <th scope="row">{{$city->id}}</th>
                                            <th scope="row">{{$city->cityName}}</th>
                                            <th scope="row">{{$city->cityId}}</th>
                                            <th scope="row">{{$city->region?->regionName}}</th>
                                            {{--                                                <td><img src="{{asset('storage/'.$city->image)}}" style="width: 100px; height: 50px" alt=""></td>--}}
                                            <td>
                                                                            @can('edit-cities')

                                                <a href="{{route('cities.edit', $city->id)}}" class="btn btn-primary"
                                                   style="margin-right: 15px">Edit</a>
                                                   @endcan
                                                     @can('delete-cities')
                                                <form action="{{route('cities.destroy', $city->id)}}" method="post"
                                                      style="display: inline-block">
                                                    {{ method_field('DELETE') }}
                                                    @csrf
                                                    <button onclick="return confirm('Məlumatın silinməyin təsdiqləyin')"
                                                            type="submit" class="btn btn-danger">Delete
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>

                                    @endforeach
                                    </tbody>
                                </table>
                                <br>
                                {{ $cities->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


@include('admin.includes.footer')
