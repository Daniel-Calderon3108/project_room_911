@extends('layouts.header.header')

@section('title', 'Login')
@section('title_header', 'ROOM 911 Authentication')
@section('content')
    <section class="form_login">
        <form action="/login" method="POST" class="login">
            @csrf
            <figure>
                <img src="{{ asset('assets/user.png') }}" alt="">
            </figure>
            @if (session('error'))
                <div class="alert">
                    {{ session('error') }}
                </div>
            @endif
            <input type="text" placeholder="Username" name="name">
            <input type="password" placeholder="Password" name="password">
            <button><img src="{{ asset('assets/enter.svg') }}" alt=""> Access</button>
        </form>
    </section>
@endsection