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

        <h2>物件 ＞ 新規作成</h2>

        {!! Form::open(['method'=>'post']) !!}
		<ul class="project-list clearfix">
            <li>
			    <div class="project-list-item">
				    <dl class="clearfix">

					    <dt class="icon-s"></dt>
					    <dd>
                            物件・現場名
                            &nbsp;
                            {!! Form::text('location_name', $model->location_name, ['placeholder'=>"物件・現場名"]) !!}
                            @if ($errors->has('location_name'))
                                <div class="error">{{ $errors->first('location_name') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            工事件名
                            &nbsp;
                            {!! Form::text('project_name', $model->project_name, ['placeholder'=>"工事件名"]) !!}
                            @if ($errors->has('project_name'))
                                <div class="error">{{ $errors->first('project_name') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            郵便番号
                            &nbsp;
                            {!! Form::text('location_zip', $model->location_zip, ['placeholder'=>"1001234"]) !!}
                            <button type="submit" name="zip2addr" value="location_">住所検索</button>
                            @if ($errors->has('location_zip'))
                                <div class="error">{{ $errors->first('location_zip') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            <?php
                            $prefs = [];
                            foreach(\App\Pref::all() as $pref)
                            {
                            $prefs[$pref->id] = $pref->name;
                            }
                            ?>
                            都道府県
                            &nbsp;
                            {!! Form::select('location_pref_id', $prefs, $model->location_pref_id) !!}
                            @if ($errors->has('location_pref_id'))
                                <div class="error">{{ $errors->first('location_pref_id') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            市区町村
                            &nbsp;
                            {!! Form::text('location_town', $model->location_town, ['placeholder'=>""]) !!}
                            @if ($errors->has('location_town'))
                                <div class="error">{{ $errors->first('location_town') }}</div>
                            @endif

                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            番地
                            &nbsp;
                            {!! Form::text('location_street', $model->location_street, ['placeholder'=>""]) !!}
                            @if ($errors->has('location_street'))
                                <div class="error">{{ $errors->first('location_street') }}</div>
                            @endif

                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            建物名
                            &nbsp;
                            {!! Form::text('location_house', $model->location_house, ['placeholder'=>""]) !!}

                            @if ($errors->has('location_house'))
                                <div class="error">{{ $errors->first('location_house') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            電話番号
                            &nbsp;
                            {!! Form::text('location_tel', $model->location_tel, ['placeholder'=>""]) !!}
                            @if ($errors->has('location_tel'))
                                <div class="error">{{ $errors->first('location_tel') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            新装/改築
                            &nbsp;
                            {!! Form::radio('renovate_flg', 1,  $model->renovate_flg, ['id'=>'apple']) !!}
                            <label for="apple">新装</label>
                            {!! Form::radio('renovate_flg', 0, !$model->renovate_flg, ['id'=>'bacon']) !!}
                            <label for="bacon">改築</label>
                            @if ($errors->has('renovate_flg'))
                                <div class="error">{{ $errors->first('renovate_flg') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            敷地面積
                            &nbsp;
                            {!! Form::text('land_area', $model->floor_area, ['placeholder'=>""]) !!}平米

                            @if ($errors->has('land_area'))
                                <div class="error">{{ $errors->first('land_area') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            建物面積（延べ床面積）
                            &nbsp;
                            {!! Form::text('floor_area', $model->floor_area, ['placeholder'=>""]) !!}平米

                            @if ($errors->has('floor_area'))
                                <div class="error">{{ $errors->first('floor_area') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            階数
                            &nbsp;
                            {!! Form::text('floor_level', $model->floor_level, ['placeholder'=>""]) !!}階

                            @if ($errors->has('floor_level'))
                                <div class="error">{{ $errors->first('floor_level') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            用途
                            &nbsp;
                            {!! Form::text('usage', $model->usage, ['placeholder'=>""]) !!}

                            @if ($errors->has('usage'))
                                <div class="error">{{ $errors->first('usage') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            着工
                            &nbsp;
                            {!! Form::text('start_at', $model->start_at, ['placeholder'=>""]) !!}

                            @if ($errors->has('start_at'))
                                <div class="error">{{ $errors->first('start_at') }}</div>
                            @endif
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            竣工
                            &nbsp;
                            {!! Form::text('finish_at', $model->finish_at, ['placeholder'=>""]) !!}

                            @if ($errors->has('usage'))
                                <div class="error">{{ $errors->first('finish_at') }}</div>
                            @endif
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            オープン予定日
                            &nbsp;
                            {!! Form::text('open_at', $model->open_at, ['placeholder'=>""]) !!}

                            @if ($errors->has('open_at'))
                                <div class="error">{{ $errors->first('open_at') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            工事会社
                            &nbsp;
                            <span>{{ ($c = $model->contractor) ? $c->name : null }}</span>
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            工事に伴う特記事項
                            &nbsp;
                            {!! Form::text('note', $model->note, ['placeholder'=>""]) !!}

                            @if ($errors->has('note'))
                                <div class="error">{{ $errors->first('note') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            進捗状況
                            &nbsp;
                            <?php
                            $progs = [];
                            foreach(\App\EstateProgress::all() as $prog)
                            {
                            $progs[$prog->id] = $prog->name;
                            }
                            ?>
                            {!! Form::select('progress_id', $progs, $model->progress_id) !!}
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            進捗特記事項
                            &nbsp;
                            {!! Form::text('comment', $model->comment, ['placeholder'=>""]) !!}

                            @if ($errors->has('comment'))
                                <div class="error">{{ $errors->first('comment') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dt class="icon-s"></dt>
					    <dd>
                            現場担当者氏名
                            &nbsp;
                            {!! Form::text('staff_name', $model->staff_name, ['placeholder'=>""]) !!}

                            @if ($errors->has('staff_name'))
                                <div class="error">{{ $errors->first('staff_name') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            担当者役職
                            &nbsp;
                            {!! Form::text('staff_position', $model->staff_position, ['placeholder'=>""]) !!}

                            @if ($errors->has('staff_position'))
                                <div class="error">{{ $errors->first('staff_position') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            担当者携帯電話番号
                            &nbsp;
                            {!! Form::text('staff_tel', $model->staff_tel, ['placeholder'=>""]) !!}

                            @if ($errors->has('staff_tel'))
                                <div class="error">{{ $errors->first('staff_tel') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            担当者メールアドレス
                            &nbsp;
                            {!! Form::text('staff_email', $model->staff_email, ['placeholder'=>""]) !!}

                            @if ($errors->has('staff_email'))
                                <div class="error">{{ $errors->first('staff_email') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社名称
                            &nbsp;
                            {!! Form::text('maintainer_company', $model->maintainer_company, ['placeholder'=>""]) !!}

                            @if ($errors->has('maintainer_company'))
                                <div class="error">{{ $errors->first('maintainer_company') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社住所
                            &nbsp;
                            {!! Form::text('maintainer_zip', $model->maintainer_zip, ['placeholder'=>""]) !!}
                            <button type="submit" name="zip2addr" value="maintainer_">住所検索</button>
                            @if ($errors->has('maintainer_zip'))
                                <div class="error">{{ $errors->first('maintainer_zip') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社住所 都道府県
                            &nbsp;
                            {!! Form::select('maintainer_pref_id', $prefs, $model->maintainer_pref_id) !!}
                            @if ($errors->has('maintainer_pref_id'))
                                <div class="error">{{ $errors->first('maintainer_pref_id') }}</div>
                            @endif
                        </dd>


					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社住所 市区町村
                            &nbsp;
                            {!! Form::text('maintainer_town', $model->maintainer_town, ['placeholder'=>""]) !!}
                            @if ($errors->has('maintainer_town'))
                                <div class="error">{{ $errors->first('maintainer_town') }}</div>
                            @endif

                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社住所 
                            番地
                            &nbsp;
                            {!! Form::text('maintainer_street', $model->location_street, ['placeholder'=>""]) !!}
                            @if ($errors->has('location_street'))
                                <div class="error">{{ $errors->first('location_street') }}</div>
                            @endif

                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社住所 
                            建物名
                            &nbsp;
                            {!! Form::text('maintainer_house', $model->maintainer_house, ['placeholder'=>""]) !!}

                            @if ($errors->has('maintainer_house'))
                                <div class="error">{{ $errors->first('maintainer_house') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社電話番号
                            &nbsp;
                            {!! Form::text('maintainer_tel', $model->maintainer_tel, ['placeholder'=>""]) !!}

                            @if ($errors->has('maintainer_tel'))
                                <div class="error">{{ $errors->first('maintainer_tel') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社担当者氏名
                            &nbsp;
                            {!! Form::text('maintainer_person', $model->maintainer_person, ['placeholder'=>""]) !!}

                            @if ($errors->has('maintainer_person'))
                                <div class="error">{{ $errors->first('maintainer_person') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管理会社担当者役職
                            &nbsp;
                            {!! Form::text('maintainer_position', $model->maintainer_position, ['placeholder'=>""]) !!}

                            @if ($errors->has('maintainer_position'))
                                <div class="error">{{ $errors->first('maintainer_position') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋名称
                            &nbsp;
                            {!! Form::text('realtor_company', $model->realtor_name, ['placeholder'=>""]) !!}

                            @if ($errors->has('realtor_company'))
                                <div class="error">{{ $errors->first('realtor_company') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋住所
                            &nbsp;
                            {!! Form::text('realtor_zip', $model->realtor_zip, ['placeholder'=>""]) !!}
                            <button type="submit" name="zip2addr" value="realtor_">住所検索</button>

                            @if ($errors->has('realtor_zip'))
                                <div class="error">{{ $errors->first('realtor_zip') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋 住所 都道府県
                            &nbsp;
                            {!! Form::select('realtor_pref_id', $prefs, $model->realtor_pref_id) !!}
                            @if ($errors->has('realtor_pref_id'))
                                <div class="error">{{ $errors->first('realtor_pref_id') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋 住所 市区町村
                            &nbsp;
                            {!! Form::text('realtor_town', $model->realtor_town, ['placeholder'=>""]) !!}
                            @if ($errors->has('realtor_town'))
                                <div class="error">{{ $errors->first('realtor_town') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋 住所 建物
                            &nbsp;
                            {!! Form::text('realtor_house', $model->realtor_house) !!}
                            @if ($errors->has('realtor_house'))
                                <div class="error">{{ $errors->first('realtor_house') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋電話番号
                            &nbsp;
                            {!! Form::text('realtor_tel', $model->realtor_tel, ['placeholder'=>""]) !!}

                            @if ($errors->has('realtor_tel'))
                                <div class="error">{{ $errors->first('realtor_tel') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋担当者氏名
                            &nbsp;
                            {!! Form::text('realtor_person', $model->realtor_person, ['placeholder'=>""]) !!}

                            @if ($errors->has('realtor_person'))
                                <div class="error">{{ $errors->first('realtor_person') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            不動産屋担当者役職
                            &nbsp;
                            {!! Form::text('realtor_position', $model->realtor_position, ['placeholder'=>""]) !!}

                            @if ($errors->has('realtor_position'))
                                <div class="error">{{ $errors->first('realtor_position') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管轄消防署名
                            &nbsp;
                            {!! Form::text('firehouse_name', $model->firehouse_name, ['placeholder'=>""]) !!}

                            @if ($errors->has('firehouse_name'))
                                <div class="error">{{ $errors->first('firehouse_name') }}</div>
                            @endif
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            管轄消防署担当者名
                            &nbsp;
                            {!! Form::text('firehouse_person', $model->firehouse_person, ['placeholder'=>""]) !!}

                            @if ($errors->has('firehouse_person'))
                                <div class="error">{{ $errors->first('firehouse_person') }}</div>
                            @endif
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            管轄消防署電話番号
                            &nbsp;
                            {!! Form::text('firehouse_tel', $model->firehouse_tel, ['placeholder'=>""]) !!}
                            @if ($errors->has('firehouse_tel'))
                                <div class="error">{{ $errors->first('firehouse_tel') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            管轄警察署名
                            &nbsp;
                            {!! Form::text('police_name', $model->police_name, ['placeholder'=>""]) !!}
                            @if ($errors->has('police_name'))
                                <div class="error">{{ $errors->first('police_name') }}</div>
                            @endif
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            管轄警察署担当者
                            &nbsp;
                            {!! Form::text('police_person', $model->police_person, ['placeholder'=>""]) !!}
                            @if ($errors->has('police_person'))
                                <div class="error">{{ $errors->first('police_person') }}</div>
                            @endif
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            管轄警察署電話番号
                            &nbsp;
                            {!! Form::text('police_tel', $model->police_tel, ['placeholder'=>""]) !!}
                            @if ($errors->has('police_tel'))
                                <div class="error">{{ $errors->first('police_tel') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            緊急時連絡先電話番号
                            &nbsp;
                            {!! Form::text('sos_tel', $model->sos_tel, ['placeholder'=>""]) !!}
                            @if ($errors->has('sos_tel'))
                                <div class="error">{{ $errors->first('sos_tel') }}</div>
                            @endif
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            現場責任者
                            &nbsp;
                            {!! Form::text('chief', $model->chief, ['placeholder'=>""]) !!}
                            @if ($errors->has('chief'))
                                <div class="error">{{ $errors->first('chief') }}</div>
                            @endif
                        </dd>
					    <dt class="icon-s"></dt>
					    <dd>
                            現場副責任者
                            &nbsp;
                            {!! Form::text('assistant', $model->assistant, ['placeholder'=>""]) !!}
                            @if ($errors->has('assistant'))
                                <div class="error">{{ $errors->first('assistant') }}</div>
                            @endif
                        </dd>

					    <dt class="icon-s"></dt>
					    <dd>
                            
                            &nbsp;
                            {!! Form::submit("保存ボタン") !!}
                        </dd>

                    </dl>
                </div>
            </li>
            
            @if(0 < $model->id)
                <li>
                    工種別責任者
                    &nbsp;
                    @foreach($model->jobs as $job)
                        <p>
                            <a href="{{ route('estate-job.edit',['id'=>$job->id]) }}" target="_blank">
                                Type: {{ DB::table('job_types')->find($job->job_type_id)->name }}<br>
                                Name: {{ $job->chief }}<br>
                                Tel: {{ $job->chief_tel }}
                            </a>
                        </p>
                    @endforeach
                    <a href="{{ route('estate-job.create',['id'=>$model->id]) }}" target="_blank">+</a>
                </li>
                <li>
                    病院
                    &nbsp;
                    @foreach($model->hospitals as $hospital)
                        <p>
                            <a href="{{ route('estate-hospital.edit',['id'=>$hospital->id]) }}" target="_blank">
                                Name: {{ $hospital->name }}<br>
                                Tel: {{ $hospital->tel }}
                            </a>
                        </p>
                    @endforeach
                    <a href="{{ route('estate-hospital.create',['id'=>$model->id]) }}" target="_blank">+</a>
                </li>
            @endif

		</ul>
        {!! Form::close() !!}

        @if ($errors->any())
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        @endif

	</section>
	<!--/project-wrapper-->

        </div>
        <!--/container-->

@endsection
