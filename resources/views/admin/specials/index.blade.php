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

                                <h4 class="card-title">Endirim banneri</h4>
{{--                                    <a href="{{route('specials.create')}}" class="btn btn-primary">+</a>--}}
                                <br>
                                <br>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">

                                        <thead>
                                            <tr>
                                                <th>№</th>
                                                <th>Başlıq</th>
                                                <th>Status</th>
                                                <th>Əməliyyat</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($specials as $special)

                                            <tr>
                                                <th scope="row">{{$special->id}}</th>
                                                <th scope="row">{{$special->discount}}</th>
{{--                                                <td><img src="{{asset('storage/'.$special->image)}}" style="width: 100px; height: 50px" alt=""></td>--}}
                                                <td>{{$special->is_active  ? 'Active' : 'Deactive'}}</td>
                                                <td>
                                                    @can('edit-specials')
                                                    <a href="{{route('specials.edit',$special->id)}}" class="btn btn-primary" style="margin-right: 15px" >Edit</a>
                                                    @endcan
{{--                                                    <form action="{{route('specials.destroy', $special->id)}}" method="post" style="display: inline-block">--}}
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
                                    {{ $specials->links('admin.vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



@include('admin.includes.footer')
