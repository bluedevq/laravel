@extends('admin.layouts.auth')
@section('content')
    <div class="container">
        <div class="wrapper">
            <div class="row">
                <div class="col-md-6 offset-md-3 mt-5">
                    {!! html()->form('post', route('admin.post.login'))->addClass('form-sign-in form-sss')->attributes(['show-loading' => 1])->open() !!}

                    <div class="mt-40-xs">
                        <div class="text-center"><b>{{ __('messages.page_title.admin.login') }}</b></div>
                    </div>

                    @include('admin.elements.flash_messages')

                    <div class="mt-40-xs form-group error">
                        <label>{{ __('models.administrators.attributes.email') }}</label>
                        {!! html()->email('email')->addClass('form-control ' .  ($errors->has('email') ? 'border-error' : '')) !!}
                        @if($errors->has('email'))<p class="error">{{ $errors->first('email') }}</p>@endif
                    </div>

                    <div class="mt-30-xs form-group">
                        <label>{{ __('models.administrators.attributes.password') }}</label>
                        {!! html()->password('password')->addClass('form-control ' .  ($errors->has('password') ? 'border-error' : '')) !!}
                        @if($errors->has('password'))<p class="error">{{ $errors->first('password') }}</p>@endif
                    </div>

                    <button class="btn btn-success" value="Login" type="submit">{{ __('messages.button.login') }}</button>
                    {!! html()->form()->close() !!}
                </div>
            </div>
        </div>
    </div>
@stop
