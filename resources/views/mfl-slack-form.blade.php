
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">Sign into your MFL account</div>
            <div class="panel-body">
                {{ Form::open( array('action' => 'RegisterFormController@registerUser') ) }}
                <div class="form-group">
                    <div class="form-section">
                        {!! Form::label('MFL Username or Email *') !!}
                        {!! Form::text('email', null, 
                            array('required', 
                                'class'=>'form-control', 
                                'placeholder'=>'Your e-mail address')) !!}
                    </div>
                    <div class="form-section"> 
                        {!! Form::label('MFL Password *') !!}
                        {!! Form::text('password', null, 
                            array('required', 
                                'class'=>'form-control', 
                                'placeholder'=>'Your password')) !!}
                    </div>

                    {!! Form::submit('Connect Account',
                        array('class'=>'btn btn-primary')) !!}
                </div>

                {{ Form::close() }}
            </div>
            </div>
    </div>
</div>
@endsection