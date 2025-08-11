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

                            
                            <div class="d-flex justify-content-between align-items-center">
                                
                                <h4 class="card-title">Əldə olunan qazanc</h4>
                                
                                <a href="{{ route('influencers.collections.products.index',[
                                    'influencer'=>request()->influencer,
                                    'collection'=>request()->collection
                                ]) }}" class="btn btn-primary">Geri Qayıt</a>
                            
                            </div>

                           

                            
                            <!-- @can('create-blogs')
                               <a href="{{route('blogs.create')}}" class="btn btn-primary">+</a>
                               @endcan -->
                            <br>
                            <br>
                            <div class="table-responsive">


                            
                                <table class="table table-centered mb-0 align-middle table-hover table-nowrap">

                                    <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Influser</th>
                                        <th>Miqdar</th>
                                        <th>Tip</th>
                                        <th>Kolleksiya</th>
                                        <th>Müştəri</th>
                                        <th>Kupon</th>
                                        <th>Yaradılma tarixi</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($demandPaymentBalanceOrders as $balance)

                                    
                                    
                                    
                                        <tr>
                                            <td scope="row">{{$loop->iteration}}</td>
                                            <td scope="row">{{ $balance->demandPaymentBalance?->influencer?->fullName }}</td>
                                            <td scope="row">{{$balance->amount}}</td>
                                            <td scope="row">{{$balance->type}}</td>
                                            <td scope="row">{{$balance->collection?->title}}</td>
                                            <td scope="row">{{$balance->customer?->fullName}}</td>
                                            <td scope="row">{{$balance->coupon?->title}}</td>
                                            <td scope="row">{{$balance->created_at->format('Y-m-d H:i:s')}}</td>
                                        </tr>

                                    @endforeach

                                    </tbody>
                                </table>
                                <br>
                                {{ $demandPaymentBalanceOrders->links('admin.vendor.pagination.bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




@include('admin.includes.footer')






