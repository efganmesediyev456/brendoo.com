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

                            <h4 class="card-title">Tez tez verilən suallar</h4>
                            @can('create-faqs')
                                <a href="{{ route('faqs.create') }}" class="btn btn-primary">+</a>
                            @endcan
                            <br>
                            <br>
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">

                                    <thead>
                                        <tr>
                                            <th>№</th>
                                            <th>Başlıq</th>
                                            <th>Kateqoriya</th>
                                            <th>Status</th>
                                            <th>Əməliyyat</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($faqs as $faq)
                                            <tr>
                                                <th scope="row">{{ $faq->id }}</th>
                                                <th scope="row">{{ $faq->title }}</th>
                                                <th scope="row">{{ $faq->faq_category?->title }}</th>
                                                {{--                                                <td><img src="{{asset('storage/'.$faq->image)}}" style="width: 100px; height: 50px" alt=""></td> --}}
                                                <td>{{ $faq->is_active == true ? 'Active' : 'Deactive' }}</td>
                                                <td>
                                                    @can('edit-faqs')
                                                        <a href="{{ route('faqs.edit', $faq->id) }}" class="btn btn-primary"
                                                            style="margin-right: 15px">Edit</a>
                                                    @endcan
                                                    @can('delete-faqs')
                                                        <form action="{{ route('faqs.destroy', $faq->id) }}" method="post"
                                                            style="display: inline-block">
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
                                {{ $faqs->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>



@include('admin.includes.footer')
