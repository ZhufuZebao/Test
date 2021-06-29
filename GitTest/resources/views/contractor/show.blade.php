@extends('layouts.app')

@section('header')
<title>登録画面</title>
<link rel="stylesheet" href="{{ url('/') }}/css/bootstrap.min.css" />
@endsection

@section('content')

    {{ Breadcrumbs::render('contractor.show', $model) }}

<!--container-->
<div class="container low user">


    <div class="alert alert-warning">
        
        <div>
            名前： {{ $model->name }}
        </div>

        <div class="col-md-4">
            〒{{ $model->zip }}
            {{ ($p = $model->prefecture) ? $p->name : null }}
            {{ $model->addr }}
        </div>


        <div class="col-md-4">
            代表：{{ $model->representative }}
        </div>


        <div class="col-md-4">
            設立：{{ $model->establishment }}
        </div>

        <div class="col-md-4">
            説明：{{ $model->contents }}
        </div>

        <a class="btn btn-primary" href="{{ route('contractor.edit',['id'=>$model->id]) }}">編集</a>
      </div>

        
    <table>
        <caption>案件</caption>
        <thead>
            <tr>
                <td>名称</td>
                <td>開始日</td>
                <td>終了日</td>
                <td>作成者</td>
                <td>担当会社</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($model->projects as $project)
                <tr>
                    <td><a href="{{ route('project.show', ['id' => $project->id]) }}">{{ $project->name }}</a></td>
                    <td>{{ $project->st_date }}</td>
                    <td>{{ $project->ed_date }}</td>
                    <td>{{ $project->author }}</td>
                    <td>{{ ($c = $project->contractor) ? $c->name : null }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
<!--/container-->

@endsection
