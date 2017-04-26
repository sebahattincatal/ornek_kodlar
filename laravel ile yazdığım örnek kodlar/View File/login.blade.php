@extends('individual.layout')

@section('title', 'Bireysel Giriş - PayTrust')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption caption-md">
                    <span class="caption-subject font-blue-madison bold uppercase">Kullanıcı Girişi</span>
                </div>
            </div>

            <div class="portlet-body form">

                @if (count($errors) == 0)
                    @if(Session::has('message'))
                        <div class="alert alert-danger" role="alert">{{Session::get('message')}}</div>
                    @endif
                @endif

                {!! Form::open(array('route' => 'individual.auth.login', 'method' => 'POST', 'class' => 'form-horizontal form-row-seperated')) !!}

                    <div class="form-body">
                        <div class="form-group @if ($errors->first('identity_no') != "") has-error @endif">
                            {!! Form::label('identity_no', 'T.C. Kimlik No', array('class' => 'col-md-3 control-label')) !!}
                            <div class="col-md-9">
                                <div class="input-group">
                                    {!! Form::text('identity_no', null, array('class' => 'form-control', 'placeholder' => 'T.C. Kimlik numaranızı girin', 'maxlength' => '11')) !!}
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                </div>
                                @if ($errors->first('identity_no') != "")
                                <span class="help-block">{{ $errors->first('identity_no')  }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group @if ($errors->first('password') != "") has-error @endif">
                            {!! Form::label('password', 'Parola', array('class' => 'col-md-3 control-label')) !!}
                            <div class="col-md-9">
                                {!! Form::password('password', array('class' => 'form-control')) !!}
                                @if ($errors->first('password') != "")
                                <span class="help-block">{{ $errors->first('password')  }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group last">
                            <label class="col-md-3 control-label"></label>
                            <div class="col-md-9">
                                <p class="form-control-static">
                                     <a href="{{URL::route('individual.auth.reminder')}}">Parolamı Unuttum</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions fluid">
                        <div class="row">
                            <div class="col-md-offset-3 col-md-9">
                                {!! Form::button('<i class="fa fa-lock"></i> Güvenli Giriş', array('class' => 'btn green', 'type' => 'submit')) !!}
                            </div>
                        </div>
                    </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>


  <div class="col-md-6">
      <div class="portlet light bordered">
          <div class="portlet-title">
              <div class="caption caption-md">
                  <span class="caption-subject font-blue-madison bold uppercase">Kullanıcı Ekranı</span>
              </div>
          </div>

        <!--<a href="{{URL::route('individual.auth.login')}}">Giriş</a>
        <br />-->
        <a href="{{URL::route('individual.auth.reminder')}}">Parola sıfırla</a>
        <br /><br/>
        <a href="{{URL::route('individual.auth.register')}}">Yeni Kayıt</a>
    </div>
  </div>

</div>
@stop
