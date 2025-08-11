@include('admin.includes.header')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('coupons.update', $coupon->id) }}" method="post" enctype="multipart/form-data">
                {{ method_field('PUT') }}
                @csrf
                <div class="card">
                    
                    <div class="card-body">

                    

                        <nav aria-label="breadcrumb" style="margin-bottom: 20px;">
                            <ol class="breadcrumb bg-light p-3 rounded">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('coupons.index') }}">Kupon siyahı</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $coupon->code }}</li>
                            </ol>
                        </nav>


                         


                         <ul class="nav nav-tabs" id="languageTab" role="tablist">
                            @foreach(['en', 'ru'] as $lang)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link @if($loop->first) active @endif" id="{{ $lang }}-tab" data-bs-toggle="tab" href="#{{ $lang }}" role="tab" aria-controls="{{ $lang }}" aria-selected="true">
                                        {{ strtoupper($lang) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>


                        <div class="tab-content" id="languageTabContent">
                            @foreach(['en', 'ru'] as $lang)
                                <div class="tab-pane fade @if($loop->first) show active @endif" id="{{ $lang }}" role="tabpanel" aria-labelledby="{{ $lang }}-tab">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="col-form-label">Başlıq* {{ $lang }}</label>
                                                <input id="title_{{ $lang }}" class="form-control" type="text" value="{{ $coupon->translate($lang)?->title }}" name="{{ $lang }}_title">
                                                @if($errors->first("{$lang}_title"))
                                                    <small class="form-text text-danger">{{ $errors->first("{$lang}_title") }}</small>
                                                @endif
                                            </div>
                                        </div>
                                      
                                    </div>
                                </div>
                            @endforeach
                        </div>


                        <div class="row">
                            <div class="col-md-6">

                                <!-- Coupon Code Input -->
                                <div class="mb-3">
                                    <label class="col-form-label">Kupon Kodu</label>
                                    <input class="form-control" type="text" name="code" value="{{ old('code', $coupon->code) }}" required>
                                    @if($errors->first('code'))
                                        <small class="form-text text-danger">{{ $errors->first('code') }}</small>
                                    @endif
                                </div>

                                <!-- Discount Input -->
                                <div class="mb-3">
                                    <label class="col-form-label">Endirim</label>
                                    <input class="form-control" type="number" name="discount" value="{{ old('discount', $coupon->discount) }}" required>
                                    @if($errors->first('discount'))
                                        <small class="form-text text-danger">{{ $errors->first('discount') }}</small>
                                    @endif
                                </div>

                                <!-- Type Input (Percentage or Amount) -->
                                <div class="mb-3">
                                    <label class="col-form-label">Endirim Tipi</label>
                                    <select class="form-control" name="type" required>
                                        <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Faiz</option>
                                        <option value="amount" {{ old('type', $coupon->type) == 'amount' ? 'selected' : '' }}>Məbləğ</option>
                                    </select>
                                    @if($errors->first('type'))
                                        <small class="form-text text-danger">{{ $errors->first('type') }}</small>
                                    @endif
                                </div>

                                <!-- Valid From Date -->
                                <div class="mb-3">
                                    <label class="col-form-label">Başlanğıc Tarixi</label>
                                    <input class="form-control" type="date" name="valid_from" value="{{ old('valid_from', $coupon->valid_from) }}" required>
                                    @if($errors->first('valid_from'))
                                        <small class="form-text text-danger">{{ $errors->first('valid_from') }}</small>
                                    @endif
                                </div>

                                <!-- Valid Until Date -->
                                <div class="mb-3">
                                    <label class="col-form-label">Bitmə Tarixi</label>
                                    <input class="form-control" type="date" name="valid_until" value="{{ old('valid_until', $coupon->valid_until) }}" required>
                                    @if($errors->first('valid_until'))
                                        <small class="form-text text-danger">{{ $errors->first('valid_until') }}</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="col-form-label">Kupon Tipi</label>
                                    <select class="form-select" name="coupon_type">
                                        <option value="">Seçin</option>
                                        <option @selected($coupon->coupon_type==0) value="0">Adi</option>
                                        <option @selected($coupon->coupon_type==1) value="1">Influencer</option>
                                    </select>
                                    @if($errors->first('coupon_card_type'))
                                        <small class="form-text text-danger">{{ $errors->first('coupon_card_type') }}</small>
                                    @endif
                                </div>


                                 <div class="mb-3" @if($coupon->coupon_type===0) style="display: none;" @endif >
                                    <label class="col-form-label">Influser</label>
                                    <select class="form-select" name="influencer_id">
                                        <option value="">Seçin</option>
                                        @foreach($influencers as $influencer)
                                            <option @selected($influencer->id == $coupon->influencer_id) value="{{ $influencer->id }}">{{ $influencer->fullName }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->first('influencer_id'))
                                        <small class="form-text text-danger">{{ $errors->first('influencer_id') }}</small>
                                    @endif
                                </div>

                                <!-- Active Checkbox -->
                                <div class="mb-3">
                                        <label class="col-form-label">Aktiv</label>
                                        <select name="is_active" class="form-control">
                                            <option value="1" {{ old('is_active', $coupon->is_active) == 1 ? 'selected' : '' }}>Aktiv</option>
                                            <option value="0" {{ old('is_active', $coupon->is_active) == 0 ? 'selected' : '' }}>Deaktiv</option>
                                        </select>
                                    </div>


                                <div class="mb-3">
                                    <label class="col-form-label">Bir nəfərdən əldə olunan qazanc</label>
                                    <input  name="earn_price" type="number" value="{{ $coupon->earn_price }}" class="form-control">
                                    @if($errors->first('earn_price'))
                                        <small class="form-text text-danger">{{ $errors->first('earn_price') }}</small>
                                    @endif
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
        $("[name='coupon_type']").on("change", function(){
            if($(this).val()==1){
                $("[name='influencer_id']").parent().css({
                    "display":"block"
                })
            }else{
                 $("[name='influencer_id']").parent().css({
                    "display":"none"
                })
            }
        })
    })
</script>