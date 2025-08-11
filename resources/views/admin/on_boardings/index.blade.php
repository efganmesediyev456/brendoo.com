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
                                <h4 class="card-title">On boarding</h4>
                                @can('create-on_boardings')
                                    <a href="{{route('on_boardings.create')}}" class="btn btn-primary">+</a>
                                @endcan
                                <br>
                                <br>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">

                                        <thead>
                                            <tr>
                                                <th>№</th>
                                                <th>Basliq</th>
                                                <th>Status</th>
                                                <th>Əməliyyat</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($on_boardings as $on_boarding)

                                            <tr>
                                                <th scope="row">{{$on_boarding->id}}</th>
                                                <th scope="row">{{$on_boarding->title}}</th>
                                                <td>{{$on_boarding->is_active ? 'Active' : 'Deactive'}}</td>
                                                <td>
                                                @can('edit-on_boardings')

                                                    <a href="{{route('on_boardings.edit',$on_boarding->id)}}" class="btn btn-primary" style="margin-right: 15px" >Edit</a>
                                                @endcan
                                                @can('delete-on_boardings')
                                                    <form action="{{route('on_boardings.destroy', $on_boarding->id)}}" method="post" style="display: inline-block">
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
                                    {{ $on_boardings->links('admin.vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



@include('admin.includes.footer')
