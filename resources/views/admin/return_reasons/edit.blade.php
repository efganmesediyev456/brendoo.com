@include('admin.includes.header')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <form action="{{ route('return_reasons.update',['return_reason'=> $item->id]) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method("PUT")

                @if(session('message'))
                    <div class="alert alert-success">{{session('message')}}</div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">əlavə et</h4>
                        <div class="row">
                            <div class="col-6">  
                                <!-- Başlıq və Mətn -->
                                <div class="mb-3"> 
                                    <label class="col-form-label">Başlıq en</label>
                                    <input class="form-control" type="text" name="en_title" value="{{ $item->translate('en')?->title }}">
                                    @if($errors->first('en_title'))
                                        <small class="form-text text-danger">{{ $errors->first('en_title') }}</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Başlıq ru</label>
                                    <input class="form-control" type="text" name="ru_title" value="{{ $item->translate('ru')?->title }}">
                                    @if($errors->first('ru_title'))
                                        <small class="form-text text-danger">{{ $errors->first('ru_title') }}</small>
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
