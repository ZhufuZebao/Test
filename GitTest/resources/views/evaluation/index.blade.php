@extends('layouts.app')

@section('header')
<title>評価</title>
@endsection

@section('content')

<!--container-->
<div class="container low evaluation">
    <h1>評価</h1>

    <div class="evaluation">
        <div class="button green pencil"><a href="{{ url('/listevaluation') }}">依頼主の評価を投稿する</a></div>
        <div class="button white"><a href="{{ url('/myevaluation') }}">自分の評価を見る</a></div>
    </div>
</div>
<!--/container-->

@endsection
