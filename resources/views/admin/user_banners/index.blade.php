@include('admin.includes.header')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            @if(session('message'))
                <div class="alert alert-success">{{session('message')}}</div>
            @endif
            <form action="{{route('user_banners.update', $item->id)}}" method="post" enctype="multipart/form-data">
                {{ method_field('PUT') }}
                @csrf
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Şəkil</h4>
                        <div class="row">
                            <div class="col-6">

                                <div class="mb-3">
                                    <img style="width: 100px; height: 100px;" src="{{asset('storage/' . $item->banner)}}" class="uploaded_image" alt="{{$item->banner}}">
                                    <div class="form-group">
                                        <label >Image (1440x500)</label>
                                        <input type="file" name="banner" class="form-control">
                                    </div>
                                    @if($errors->first('banner')) <small class="form-text text-danger">{{$errors->first('banner')}}</small> @endif
                                </div>

                               

                                @can("edit-user_banners")
                                <div class="mb-3">
                                    <button class="btn btn-primary">Yadda saxla</button>
                                </div>
                                @endcan

                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@include('admin.includes.footer')

