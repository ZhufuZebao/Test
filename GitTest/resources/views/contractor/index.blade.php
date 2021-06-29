@extends('layouts.app')

@section('header')
<title>登録画面</title>
<link rel="stylesheet" href="{{ url('/') }}/css/bootstrap.min.css" />
@endsection

@section('content')

    {{ Breadcrumbs::render('contractor.index') }}

<!--container-->
<div class="container low user">
    <h1>案件一覧</h1>

    <div class="container">
        <table>
            <thead>
                <tr>
                    <td>名称</td>
                    <td>〒</td>
                    <td>都道府県</td>
                    <td>住所</td>
                    <td>代表</td>
                    <td>設立</td>
                    <td>説明</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($models as $model)
                    <tr>
                        <td><a href="{{ route('contractor.show', ['id' => $model->id]) }}">{{ $model->name }}</a></td>
                        <td>{{ $model->zip }}</td>
                        <td>{{ ($p = $model->prefecture) ? $p->name : null }}</td>
                        <td>{{ $model->addr }}</td>
                        <td>{{ $model->representative }}</td>
                        <td>{{ $model->establishment }}</td>
                        <td>{{ $model->contents }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $models->links() }}

</div>
<!--/container-->

@endsection
