@php
$bodyStyle = 'low customer';
@endphp

@extends('layouts.app')

@section('content')

		<header>
			<h1><a href="{{ route('change-log.index') }}">変更履歴</a></h1>

			<ul class="header-nav friend">
				<li><a href="{{ route('change-log.index') }}">一覧</a></li>
				<li class="current"><a href="{{ route('change-log.show',['id'=>$model->id]) }}">詳細</a></li>
			</ul>

		    <div class="title-wrap">

			    <dl class="project-serch clearfix">
				    <dt>user_id</dt>
				    <dd><input tupe="text"></dd>

				    <dt>created_at</dt>
				    <dd><input tupe="text"></dd>

				    <dt>tbl_name</dt>
				    <dd><input tupe="text"></dd>

				    <dt>before/after</dt>
				    <dd><input tupe="text"></dd>
			    </dl>

			    <div class="button-s">検索</div>
		    </div>

		</header>

	    <section class="project-wrapper">
		    <div class="project-deteil-wrap clearfix">
			    <dl class="clearfix">

				    <dt class="no icon-s">NO</dt>
				    <dd>{{ sprintf('%08d', $model->id) }}</dd>

				    <dt class="name icon-s">ユーザ名</dt>
				    <dd>{{ $model->user_id }}</dd>

				    <dt class="name icon-s">IP</dt>
				    <dd>{{ $model->ip }}</dd>

				    <dt class="name icon-s">URL</dt>
				    <dd>{{ $model->url }}</dd>

				    <dt class="name icon-s">テーブル名</dt>
				    <dd>{{ $model->tbl_name }}</dd>

				    <dt class="name icon-s">ID</dt>
				    <dd>{{ $model->tbl_id }}</dd>

			    </dl>
		    </div>

            <div>
                <table>
                    <thead>
                        <tr>
                            <th>変更前</th>
                            <th>変更後</th>
                        </tr>
                    </thead>
                    @foreach($model->getDiff() as $key => $values)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ array_shift($values) }}</td>
                            <td>{{ array_shift($values) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <div>
                &nbsp;
            </div>

            <div>
                <table>
                    <caption>
                        現在の当該レコードの値
                    </caption>
                    <thead>
                        <tr>
                            <th>列</th>
                            <th>値</th>
                        </tr>
                    </thead>
                    @foreach($model->getCurrent() as $key => $value)
                        <tr>
                            <th>{{ $key }}</th>
                            <td>{{ $value }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>

            <div>
                &nbsp;
            </div>


	    </section>

@endsection
