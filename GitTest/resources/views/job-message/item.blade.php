{{-- $model: JobOfferMessage --}}
<hr>
<div class="row small">
    <div class="col-md-3">
	<a href="{{ route('worker.show',['id'=>$model->sender_id]) }}">
	    @if($model->sender->photo)
		<img src="{{ route('worker.photo',['id'=>$model->sender_id]) }}" width="32">
	    @else
		<img src="{{ url('/') }}/images/user.png" width="32">
	    @endif
	    <small>{{ $model->sender->name }}</small>
	</a>
    </div>
    <div class="col-md-1">
	@if($model->read_at)
	    <small>既読</small>
	@elseif(Auth::id() == $model->sender_id)
	    <small>自分</small>
	@else
	    <small>未読</small>
	@endif
    </div>
    <div class="col-md-4">
	{{ $model->vacancy->name }}
    </div>
    <div class="col-md-4 text-right">
	<a href="{{ route('job-message.show',['id'=>$model->id]) }}">
	    {{ $model->created_at->format('Y年m月d日 H:i') }}
	</a>
    </div>
    <div class="col-md-4">
    </div>
    <div class="col-md-6">
	<a href="{{ route('job.messages',['id'=>$model->vacancy_id,'worker_id'=>$model->offer->worker_id]) }}">
	    {{ \Illuminate\Support\Str::limit($model->content, 64) }}
	</a>
    </div>
</div>
