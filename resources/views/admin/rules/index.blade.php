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
                                @can('create-rules')
                                    <a href="{{route('rules.create')}}" class="btn btn-primary">+</a>
                                    @endcan
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
                                        @foreach($rules as $rule)

                                            <tr>
                                                <th scope="row">{{$rule->id}}</th>
                                                <th scope="row">{{$rule->title}}</th>
                                                <td>{{$rule->is_active ? 'Active' : 'Deactive'}}</td>
                                                <td>
                                                @can('edit-rules')
                                                    <a href="{{route('rules.edit',$rule->id)}}" class="btn btn-primary" style="margin-right: 15px" >Edit</a>
                                                    @endcan
                                                     @can('delete-rules')
                                                    <form action="{{route('rules.destroy', $rule->id)}}" method="post" style="display: inline-block">
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
                                    {{ $rules->links('admin.vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



@include('admin.includes.footer')
