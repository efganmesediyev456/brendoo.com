@include('admin.includes.header')

<style>
    .story-container{
        display: grid;
        grid-template-columns: repeat(3,1fr);
        width: 100%;
    }
    .story-container img,video{
        width: 100%; 
        height: 300px;
    }
</style>
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <form action="{{ route('influencers.stories.update', $story->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Story Düzəliş Et</h4>
                        <div class="row">
                            <!-- salam -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="col-form-label">İnfluencer</label>
                                    <select name="influencer_id" class="form-control">
                                        @foreach($influencers as $influencer)
                                            <option value="{{ $influencer->id }}" 
                                                {{ $story->influencer_id == $influencer->id ? 'selected' : '' }}>
                                                {{ $influencer->name }} ({{ $influencer->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Başlıq (İngilis)</label>
                                    <input class="form-control" type="text" name="en_title" 
                                           value="{{ $story->translate('en')->title }}">
                                    @error('en_title')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Başlıq (Rus)</label>
                                    <input class="form-control" type="text" name="ru_title" 
                                           value="{{ $story->translate('ru')->title }}">
                                    @error('ru_title')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Açıqlama (İngilis)</label>
                                    <textarea class="form-control" name="en_description">{{ $story->translate('en')->description }}</textarea>
                                    @error('en_description')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Açıqlama (Rus)</label>
                                    <textarea class="form-control" name="ru_description">{{ $story->translate('ru')->description }}</textarea>
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

                                

                                <div class="story-container">
                                    @foreach($story->images as $image)
                                    <div class="image-container position-relative d-inline-block m-2">
                                        <img src="{{ $image->file_url }}" style=" object-fit: cover;">
                                        <button type="button" 
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 delete-media" 
                                                data-media-id="{{ $image->id }}"
                                                data-type="image">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach

                                <!-- test -->

                                @foreach($story->videos as $video)
                                    <div class="video-container position-relative d-inline-block m-2">
                                        <video  controls>
                                            <source src="{{ $video->file_url }}" type="{{ $video->mime_type }}">
                                        </video>
                                        <button type="button" 
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 delete-media" 
                                                data-media-id="{{ $video->id }}"
                                                data-type="video">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                                </div>

<!-- Media Silmə Modalı -->
<div class="modal fade" id="deleteMediaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Media Silinməsi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bu media elementini silmək istədiyinizə əminsinizmi?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bağla</button>
                <button type="button" class="btn btn-danger" id="confirmMediaDelete">Sil</button>
            </div>
        </div>
    </div>
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




<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentMediaId = null;
    let currentMediaType = null;
    
    document.querySelectorAll('.delete-media').forEach(button => {
        button.addEventListener('click', function() {
            currentMediaId = this.getAttribute('data-media-id');
            currentMediaType = this.getAttribute('data-type');
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteMediaModal'));
            deleteModal.show();
            // slaam
        });
    });

    document.getElementById('confirmMediaDelete').addEventListener('click', function() {
        if (currentMediaId && currentMediaType) {
            fetch(`/media/${currentMediaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const elementToRemove = document.querySelector(
                        `.${currentMediaType}-container [data-media-id="${currentMediaId}"]`
                    ).closest(`.${currentMediaType}-container`);
                    
                    if (elementToRemove) {
                        elementToRemove.remove();
                    }

                    var deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteMediaModal'));
                    deleteModal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Uğurlu Əməliyyat',
                        text: 'Media elementi uğurla silindi'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Xəta',
                    text: 'Media elementini silmək mümkün olmadı'
                });
            });
        }
    });
});
</script>
