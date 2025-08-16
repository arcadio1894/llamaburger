@extends('layouts.auth')

@section('styles')
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;500;600;700&family=Open+Sans:wght@400;600;700&display=swap");

        h1, h2 {
            font-family: 'Dancing Script', cursive;
            font-size: 2.5rem !important;
            font-weight: bold;
        }

        .btn-submit{
            display: inline-block !important;
            padding: 10px 55px !important;
            background-color: #ffbe33 !important;
            color: #ffffff !important;
            border-radius: 45px !important;
            -webkit-transition: all 0.3s;
            transition: all 0.3s;
            border: none !important;
        }

        .img {
            width: 400px;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .img img {
            width: 350px;
            height: auto;
        }
    </style>
@endsection

@section('content')
    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center mb-3">
                    <h2 class="heading-section">Iniciar sesión</h2>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-12 col-lg-10">
                    <div class="wrap d-md-flex">
                        <div class="img">
                            <img src="{{ asset('/landing/images/about-img1.png') }}" alt="Imagen">
                        </div>
                        <div class="login-wrap p-4 p-md-5">

                            <form method="POST" action="{{ route('login') }}" class="signin-form">
                                @csrf
                                <div class="form-group mb-3">
                                    <label class="label" for="name">Correo Electrónico</label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="label" for="password">Contraseña</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn-submit form-control btn btn-primary rounded submit px-3">Iniciar Sesión</button>
                                </div>
                                <div class="form-group d-md-flex">
                                    <div class="w-50 text-left">
                                        <label class="checkbox-wrap checkbox-primary mb-0">Recordar
                                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} checked>

                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    {{--<div class="w-50 text-md-right">
                                        @if (Route::has('password.request'))
                                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                                {{ __('Forgot Your Password?') }}
                                            </a>
                                        @endif
                                    </div>--}}
                                </div>
                            </form>
                            @if (Route::has('register'))
                            <p class="text-center">Aún no tienes una cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
                            @endif
                            <p class="text-center">Regresar <a href="{{ url('/') }}">Inicio</a></p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
