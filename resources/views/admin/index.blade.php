@extends('layouts.master')

@section('css')
<!--Chartist Chart CSS -->
<link rel="stylesheet" href="{{ URL::asset('plugins/chartist/css/chartist.min.css') }}">
<style>
    .mini-stat.stat-brown {
        background: linear-gradient(145deg, #8B4513 0%, #6f330d 100%) !important;
        border: 1px solid #6f330d;
    }

    .mini-stat.stat-brown .text-white-50,
    .mini-stat.stat-brown p,
    .mini-stat.stat-brown a {
        color: rgba(255, 244, 234, 0.85) !important;
    }

    #chart-with-area .ct-series-a .ct-line,
    #chart-with-area .ct-series-a .ct-point {
        stroke: #8B4513 !important;
    }

    #chart-with-area .ct-series-a .ct-area {
        fill: rgba(139, 69, 19, 0.22) !important;
    }
</style>
@endsection

@section('breadcrumb')
<div class="col-sm-6 text-left" >
     <h4 class="page-title">Dashboard</h4>
</div>
@endsection

@section('content')
                   <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card mini-stat stat-brown text-white">
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <div class="float-left mini-stat-img mr-4">
                                                <span class="ti-id-badge" style="font-size: 20px"></span>
                                            </div>
                                            <h5 class="font-16 text-uppercase mt-0 text-white-50">Total <br> Employees</h5>
                                            <h4 class="font-500 text-white-50">{{$data[0]}} </h4>
                                            <span class="ti-user" style="font-size: 71px"></span>
                                              
                                        </div>
                                        <div class="pt-2">
                                            <div class="float-right">
                                                <a href="#" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                                            </div>
                                            <p class="text-white-50 mb-0">More info</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card mini-stat stat-brown text-white">
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <div class="float-left mini-stat-img mr-4">
                                                <i class="ti-alarm-clock" style="font-size: 20px"></i>
                                            </div>
                                            <h6  class="font-16 text-uppercase mt-0 text-white-50" >On Time <br> Percentage</h6>
                                            <h4 class="font-500 text-white-50">{{$data[3]}} %<i class="text-danger ml-2"></i></h4>
                                            <span class="peity-donut" data-peity='{ "fill": ["#8B4513", "#ead8cb"], "innerRadius": 28, "radius": 32 }' data-width="72" data-height="72">{{$data[3]}}/{{count($data)}}</span>
                                                       
                                        </div>
                                        <div class="pt-2">
                                            <div class="float-right">
                                                <a href="#" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                                            </div>
        
                                            <p class="text-white-50 mb-0">More info</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card mini-stat stat-brown text-white">
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <div class="float-left mini-stat-img mr-4">
                                                <i class=" ti-check-box " style="font-size: 20px"></i>
                                            </div>
                                            <h5 class="font-16 text-uppercase mt-0 text-white-50">On Time <br> Today</h5>
                                            <h4 class="font-500 text-white-50">{{$data[1]}} <i class=" text-success ml-2"></i></h4>
                                            <span class="peity-donut" data-peity='{ "fill": ["#8B4513", "#ead8cb"], "innerRadius": 28, "radius": 32 }' data-width="72" data-height="72">{{$data[1]}}/{{count($data)}}</span>
                                             
                                        </div>
                                        <div class="pt-2">
                                            <div class="float-right">
                                                <a href="#" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                                            </div>
        
                                            <p class="text-white-50 mb-0">More info</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card mini-stat stat-brown text-white">
                                    <div class="card-body">
                                        <div class="mb-4">
                                            <div class="float-left mini-stat-img mr-4">
                                                <i class="ti-alert" style="font-size: 20px"></i>
                                            </div>
                                            <h5 class="font-16 text-uppercase mt-0 text-white-50">Late <br> Today</h5>
                                            <h4 class="font-500 text-white-50">{{$data[2]}}<i class=" text-success ml-2"></i></h4>
                                            <span class="peity-donut" data-peity='{ "fill": ["#8B4513", "#ead8cb"], "innerRadius": 28, "radius": 32 }' data-width="72" data-height="72">{{$data[2]}}/{{count($data)}}</span>
                                             
                                        </div>
                                        <div class="pt-2">
                                            <div class="float-right">
                                                <a href="#" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                                            </div>
        
                                            <p class="text-white-50 mb-0">More info</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="mt-0 header-title mb-5">Monthly Report</h4>
                                        <div class="row">
                                            <div class="col-lg-7">
                                                <div>
                                                    <div id="chart-with-area" class="ct-chart earning ct-golden-section"></div>
                                                </div>
                                            </div>
                                            <div class="col-lg-5">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="text-center">
                                                            <p class="text-muted mb-4">This month</p>
                                                            <h4>{{ $data[4] }}</h4>
                                                            <p class="text-muted mb-5">Total attendance records this month</p>
                                                            <span class="peity-donut" data-peity='{ "fill": ["#8B4513", "#ead8cb"], "innerRadius": 28, "radius": 32 }' data-width="72" data-height="72">{{$data[3]}}/{{count($data)}}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="text-center">
                                                            <p class="text-muted mb-4">Last month</p>
                                                            <h4>{{ $data[5] }}</h4>
                                                            <p class="text-muted mb-5">Total attendance records last month</p>
                                                            <span class="peity-donut" data-peity='{ "fill": ["#8B4513", "#ead8cb"], "innerRadius": 28, "radius": 32 }' data-width="72" data-height="72">3/5</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end row -->
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                        </div>
                        <!-- end row -->
                        
                        
                        <!-- end row -->
@endsection

@section('script')
<!--Chartist Chart-->
<script src="{{ URL::asset('plugins/chartist/js/chartist.min.js') }}"></script>
<script src="{{ URL::asset('plugins/chartist/js/chartist-plugin-tooltip.min.js') }}"></script>
<!-- peity JS -->
<script src="{{ URL::asset('plugins/peity-chart/jquery.peity.min.js') }}"></script>
<script src="{{ URL::asset('assets/pages/dashboard.js') }}"></script>
<script>
    // Line chart with real data
    new Chartist.Line('#chart-with-area', {
        labels: {!! json_encode(range(1, count($data[6]))) !!},
        series: [
            {!! json_encode($data[6]) !!}
        ]
    }, {
        low: 0,
        showArea: true,
        plugins: [
            Chartist.plugins.tooltip()
        ]
    });
</script>
@endsection