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
                            <h4 class="card-title">Kateqoriyalar</h4>
                            @can('create-categories')
                            <a href="{{route('categories.create')}}" class="btn btn-primary">+</a>
                            <br>
                            <br>
                            @endcan
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

                                    @foreach($categories as $key => $category)

                                        <tr>
                                            <th scope="row">{{$key+1}}</th>
                                            <th scope="row">{{$category->title}}</th>
                                            {{--<td><img src="{{asset('storage/'.$category->image)}}" style="width: 100px; height: 50px" alt=""></td>--}}
                                            <td>
                                                @can('edit-categories')
                                                <a href="{{route('categories.edit',$category->id)}}"
                                                   class="btn btn-primary"
                                                   style="margin-right: 15px">Edit</a>
                                                   @endcan
                                                   @can('delete-categories')
                                                <form action="{{route('categories.destroy', $category->id)}}"
                                                      method="post" style="display: inline-block">
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
                                {{ $categories->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('admin.includes.footer')
