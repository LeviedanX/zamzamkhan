@extends('layouts.app')

@section('bodyClass', 'public-home')

@section('content')
    @include('partials.hero')
    @include('partials.tentang')
    @include('partials.visi-misi')
    @include('partials.layanan')
    @include('partials.keunggulan')
    @include('partials.artikel')
    @include('partials.agenda')
    @include('partials.statistik')
    @include('partials.klien')
    @include('partials.testimoni')
    @include('partials.faq')
    @include('partials.kontak')
@endsection
