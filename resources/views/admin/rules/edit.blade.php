@include('admin.includes.header')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            @if(session('message'))
                <div class="alert alert-success">{{session('message')}}</div>
            @endif
            <form action="{{route('rules.update', $rule->id)}}" method="post" enctype="multipart/form-data">
                {{ method_field('PUT') }}
                @csrf
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{$rule->translate('en')->title}}</h4>
                        <div class="row">
                            <div class="col-6">

                                <div class="mb-3">
                                    <label class="col-form-label">Başlıq en</label>
                                    <input class="form-control" type="text" name="en_title" value="{{$rule->translate('en')->title}}">
                                    @if($errors->first('en_title')) <small class="form-text text-danger">{{$errors->first('en_title')}}</small> @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Başlıq ru</label>
                                    <input class="form-control" type="text" name="ru_title" value="{{$rule->translate('ru')->title}}">

                                    @if($errors->first('ru_title')) <small class="form-text text-danger">{{$errors->first('ru_title')}}</small> @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Text en</label>
                                    <textarea id="editor_en" class="form-control" type="text" name="en_description">{{$rule->translate('en')->description}}</textarea>
                                    @if($errors->first('en_description')) <small class="form-text text-danger">{{$errors->first('en_description')}}</small> @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Text ru</label>
                                    <textarea id="editor_ru" class="form-control" type="text" name="ru_description">{{$rule->translate('ru')->description}}</textarea>
                                    @if($errors->first('ru_description')) <small class="form-text text-danger">{{$errors->first('ru_description')}}</small> @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Image1</label>
                                    <input class="form-control" name="image1" type="file"/>
                                    @if($errors->first('image1')) <small class="form-text text-danger">{{$errors->first('image1')}}</small> @endif
                                    @if($rule->image1)
                                    <img width="200" src="/storage/{{ $rule->image1 }}" alt="">
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Image2</label>
                                    <input class="form-control" name="image2" type="file"/>
                                    @if($errors->first('image2')) <small class="form-text text-danger">{{$errors->first('image2')}}</small> @endif
                                    @if($rule->image2)
                                    <img width="200"  src="/storage/{{ $rule->image2 }}" alt="">
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Active</label>
                                    <select name="is_active" id="" class="form-control">
                                        <option value="1" {{$rule->is_active ? 'selected' : ''}}>Active</option>
                                        <option value="0" {{!$rule->is_active ? 'selected' : ''}}>Deactive</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <button class="btn btn-primary">Yadda saxla</button>
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

