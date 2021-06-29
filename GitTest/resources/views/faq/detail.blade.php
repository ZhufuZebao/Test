@extends('layouts.app')

@section('header')
<title>教えてサイト</title>
@endsection

@section('content')

<!--container-->
<div class="container low faq details">
    <h1>教えてサイト</h1>

    <div class="title">{{ $faqs->title }}</div>

    <div class="date">{{ $faqs->disp_dt }}</div>
    <div class="favorite added">★</div>

    <dl>
        <dt>【内容】</dt>
        <dd>
            {{ $faqs->comment }}
        </dd>
    </dl>

    <div class="button green pencil"><a href="{{ url('/faqreply/id/'. $faqs->id) }}">返信する</a></div>


    <div class="button back"><a href="{{ url('/faq') }}">教えてサイトトップに戻る</a>
</div>
<!--/container-->

@endsection
