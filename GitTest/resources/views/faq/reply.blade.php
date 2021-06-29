@extends('layouts.app')

@section('header')
<title>教えてサイト</title>
@endsection

@section('content')

<!--container-->
<div class="container low faq details response">
    <h1>教えてサイト</h1>

    <div class="title">{{ $faqs->title }}</div>

    <form class="form-horizontal" name="input_form" role="form" method="POST" action="{{ url('/faqconfirm') }}">
    {{ csrf_field() }}
    <input type="hidden" name="id" id="id" value="{{ $faqs->id }}">

    <textarea name="comment">{{ old('comment') }}</textarea>

@if ($errors->has('comment'))
    <span class="help-block">
        <strong>{{ $errors->first('comment') }}</strong>
    </span>
@endif

    </form>

    <div class="button green"><a href="javascript:document.input_form.submit();">入力内容の確認</a></div>


    <div class="button back"><a href="{{ url('/faq') }}">教えてサイトトップに戻る</a>
</div>
<!--/container-->

@endsection
