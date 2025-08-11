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

                            <h4 class="card-title">Storilər</h4>
                            @can('create-stories')
                                <a href="{{ route('influencers.stories.create',['influencer'=>request()->influencer]) }}" class="btn btn-primary">+</a>
                            @endcan
                            <br><br>
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>№</th>
                                            <th>Başlıq</th>
                                            <th>İnfluencer</th>
                                            <th>Şəkil</th>
                                            <th>Video</th>
                                            <th>Əməliyyat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stories as $story)
                                            <tr>
                                                <th scope="row">{{ $story->id }}</th>
                                                <td>{{ $story->title }}</td>
                                                <td>{{ $story->influencer->name }}</td>
                                                <td>
                                                    @if($story->image)
                                                        <img src="{{ asset('storage/'.$story->image) }}" 
                                                             style="width: 100px; height: 50px" alt="Story Image">
                                                    @else
                                                        Şəkil yoxdur
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($story->video)
                                                        <a href="{{ asset('storage/'.$story->video) }}" 
                                                           target="_blank">Video</a>
                                                    @else
                                                        Video yoxdur
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('edit-stories')
                                                        <a href="{{ route('influencers.stories.edit', $story->id) }}" 
                                                           class="btn btn-primary">Düzəliş</a>
                                                    @endcan
                                                    @can('delete-stories')
                                                        <form action="{{ route('influencers.stories.destroy', $story->id) }}" 
                                                              method="post" style="display: inline-block">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button onclick="return confirm('Məlumatın silinməyini təsdiqləyin')" 
                                                                    type="submit" class="btn btn-danger">Sil</button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <br>
                                {{ $stories->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.includes.footer')
