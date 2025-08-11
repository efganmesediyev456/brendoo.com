@include('admin.includes.header')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <form action="{{ route('influencers.stories.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Story Əlavə Et</h4>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="col-form-label">İnfluencer</label>
                                    <select name="influencer_id" class="form-control">
                                        @foreach($influencers as $influencer)
                                            <option @selected(request()->influencer==$influencer->id) value="{{ $influencer->id }}">{{ $influencer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Başlıq (İngilis)</label>
                                    <input class="form-control" type="text" name="en_title" 
                                           value="{{ old('en_title') }}">
                                    @error('en_title')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Başlıq (Rus)</label>
                                    <input class="form-control" type="text" name="ru_title" 
                                           value="{{ old('ru_title') }}">
                                    @error('ru_title')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Açıqlama (İngilis)</label>
                                    <textarea class="form-control" name="en_description">{{ old('en_description') }}</textarea>
                                    @error('en_description')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Açıqlama (Rus)</label>
                                    <textarea class="form-control" name="ru_description">{{ old('ru_description') }}</textarea>
                                    @error('ru_description')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                
                                <div class="mb-3">
                                    <label class="col-form-label">Şəkillər</label>
                                    <input class="form-control" type="file" name="images[]" multiple accept="image/*">
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Videolar</label>
                                    <input class="form-control" type="file" name="videos[]" multiple accept="video/*">
                                </div>

                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary">Yadda Saxla</button>
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
