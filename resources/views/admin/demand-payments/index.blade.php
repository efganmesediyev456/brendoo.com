@include('admin.includes.header')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            @if(session('message'))
                                <div class="alert alert-success">{{session('message')}}</div>
                            @endif
                            <h4 class="card-title">Tələb olunan ödənişlər</h4>
                            @can('create-demand-payments')
                            <a href="{{route('coupons.create')}}" class="btn btn-primary">+</a>
                            <br>
                            <br>
                            @endcan
                            <div class="table-responsive">
                                <table class="table table-centered mb-0 align-middle table-hover table-nowrap">
                                    <thead class="table-light">
                                    <tr>
                                        <th>№</th>
                                        <th>Influser</th>
                                        <th>Tip</th>
                                        <th>Miqdar</th>
                                        <th>Status</th>
                                        <th>Tarix</th>
                                        <th>Əməliyyat</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @foreach($demandPayments as $key => $demandPayment)

                                        <tr >
                                            <th scope="row">{{ $demandPayment->id }}</th>
                                            <th  scope="row">{{$demandPayment->influencer?->name}}</th>
                                            {{--                                                <td><img src="{{asset('storage/'.$demandPayment->image)}}" style="width: 100px; height: 50px" alt=""></td>--}}
                                            <td>{{$demandPayment->type_show}}</td>
                                            <td>{{$demandPayment->amount}}</td>
                                            <td>
                                                {{ $demandPayment->status->label() }}
                                            </td>
                                            <td>
                                                {{ $demandPayment->created_at->format("Y-m-d H:i:s") }}
                                            </td>
                                            <td>
                                                @can('edit-demand-payments')
                                                    <a data-id="{{ $demandPayment->id }}" data-date="{{ $demandPayment->created_at }}" data-amount="{{ $demandPayment->amount  }}" data-tip="{{ $demandPayment->type }}" data-influserName="{{ $demandPayment->influencer?->name }}"  href="" class="btn btn-primary statusChange"
                                                         style="margin-right: 15px">Statusu deyis</a>
                                                @endcan
                                            </td>
                                        </tr>

                                    @endforeach
                                    </tbody>
                                </table>
                                <br>
                                {{ $demandPayments->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Bağla"></button>
      </div>
      
      
      <div class="modal-body">


    <ul class="list-group">
         <li class="list-group-item d-flex justify-content-between">
            <div class="d-flex justify-content-between w-100">
                <div class="fw-bold">İnfluser</div>
                <p id="influserName" class="mb-0"></p>
            </div>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <div class="fw-bold">Tip</div>
            <p id="tip" class="mb-0"></p>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <div class="fw-bold">Miqdar</div>
            <p id="amount" class="mb-0"></p>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <div class="fw-bold mb-1">Status</div>
            <select data-id="" class="form-select w-50 statusChangeDemand">
                @foreach(\App\Enums\DemandPaymentStatusEnum::cases() as $case)
                    @if($case->value ==\App\Enums\DemandPaymentStatusEnum::NotRequested->value)
                    @continue
                    @endif
                    <option value="{{ $case->value }}">{{ $case->label() }}</option>
                @endforeach
            </select>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <div class="fw-bold">Tarix</div>
            <p id="date" class="mb-0"></p>
        </li>
    </ul>

</div>

      
    
      
    </div>
  </div>
</div>


@include('admin.includes.footer')


<script>
    $(function(){
        $(".statusChange").click(function(e){
            e.preventDefault();
            $("#exampleModal").modal("show")
            $("#exampleModalLabel").text("Statusu dəyiş");
            $("#influserName").text($(this).attr("data-influserName"))
            $("#amount").text($(this).attr("data-amount"))
            $("#tip").text($(this).attr("data-tip"))
            $("#date").text($(this).attr("data-date"))
            $(".statusChangeDemand").attr('data-id',$(this).attr('data-id'))
        })

        $(".statusChangeDemand").on("change", function(){
            var value = $(this).val();
            var dataId = $(this).attr('data-id');
            var _this = this;
            $.ajax({
                url:"",
                type:"post",
                data:{
                    status:value,
                    dataId,
                    _token:'{{ csrf_token() }}'
                },
                success:function(e){
                    toastr.success(e.message);
                    $("#exampleModal").modal("hide");
                    $(".statusChange[data-id='"+dataId+"']").parents("tr").find("td:nth-child(5)").text(e.data.status)
                },
                error:function(e){
                    toastr.error(e.responseJSON.message);
                    $("#exampleModal").modal("hide");
                }
            })
            
        })
    })
</script>