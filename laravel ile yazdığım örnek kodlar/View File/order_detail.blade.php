@extends('individual.layout')

@section('title', 'Alışveriş Detayları')

@section('page_css')
    <link href="{{ Config::get('app.cdn_url') }}/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="{{ Config::get('app.cdn_url') }}/assets/individual/pages/css/profile.css" rel="stylesheet" type="text/css"/>
    <link href="{{ Config::get('app.cdn_url') }}/assets/individual/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
@stop

@section('content')
    <div class="row margin-top-10">
        <div class="col-md-12">

            @include('individual.sidebar')

            <div class="profile-content">


                <div class="row">
                    <div class="col-md-12">
                        <div class="portlet light">
                            <div class="portlet-title tabbable-line">
                                <div class="caption caption-md">
                                    <i class="icon-globe theme-font hide"></i>
                                    <span class="caption-subject font-blue-madison bold uppercase">Alışveriş Detayları</span>
                                </div>
                                <div class="actions">
                                    <a href="{{URL::route('individual.customer.orders')}}" class="btn btn-default btn-sm">
                                        <i class="fa fa-pencil"></i> Tüm Alışverişlerim</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="portlet yellow-crusta box">


                            <div class="portlet-body">
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Evrak No:
                                    </div>
                                    <div class="col-md-7 value">
                                        {{$order->report_id}}
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Order Date & Time:
                                    </div>
                                    <div class="col-md-7 value">
                                        Dec 27, 2013 7:16:25 PM
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Order Status:
                                    </div>
                                    <div class="col-md-7 value">
                                                                            <span class="label label-success">
                                                                            Closed </span>
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Grand Total:
                                    </div>
                                    <div class="col-md-7 value">
                                        $175.25
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Payment Information:
                                    </div>
                                    <div class="col-md-7 value">
                                        Credit Card
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        <div class="portlet yellow-crusta box">
                            <!--
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-cogs"></i>Order Details
                                </div>
                            </div>
                            -->


                            <div class="portlet-body">
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Order #:
                                    </div>
                                    <div class="col-md-7 value">
                                        12313232 <span class="label label-info label-sm">
                                                                            Email confirmation was sent </span>
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Order Date & Time:
                                    </div>
                                    <div class="col-md-7 value">
                                        Dec 27, 2013 7:16:25 PM
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Order Status:
                                    </div>
                                    <div class="col-md-7 value">
                                                                            <span class="label label-success">
                                                                            Closed </span>
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Grand Total:
                                    </div>
                                    <div class="col-md-7 value">
                                        $175.25
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-5 name">
                                        Payment Information:
                                    </div>
                                    <div class="col-md-7 value">
                                        Credit Card
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>



                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="portlet grey-cascade box">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-cogs"></i> Ürünler
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>
                                                Taksit No
                                            </th>
                                            <th>
                                                Son Ödeme Tarihi
                                            </th>
                                        </tr>
                                        <tbody>
                                        @foreach($order->payments as $payment)
                                        <tr>
                                            <td>
                                                {{$payment->payment_no}}
                                            </td>
                                            <td>
                                                {{date('d/m/Y', strtotime($payment->payment_date_normal))}}
                                            </td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="portlet grey-cascade box">
                            <div class="portlet-title">
                                <div class="caption">
                                    <i class="fa fa-cogs"></i> Ödeme Planı
                                </div>
                            </div>
                            <div class="portlet-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>
                                                Taksit No
                                            </th>
                                            <th>
                                                Son Ödeme Tarihi
                                            </th>
                                            <th>
                                                Taksit Tutarı
                                            </th>
                                            <th>
                                                Kalan Tutar
                                            </th>
                                            <th>
                                                Ödeme Tarihi
                                            </th>
                                            <th>
                                                Durum
                                            </th>
                                            <th>
                                                Ödeme Yap
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($order->payments as $payment)
                                        <tr>
                                            <td>
                                                {{ $payment->payment_no }}
                                            </td>
                                            <td>
                                                {{ $payment->get_payment_date_normal() }}
                                            </td>
                                            <td>
                                                {{ $payment->get_monthly_payment() }}
                                            </td>
                                            <td>
                                                {{ $payment->get_min_required_payment() }}
                                            </td>
                                            <td>
                                                {{ $payment->get_payment_date("-") }}
                                            </td>
                                            <td>
                                                @if($payment->status == "odendi")
                                                    <span class="label label-sm label-success">Ödendi</span>
                                                @elseif($payment->status == "odenecek")
                                                    @if($payment->payment_date_normal < date('Y-m-d'))
                                                        <span class="label label-sm label-danger">Geciken Ödeme</span>
                                                    @else
                                                        <span class="label label-sm label-warning">Ödenecek</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->status == "odenecek")
                                                    <a href="{{ URL::route('individual.customer.order_detail', $payment->id) }}" class="btn btn-xs default btn-editable"><i class="fa fa-pay"></i> Ödeme Yap</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                        </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                {{$order}}


            </div>
        </div>
    </div>
@stop