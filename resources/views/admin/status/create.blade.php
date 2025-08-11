@include('admin.includes.header')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <form action="{{route('statuses.store')}}" method="post" enctype="multipart/form-data">

                @foreach($errors->all() as $error)
                <p class="alert alert-danger">{{ $error }}</p>
                @endforeach
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">əlavə et</h4>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="col-form-label">Başlıq ru</label>
                                       <input class="form-control" type="text" name="title_ru">
                                        @if($errors->first('title_ru')) <small class="form-text text-danger">{{$errors->first('title_ru')}}</small> @endif
                                    </div>
                                     <div class="mb-3">
                                        <label class="col-form-label">Başlıq en</label>
                                       <input class="form-control" type="text" name="title_en">
                                        @if($errors->first('title_en')) <small class="form-text text-danger">{{$errors->first('title_en')}}</small> @endif
                                    </div>
                                    <div class="mb-3">
                                        <label class="col-form-label ">Statusun tipi</label>
                                        <select name="type" id="" class="form-select statusType">
                                            <option value="0">Admin</option>
                                            <option value="1">Front</option> 
                                            <!-- <option value="2">Topdelivery</option> -->
                                        </select>
                                    </div>
                                    <div class="mb-3 topdeliveryStatusId" style="display:none">
                                        <label class="col-form-label">Topdelivery Status Id</label>
                                        <input type="text" name="topdelivery_status_id" class="form-control">
                                    </div>
                                     <div class="mb-3 topdeliveryStatusHide" >
                                        <label class="col-form-label">Qaytarıla bilər</label>
                                        <select name="cancelable" id="" class="form-select">
                                            <option value="0">Xeyr</option>
                                            <option value="1">Bəli</option>
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


<script>
    $(function(){
        $(".statusType").on("change", function(e){
            if($(this).val()==2){
                $(".topdeliveryStatusId").css({
                    "display":"block"
                })

                $(".topdeliveryStatusHide").css({
                    "display":"none"
                })
            }else{
                 $(".topdeliveryStatusId").css({
                    "display":"none"
                })
                  $(".topdeliveryStatusHide").css({
                    "display":"block"
                })
            }
          
        })
    })
</script>
