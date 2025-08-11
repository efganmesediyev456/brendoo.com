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
                            <h4 class="card-title">Statuslar</h4>
                            @can('create-statuses')
                               <a href="{{route('statuses.create')}}" class="btn btn-primary">+</a>
                            @endcan
                            <br>
                            <br>
                            <div class="table-responsive">
                                <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                    <thead>
                                        <tr>
                                            <th>№</th>
                                            <th>Başlıq</th>
                                            <th>Tip</th>
                                            <th>Related Status(Status dəyişən zaman avtomatik buna keçir)</th>
                                            <th>Əməliyyat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($statuses as $status)
                                        <tr>
                                            <th scope="row">{{$status->id}}</th>
                                            <th scope="row">{{$status->title_ru}}</th>
                                            <td>{{ $status->type==0 ? 'Admin':($status->type==1 ? 'Front':($status->type==2 ? 'Topdelivery': '')) }}</td>
                                            {{--                                                <td><img src="{{asset('storage/'.$status->image)}}" style="width: 100px; height: 50px" alt=""></td>--}}
                                            <td>
                                            {{ $status->related?->title_ru }}
                                            </td>
                                            <td>
                                                @can('edit-statuses')
                                                <a href="{{route('statuses.edit',$status->id)}}" class="btn btn-primary"
                                                   style="margin-right: 15px">Edit</a>
                                                   @endcan
                                                @can('delete-statuses')
                                                @if($status->deleteable)
                                                <form action="{{route('statuses.destroy', $status->id)}}" method="post" style="display: inline-block">
                                                    {{ method_field('DELETE') }}
                                                    @csrf
                                                    <button onclick="return confirm('Məlumatın silinməyin təsdiqləyin')" type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                                @endif
                                                @endcan
                                            </td>
                                        </tr>

                                    @endforeach

                                    </tbody>
                                </table>
                                <br>
                                {{ $statuses->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('admin.includes.footer')
