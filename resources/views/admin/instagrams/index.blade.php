@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if (session('message'))
                                <div class="alert alert-success">{{ session('message') }}</div>
                            @endif
                            <h4 class="card-title">Instagram hekayələri</h4>
                            @can('create-instagrams')
                                <a href="{{ route('instagrams.create') }}" class="btn btn-primary">+</a>
                            @endcan
                            <br>
                            <br>
                            <div class="table-responsive">
                                <table class="table table-centered mb-0 align-middle table-hover table-nowrap">

                                    <thead>
                                        <tr>
                                            <th>№</th>
                                            <th>Başlıq</th>
                                            <th>Məhsullar</th>
                                            <th>Əməliyyat</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach ($instagrams as $instagram)
                                            <tr>
                                                <th scope="row">{{ $instagram->id }}</th>
                                                <th scope="row">{{ $instagram->title }}</th>
                                                <th scope="row">
                                                    @can('create-instagrams')
                                                    <a class="btn btn-info"
                                                        href="{{ route('instagrams.show', $instagram->id) }}">Əlavə
                                                        et</a>
                                                    @endcan
                                                </th>

                                                {{--                                                <td><img src="{{asset('storage/'.$instagram->image)}}" style="width: 100px; height: 50px" alt=""></td> --}}
                                                <td>
                                                    @can('edit-instagrams')
                                                        <a href="{{ route('instagrams.edit', $instagram->id) }}"
                                                            class="btn btn-primary" style="margin-right: 15px">Edit</a>
                                                    @endcan
                                                    @can('delete-instagrams')
                                                        <form action="{{ route('instagrams.destroy', $instagram->id) }}"
                                                            method="post" style="display: inline-block">
                                                            {{ method_field('DELETE') }}
                                                            @csrf
                                                            <button
                                                                onclick="return confirm('Məlumatın silinməyin təsdiqləyin')"
                                                                type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                                <br>
                                {{ $instagrams->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@include('admin.includes.footer')
