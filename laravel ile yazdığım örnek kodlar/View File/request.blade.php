@extends('individual.layout')

@section('title', 'Kendini Sorgula')


@section('page_css')
    <link href="{{ Config::get('app.cdn_url') }}/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css" rel="stylesheet" type="text/css"/>
    <link href="{{ Config::get('app.cdn_url') }}/assets/individual/pages/css/profile.css" rel="stylesheet" type="text/css"/>
    <link href="{{ Config::get('app.cdn_url') }}/assets/individual/pages/css/tasks.css" rel="stylesheet" type="text/css"/>
@stop

@section('page_js_inline')
<script type="text/javascript">
    function district_empty(){
        $("select[name='district_id']").empty().append('<option selected="selected">Önce il seçin</option>');
    }

    $(document).ready(function(){
        $("input[name='mobile']").mask("(599) 999-9999");
        $("input[name='identity_serial']").mask("a99");
        $("input[name='identity_sequence']").mask("999999");

        $("#formSubmitBtn").click(function(){
            loading();
        });

        $("select[name='city_id']").change(function(){
            var city_id = $(this).val();
            if(city_id < 1)
                district_empty();
            else {
                $.post(config.service_routes.common.district_list, {city_id: city_id}, function(data) {
                    if(!data.error) {
                        $("select[name='district_id']").empty();
                        $.each(data.district_list, function (i, item) {
                            $("select[name='district_id']").append('<option value="' + data.district_list[i].id + '">' + data.district_list[i].name + '</option>');
                        });
                    } else {
                        district_empty();
                        alert(data.error);
                    }

                }, 'json');
            }

        });


    });
</script>
@stop

@section('content')

    <div class="row margin-top-10">
        <div class="col-md-12">
            @include('individual.sidebar')

            <div class="profile-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="portlet light ">
                            <div class="portlet-title tabbable-line">
                                <div class="caption caption-md">
                                    <i class="icon-globe theme-font hide"></i>
                                    <span class="caption-subject font-blue-madison bold uppercase">PayTrust Sorgulama :: 1.Adım</span>
                                </div>
                            </div>

                            <style type="text/css">
                                .bold { font-weight: 600 !important; }
                            </style>

                            <div class="portlet-body form">
                                <div class="tab-content">

                                    {!! Form::open(array('route' => 'individual.report.request', 'method' => 'POST', 'name' => 'topic_form', 'class' => 'form-horizontal innerT', 'role' => 'form')) !!}
                                    <div class="form-body">
                                        <div class="form-group">
                                            {!! Form::label('title', 'Sorgulanacak Kişi:', array('class' => 'col-md-3 control-label bold')) !!}
                                            <div class="col-sm-9">
                                                <p class="form-control-static">VOLKAN METİN  <strong>[</strong> 27364335722 <strong>]</strong></p>
                                            </div>
                                        </div>

                                        <div class="form-group @if ($errors->has('bank')) has-error @endif">
                                            {!! Form::label('bank', 'Çalıştığınız Banka:', array('class' => 'col-md-3 control-label bold')) !!}
                                            <div class="col-md-9">
                                                {!! Form::select('bank', $data['banks'], '', array('class' => 'form-control input-large')) !!}
                                                @if ($errors->has('bank'))
                                                    <span class="help-inline">{{ $errors->first('bank')  }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group @if ($errors->has('mobile')) has-error @endif">
                                            {!! Form::label('mobile', 'Cep Telefonu:', array('class' => 'col-md-3 control-label bold')) !!}
                                            <div class="col-sm-9">
                                                {!! Form::text('mobile', null, array('class' => 'form-control input-large', 'placeholder' => '')) !!}
                                                @if ($errors->first('mobile'))
                                                    <span class="help-block">{{ $errors->first('mobile') }}</span>
                                                @else
                                                    <span class="help-block">Bankada kayıtlı cep telefonunuz</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group @if ($errors->has('city_id') || $errors->has('district_id') || $errors->has('address')) has-error @endif">
                                            {!! Form::label('address', 'Açık Adres:', array('class' => 'col-md-3 control-label bold')) !!}
                                            <div class="col-sm-9">
                                                {!! Form::select('city_id', $data['cities'], '', array('class' => 'form-control input-small input-inline')) !!}
                                                {!! Form::select('district_id', $data['districts'], '', array('class' => 'form-control input-small input-inline')) !!}
                                                <br />
                                                {!! Form::textarea('address', null, array('class' => 'form-control input-large', 'placeholder' => 'Açık adresinizi buraya yazın', 'cols' => 40, 'rows' => 5)) !!}
                                                @if ($errors->first('address'))
                                                    <span class="help-block">{{ $errors->first('address') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group @if ($errors->has('identity_serial') || $errors->has('identity_sequence')) has-error @endif">
                                            {!! Form::label('identity', 'Nüfus Cüzdanı Seri/Sıra:', array('class' => 'col-md-3 control-label bold')) !!}
                                            <div class="col-sm-9">
                                                {!! Form::text('identity_serial', null, array('class' => 'form-control input-xs input-inline', 'placeholder' => 'Seri numarası')) !!}
                                                {!! Form::text('identity_sequence', null, array('class' => 'form-control input-small input-inline', 'placeholder' => 'Sıra numarası')) !!}
                                                @if ($errors->first('identity'))
                                                    <span class="help-block">{{ $errors->first('identity') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group @if ($errors->has('mother_maiden_name_1') || $errors->has('mother_maiden_name_2')) has-error @endif">
                                            {!! Form::label('mother_maiden_name', 'Anne Kızlık Soyadının ilk 2 Harfi:', array('class' => 'col-md-3 control-label bold')) !!}
                                            <div class="col-sm-9">
                                                {!! Form::text('mother_maiden_name_1', null, array('class' => 'form-control input-xs input-inline', 'placeholder' => 'İlk harfi', 'maxlength' => 1)) !!}
                                                {!! Form::text('mother_maiden_name_2', null, array('class' => 'form-control input-xs input-inline', 'placeholder' => 'İkinci harfi', 'maxlength' => 1)) !!}
                                                @if ($errors->first('mother_maiden_name'))
                                                    <span class="help-block">{{ $errors->first('mother_maiden_name') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group @if ($errors->has('identity_volume_no')) has-error @endif">
                                            {!! Form::label('identity_volume_no', 'Cilt Numarası:', array('class' => 'col-md-3 control-label bold')) !!}
                                            <div class="col-sm-9">
                                                {!! Form::text('identity_volume_no', null, array('class' => 'form-control input-large', 'placeholder' => '', 'maxlength' => 4)) !!}
                                                @if ($errors->first('identity_volume_no'))
                                                    <span class="help-block">{{ $errors->first('identity_volume_no') }}</span>
                                                @else
                                                    <span class="help-block">Nüfus cüzdanının arka tarafında yer alan cilt numaranız</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group @if ($errors->has('identity_city_id')) has-error @endif">
                                            {!! Form::label('identity_city_id', 'Nüfusa Kayıtlı İl:', array('class' => 'col-md-3 control-label bold')) !!}
                                            <div class="col-md-9">
                                                {!! Form::select('identity_city_id', $data['cities'], '', array('class' => 'form-control input-large')) !!}
                                                @if ($errors->has('identity_city_id'))
                                                    <span class="help-inline">{{ $errors->first('identity_city_id')  }}</span>
                                                @endif
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-actions">
                                        <div class="row">
                                            <div class="col-md-offset-3 col-md-9">
                                                {!! Form::button('<i class="fa fa-search-plus"></i> Sorgulamayı Başlat', array('type' => 'submit', 'class' => 'btn btn-primary', 'id' => 'formSubmitBtn')) !!}
                                            </div>
                                        </div>
                                    </div>

                                    {!! Form::close() !!}

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END PROFILE CONTENT -->
        </div>
    </div>
@stop
