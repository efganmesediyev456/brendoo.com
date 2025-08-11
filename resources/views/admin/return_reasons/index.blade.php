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
                                
                                <h4 class="card-title">Geri qaytarılma səbəbləri</h4>
                                @can('create-return_reasons')
                                    <a href="{{route('return_reasons.create')}}" class="btn btn-primary">+</a>
                                @endcan
                                <br>
                                <br>
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">

                                        <thead>
                                            <tr>
                                                <th>№</th>
                                                <th>Başlıq</th>
                                                <th>Əməliyyat</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                        @foreach($items as $item)

                                            <tr>
                                                <th scope="row">{{$item->id}}</th>
                                                <th scope="row">{{$item->title}}</th>
                                                <td>
                                                    @can('edit-return_reasons')
                                                    <a href="{{route('return_reasons.edit',$item->id)}}" class="btn btn-primary" style="margin-right: 15px" >Edit</a>
                                                    @endcan
                                                <form action="{{route('return_reasons.destroy', $item->id)}}" method="post" style="display: inline-block">
                                                                {{ method_field('DELETE') }} 
                                                            @csrf
                                                    <button onclick="return confirm('Məlumatın silinməyin təsdiqləyin')" type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                                </td>
                                            </tr>

                                        @endforeach

                                        </tbody>
                                    </table>
                                    <br>
                                    {{ $items->links('admin.vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>



@include('admin.includes.footer')
