@component('mail::message')

{!! $message !!}

Thanks,<br>
{{ $user->name }}<br>
{{ $user->email }}<br>
@if($user->phone)
{{ $user->phone }}<br>
@endif
Automotive Risk Management Partners

@if($trackingId)
<img src="{{ route('mailgun.open-track', ['message_id' => $trackingId]) }}" width="1" height="1" style="display:none;" alt="" />
@endif
@endcomponent
