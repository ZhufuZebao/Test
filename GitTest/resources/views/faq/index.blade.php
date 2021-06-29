@extends('layouts.app')

@section('header')
<title>教えてサイト</title>
@endsection

@section('content')

<!--container-->
<div class="container low faq">
    <h1>教えてサイト</h1>

    <div class="title">カテゴリーを選択</div>

    <ul class="category-list clearfix">
        <li><a href="{{ url('/faqcategory/id/1') }}"></a>材料</li>
        <li><a href="{{ url('/faqcategory/id/2') }}"></a>メンバー</li>
        <li><a href="{{ url('/faqcategory/id/3') }}"></a>技術</li>
        <li><a href="{{ url('/faqcategory/id/4') }}"></a>イベント</li>
        <li><a href="{{ url('/faqcategory/id/5') }}"></a>売買</li>
        <li><a href="{{ url('/faqcategory/id/6') }}"></a>その他</li>
    </ul>

    <div class="title">新着記事</div>

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
</div>
<!--/container-->

@endsection
