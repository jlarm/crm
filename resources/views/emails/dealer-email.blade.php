@component('mail::message')

{!! $message !!}

Thanks,<br>
{{ $user->name }}<br>
{{ $user->email }}<br>
@if($user->phone)
{{ $user->phone }}<br>
@endif
Automotive Risk Management Partners
@endcomponent
