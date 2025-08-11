@include('admin.includes.header')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <form action="{{route('statuses.update', $status->id)}}" method="post" enctype="multipart/form-data">
                    @method('PUT')

                    @if(session()->has('message'))
                    <p class="alert alert-success">{{ session()->get('message') }}</p>
                    @endif

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
                                       <input value="{{ $status->title_ru }}" class="form-control" type="text" name="title_ru">
                                        @if($errors->first('title_ru')) <small class="form-text text-danger">{{$errors->first('title_ru')}}</small> @endif
                                    </div>

                                     <div class="mb-3">
                                        <label class="col-form-label">Başlıq en</label>
                                       <input class="form-control" type="text" name="title_en" value="{{ $status->title_en }}">
                                        @if($errors->first('title_en')) <small class="form-text text-danger">{{$errors->first('title_en')}}</small> @endif
                                    </div>

                                    <div class="mb-3">
                                        <label class="col-form-label ">Statusun tipi</label>
                                        <select name="type" id="" class="form-select statusType">
                                            <option value="0" @selected($status->type==0)>Admin</option>
                                            <option value="1" @selected($status->type==1)>Front</option>
                                            <!-- <option value="2" @selected($status->type==2)>Topdelivery</option> -->
                                        </select>
                                    </div>



                                    <div class="mb-3">
                                        <label class="col-form-label ">Statuslar hansı status ilə əlaqələnsin?</label>
                                        <select name="related_id" id="" class="form-select">
                                            <option value="">Seçin(Seçilməyədə bilər)</option>
                                            @foreach($statuses as $stat)
                                            <option @selected($stat->id==$status->related_id) value="{{ $stat->id }}">{{ $stat->title_ru }}(Ru) - {{ $stat->title_en }}(En)({{ $stat->statusView }}) </option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="mb-3 topdeliveryStatusId"  @if($status->type!=2) style="display:none" @endif>
                                        <label class="col-form-label">Topdelivery Status Id</label>
                                        <input type="text" name="topdelivery_status_id" class="form-control" value="{{ $status->topdelivery_status_id }}">
                                    </div>


                                    <div class="mb-3 topdeliveryStatusHide" @if($status->type==2) style="display:none" @endif>
                                        <label class="col-form-label">Qaytarıla bilər</label>
                                        <select name="cancelable" id="" class="form-select">
                                            <option value="0" @selected($status->cancelable==0)>Xeyr</option>
                                            <option value="1" @selected($status->cancelable==1)>Bəli</option>
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
