<!doctype html>

<html lang="ja">

<head>
    <title>簡易レポート</title>

    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <style>
        @font-face {
            font-family: 'rounded-mplus';
            src: url("file://{{base_path('resources/fonts/rounded-mplus-1c-regular.ttf')}}") format('truetype');
        }
        body {
            font-family: 'rounded-mplus', sans-serif;
        }
        tr td{
            border-left: 1px solid;
            border-top: 1px solid;
        }
        tr td:last-of-type{
            border-right: 1px solid;
        }
        .table_b:last-of-type td{
            border-bottom:1px solid;
        }
        .table_a td {
            width: 500px;
            height: 30px;
            padding: 0 10px;
        }
        .table_empty td {
            width: 500px;
            height: 30px;
            padding: 0 10px;
            border-bottom: 1px solid;
        }
        .table_b {
            page-break-inside:avoid;
        }
        .table_b td{
            padding: 10px 0;
            font-size: 16px;
            width: 500px;
            overflow: hidden;
            word-break: break-all;
            vertical-align: top;
            border-bottom:1px solid;
            height: 330px;
            max-height:330px;
        } 
        .table_b td p{
            line-height: 25px;
            padding: 0 10px;
            margin: 0px;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-all;
        }
        .table_b td p.row10p {
            max-height:250px;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 10;
        }
        .table_b td p.row11p {
            max-height:275px;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 11;
        }
        .table_b td p.row12p {
            max-height:300px;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 12;
        }
    </style>
</head>

<body>
    <div>
    <table autosize="1" cellSpacing="0" cellPadding="0" style="overflow: hidden;table-layout:fixed;">
        <thead>
            <tr class="table_a">
                <td>【作業日】</td>
                <td>{{$report_date}} ～ {{$report_date_ed}}</td>
            </tr>
            <tr class="table_a">
                <td>【報告者】</td>
                <td>{{$createName}}</td>
            </tr>
            @if (count($report_file) === 0)
            <tr class="table_empty">
            @else
            <tr class="table_a">
            @endif
                <td>【現場名】</td>
                <td>{{$placeName}}</td>
            </tr>
        </thead>
        <tbody>
        @foreach ($report_file as $file)
            <tr class="table_b">
                <td style="width:450px;height:300px;min-height: 300px; max-height: 300px;padding: 25px;text-align: center;">
                    <img src="{{$file['file_path']}}" style="max-height: 250px;max-width:400px;object-fit:contain;vertical-align: top;" />
                </td>
                <td>
                    <p>作業日:{!! $file['report_date'] !!}</p>
                    @if ($file['work_place'] && strlen($file['work_place']) > 0)
                        <p>作業箇所:{!! $file['work_place'] !!}</p>
                    @endif
                    @if ($file['weather'] && strlen($file['weather']) > 0)
                        <p >天気:{!! $file['weather'] !!}</p>
                    @endif
                    @if ($file['comment'] && strlen($file['comment']) > 0)
                        <p class="{!! $file['row_num_style'] !!}" style="overflow:hidden;">作業内容: {!!$file['comment'] !!}</p>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>

</body>

</html>
