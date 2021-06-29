@extends('layouts.app')

@section('header')
<title>教えてサイト</title>
@endsection

@section('content')

<!--container-->
<div class="container low faq">
    <h1>教えてサイト</h1>

    <div class="title">{{ $category_name }}について</div>

    <ul class="new-list">
    @foreach ($faqs as $key => $items)
        <li>
            <a href="{{ url('/faqdetail/id/'. $items->id) }}" class="clearfix">
                <span class="photo">
                @if ($items->contractor_id != '')
                <img src="{{ asset('/photo/contractors/'. sprintf('%010d', $items->contractor_id)) }}.jpg" alt="業者">
                @endif
                </span>
                <span class="article-title">{{ $items->title }}</span>
                <span class="content">{{ $items->comment }}</span>
                <span class="date">{{ $items->disp_dt }}</span>
                <span class="favorite">★</span>
            </a>
        </li>
    @endforeach
    </ul>

<?php /*
    <div class="faq">
        <div class="button green pencil"><a href="">新規で投稿する</a></div>
    </div>
*/ ?>

    <div class="button back"><a href="{{ url('/faq') }}">教えてサイトトップに戻る</a>
</div>
<!--/container-->

@endsection
