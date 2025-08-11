@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <form action="{{ route('influencers.collections.update', ['influencer'=>request()->influencer,'collection'=>$collection->id]) }}" method="post" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Kolleksiya Redaktə Et</h4>
                        @if(session()->has("success"))
                            <p class="alert alert-success">{{ session()->get("success") }}</p>
                        @endif
                        <div class="row">
                            <div class="col-6">
                                <!-- Başlıq və Mətn -->
                                <div class="mb-3">
                                    <label class="col-form-label">Başlıq</label>
                                    <input class="form-control" type="text" name="title" value="{{ $collection->title }}">
                                    @if($errors->first('title'))
                                        <small class="form-text text-danger">{{ $errors->first('title') }}</small>
                                    @endif
                                </div>


                                <div class="mb-3">
                                    <label class="col-form-label">Haqqında</label>
                                    <textarea class="form-control" type="text" name="description">{{ $collection->description }}</textarea>

                                    @if($errors->first('description'))
                                        <small class="form-text text-danger">{{ $errors->first('description') }}</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Qazanc faizi</label>
                                    <input class="form-control" type="text" name="earn_price" value="{{ $collection->earn_price }}">
                                    @if($errors->first('earn_price'))
                                        <small class="form-text text-danger">{{ $errors->first('earn_price') }}</small>
                                    @endif
                                </div>


                                <div class="mb-3">
                                    <label class="col-form-label">Status</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_active" value="1" {{ $collection->status == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_active">
                                            Aktiv
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status" id="status_deactive" value="0" {{ $collection->status == 0 ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_deactive">
                                            Deaktiv
                                        </label>
                                    </div>

                                    @if($errors->first('status'))
                                        <small class="form-text text-danger">{{ $errors->first('status') }}</small>
                                    @endif
                                </div>

                                 

                                
                                <div class="mb-3">
                                    <button class="btn btn-primary" type="submit">Yadda saxla</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.includes.footer')
