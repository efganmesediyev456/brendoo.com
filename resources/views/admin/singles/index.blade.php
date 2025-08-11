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
                                <h4 class="card-title">Səhifələr</h4>
                                @can('create-singles')
                                    <a href="{{route('singles.create')}}" class="btn btn-primary">+</a>
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
                                        @foreach($singles as $single)

                                            <tr>
                                                <th scope="row">{{$single->id}}</th>
                                                <th scope="row">{{$single->title}}</th>
{{--                                                <td><img src="{{asset('storage/'.$single->image)}}" style="width: 100px; height: 50px" alt=""></td>--}}
                                                <td>
                                            @can('edit-singles')
                                                    <a href="{{route('singles.edit',$single->id)}}" class="btn btn-primary" style="margin-right: 15px" >Edit</a>
                                                    @endcan
{{--                                                    <form action="{{route('singles.destroy', $single->id)}}" method="post" style="display: inline-block">--}}
{{--                                                        {{ method_field('DELETE') }}--}}
{{--                                                        @csrf--}}
{{--                                                        <button onclick="return confirm('Məlumatın silinməyin təsdiqləyin')" type="submit" class="btn btn-danger">Delete</button>--}}
{{--                                                    </form>--}}
                                                </td>
                                            </tr>

                                        @endforeach

                                        </tbody>
                                    </table>
                                    <br>
                                    {{ $singles->links('admin.vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@include('admin.includes.footer')
