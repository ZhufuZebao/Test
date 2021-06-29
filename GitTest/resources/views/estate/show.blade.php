@php
$bodyStyle = 'low project';
@endphp

@extends('layouts.app')

@section('header')
    <title>案件一覧 | {{ config('app.name') }}</title>
    <style type="text/css">
     .project-list-item dd { max-width: 100% }
    </style>
@endsection

@section('content')

	<header>
		<h1><a href="{{ route('project.index') }}">案件</a></h1>

		<div class="title-wrap">

			<dl class="project-serch clearfix">
				<dt>案件番号</dt>
				<dd><input tupe="text"></dd>

				<dt>施工期間</dt>
				<dd><input tupe="text"></dd>

				<dt>場所</dt>
				<dd><input tupe="text"></dd>

				<dt>フリーワード</dt>
				<dd><input tupe="text"></dd>
			</dl>

			<div class="button-s">検索</div>
		</div>
	</header>

	<!--project-wrapper-->
	<section class="project-wrapper">
        <h2>物件 ＞ {{ sprintf('%08d', $model->id) }}</h2>

		<ul class="project-list clearfix">
            <li>
			    <div class="project-list-item">
				    <dl class="clearfix">

					    <dt class="icon-s"></dt>
					    <dd>
                            物件・現場名
                            &nbsp;
                            {{ $model->location_name }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            工事件名
                            &nbsp;
                            {{ $model->project_name }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            住所
                            &nbsp;
                            {{ $model->location_zip }}
                            {{ array_get($model,'locationPref.name') }}
                            {{ $model->location_town }}
                            {{ $model->location_street }}
                            {{ $model->location_home }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            電話番号
                            &nbsp;
                            {{ $model->location_tel }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            新装/改築
                            &nbsp;
                            {{ $model->renovate_flg ? "新装" : "改築" }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            敷地面積
                            &nbsp;
                            {{ $model->land_area }}平米
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            建物面積（延べ床面積）
                            &nbsp;
                            {{ $model->floor_area }}平米
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            階数
                            &nbsp;
                            {{ $model->floor_level }}階
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            用途
                            &nbsp;
                            {{ $model->usage }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            着工
                            &nbsp;
                            {{ $model->start_at }}
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            竣工
                            &nbsp;
                            {{ $model->finish_at }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            オープン予定日
                            &nbsp;
                            {{ $model->open_at }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            工事会社
                            &nbsp;
                            {{ ($c = $model->contractor) ? $c->name : "未定" }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            工事に伴う特記事項
                            &nbsp;
                            {{ $model->note }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            進捗状況
                            &nbsp;
                            {{ ($p = $model->progress) ? $p->name : null }}
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            進捗特記事項
                            &nbsp;
                            {{ $model->comment }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            現場担当者氏名
                            &nbsp;
                            {{ $model->staff_name }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            担当者役職
                            &nbsp;
                            {{ $model->staff_position }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            担当者携帯電話番号
                            &nbsp;
                            {{ $model->staff_tel }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            担当者メールアドレス
                            &nbsp;
                            {{ $model->staff_email }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社名称
                            &nbsp;
                            {{ $model->maintainer_company }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社住所
                            &nbsp;
                            {{ $model->maintainer_zip }}
                            {{ array_get($model,'maintainerPref.name') }}
                            {{ $model->maintainer_town }}
                            {{ $model->maintainer_street }}
                            {{ $model->maintainer_home }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社電話番号
                            &nbsp;
                            {{ $model->maintainer_tel }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社担当者氏名
                            &nbsp;
                            {{ $model->maintainer_person }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社担当者役職
                            &nbsp;
                            {{ $model->maintainer_position }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋名称
                            &nbsp;
                            {{ $model->realtor_company }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋住所
                            &nbsp;
                            {{ $model->realtor_zip }}
                            {{ array_get($model,'maintainerPref.name') }}
                            {{ $model->realtor_town }}
                            {{ $model->realtor_street }}
                            {{ $model->realtor_home }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋電話番号
                            &nbsp;
                            {{ $model->realtor_tel }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋担当者氏名
                            &nbsp;
                            {{ $model->realtor_person }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋担当者役職
                            &nbsp;
                            {{ $model->realtor_position }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管轄消防署名
                            &nbsp;
                            {{ $model->firehouse_name }}
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            管轄消防署担当者名
                            &nbsp;
                            {{ $model->firehouse_person }}
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            管轄消防署電話番号
                            &nbsp;
                            {{ $model->firehouse_tel }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管轄警察署名
                            &nbsp;
                            {{ $model->police_name }}
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            管轄警察署担当者
                            &nbsp;
                            {{ $model->police_person }}
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            管轄警察署電話番号
                            &nbsp;
                            {{ $model->police_tel }}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            <i style="color:black"><a href="{{ route('estate.edit',['id'=>$model->id]) }}">編集ボタン</a></i>
                        </dd>

                    </dl>
                </div>
            </li>

            <li>
                <p>
                    緊急時連絡先電話番号
                    &nbsp;
                    {{ $model->sos_tel }}
                </p>
                <p>
                    現場責任者
                    &nbsp;
                    {{ $model->chief }}
                </p>
                <p>
                    現場副責任者
                    &nbsp;
                    {{ $model->assistant }}
                </p>
            </li>

            <li>
                工種別責任者
                &nbsp;
                @foreach($model->jobs as $job)
                    <p>
                        <a href="{{ route('estate-job.edit',['id'=>$job->id]) }}">
                            Type: {{ DB::table('job_types')->find($job->job_type_id)->name }}<br>
                            Name: {{ $job->chief }}<br>
                            Tel: {{ $job->chief_tel }}
                        </a>
                    </p>
                @endforeach
                <a href="{{ route('estate-job.create',['id'=>$model->id]) }}">+</a>
            </li>

            <li>
                病院
                &nbsp;
                @foreach($model->hospitals as $hospital)
                    <p>
                        <a href="{{ route('estate-hospital.edit',['id'=>$hospital->id]) }}">
                            Name: {{ $hospital->name }}<br>
                            Tel: {{ $hospital->tel }}
                        </a>
                    </p>
                @endforeach
                <a href="{{ route('estate-hospital.create',['id'=>$model->id]) }}">+</a>
            </li>

		</ul>

	</section>
	<!--/project-wrapper-->

        </div>
        <!--/container-->

@endsection
