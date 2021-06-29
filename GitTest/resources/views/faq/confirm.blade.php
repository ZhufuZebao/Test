@extends('layouts.app')

@section('header')
<title>教えてサイト</title>
@endsection

@section('content')

<!--container-->
<div class="container low faq details response">
    <h1>教えてサイト</h1>

    <div class="title">{{ $faqs->title }}</div>

    <div class="response-text">{{ $input['comment'] }}</div>

    <div class="button green"><a href="javascript:document.input_form.submit();">送信</a></div>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/faqregist') }}">
    {{ csrf_field() }}
    <input type="hidden" name="id" id="id" value="{{ $faqs->id }}">
    <input type="hidden" name="comment" id="comment" value="{{ $input['comment'] }}">
    </form>

    <div class="button back"><a href="{{ url('/faq') }}">教えてサイトトップに戻る</a>
</div>
<!--/container-->

@endsection
