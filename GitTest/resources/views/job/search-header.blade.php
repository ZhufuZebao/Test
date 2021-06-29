<div class="alert alert-dark">
    {!! Form::text('keyword', request()->input('keyword'), ['size'=>32,'max'=>72]) !!}
    <button type="submit" class="btn-primary">仕事を探す</button>

    <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#jobSearchModal">
        
    </button>

    <a href="#jobSearchModal" class="btn btn-default" data-toggle="collapse">詳細検索</a>

</div>

<!-- Modal -->
<div class="collapse" id="jobSearchModal" tabindex="-1" role="dialog" aria-labelledby="jobSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jobSearchModalLabel">
                    種別
                </h5>
            </div>
            <div class="modal-body">
                @foreach(\App\Skill::all() as $key => $skill)
                    {{ Form::checkbox('skill_id[]',$skill->id, ($skill->id == $query->skill_id),['id'=>'skill_id_'.$key]) }}
                    <label for="skill_id_{{ $key }}">{{ $skill->name }}</label>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">仕事を探す</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">すべてクリア</button>
            </div>
        </div>
    </div>
</div>
