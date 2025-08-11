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

            <form action="{{ route('top-delivery-statuses.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Create Top Delivery Status</h4>
                        
                        <div class="mb-3">
                            <label class="col-form-label">Status IDs (Multiple)</label>
                            <select name="status_id[]" class="form-control" multiple>
                                @for($i = 0; $i <= 22; $i++)
                                    <option value="{{ $i }}">status {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="col-form-label">Title (English)</label>
                            <input type="text" name="title_en" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="col-form-label">Title (Russian)</label>
                            <input type="text" name="title_ru" class="form-control">
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Save</button>
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
