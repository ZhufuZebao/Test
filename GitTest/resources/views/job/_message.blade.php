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
	@if($model->read_at)
	    <small>既読</small>
	@elseif(Auth::id() == $model->sender_id)
	    <small>自分</small>
	@else
	    <small>未読</small>
	@endif
    </div>

    <div class="col-md-6">
	    {{ $model->content }}
    </div>

    <div class="col-md-3 text-right">
	    <a href="{{ route('job-message.show',['id'=>$model->id]) }}">
	        {{ $model->created_at->format('Y年m月d日 H:i') }}
	    </a>
    </div>
</div>
