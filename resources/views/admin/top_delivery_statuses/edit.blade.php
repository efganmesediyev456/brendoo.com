@include('admin.includes.header')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('top-delivery-statuses.update', $topDeliveryStatus->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit Top Delivery Status</h4>
                        
                       
                        <div class="mb-3">
                            <label class="col-form-label">Status IDs (Multiple)</label>
                            <select name="status_id[]" class="form-control" multiple>
                                @for ($i = 0; $i <= 22; $i++)
                                    <option value="{{ $i }}" {{ in_array($i, $topDeliveryStatus->status_id ?? []) ? 'selected' : '' }}>
                                        Status {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>


                        <div class="mb-3">
                            <label class="col-form-label">Title (English)</label>
                            <input type="text" name="title_en" class="form-control" value="{{ $topDeliveryStatus->title_en }}">
                        </div>

                        <div class="mb-3">
                            <label class="col-form-label">Title (Russian)</label>
                            <input type="text" name="title_ru" class="form-control" value="{{ $topDeliveryStatus->title_ru }}">
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@include('admin.includes.footer')

<script>
    $(function(){
        $("select").select2()
    })
</script>