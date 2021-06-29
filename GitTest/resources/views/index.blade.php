@extends('layouts.layout')

@section('header')
    <title>{{ config('app.name') }}</title>
@endsection

@section('content')

	<div id="app">
		<App/>
	</div>

@endsection

