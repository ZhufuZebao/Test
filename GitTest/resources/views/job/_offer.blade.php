
<div class="alert alert-dark">
    職人：
    <div class="row">
        <div class="col-md-4">
            <img src="{{ route('worker.photo',['id'=>$offer->worker_id]) }}">
        </div>
        <div class="col-md-8">
            {{ ($w = $offer->worker) ? $w->name : null }} さん
        </div>
    </div>

    へのメッセージ
    <div class="col-md-12">
    {!! Form::textarea('content', $offer->content) !!}
    </div>
</div>

<div class="alert alert-dark">
    <button type="submit" name="status_id" class="btn-info" value="{{ \App\JobVacancyStatus::PUBLISH }}">公開して指名する</button>
    または
    <button type="submit" name="status_id" class="btn-dark" value="-1">指名を中止</button>
</div>
