<div class="alert alert-dark">
    状態
    {{ ($s = $model->status) ? $s->name : null }}
    <button type="submit" name="status_id" class="btn-secondary" value="{{ \App\JobVacancyStatus::TEMPORAL }}">一時保存</button>
    <button type="submit" name="status_id" class="btn-info" value="{{ \App\JobVacancyStatus::PUBLISH }}">公開</button>

    @if($model->id)
        <p class="text-right">
            <button type="submit" name="status_id" class="btn-danger" value="{{ \App\JobVacancyStatus::CLOSED }}">停止</button>
        </p>
    @endif
</div>
