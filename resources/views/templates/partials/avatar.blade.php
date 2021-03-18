@if(!$status->user->avatar)
    <img src="{{$status->user->getAvatarUrl()}}"
         class="avatar img-thumbnail rounded-circle mr-3"
         alt="{{$status->user->getNameOrUsername()}}">
@else
    <img src="{{$status->user->getAvatarsPath($status->user->id)
              . $status->user->avatar}}"
         class="avatar img-thumbnail rounded-circle mr-3"
         alt="{{$status->user->getNameOrUsername()}}">
@endif
