@component('mail::message')

{!! $message !!}

Thanks,<br>
{{ $sender->name }}<br>
{{ $sender->email }}<br>
@if($sender->phone){{ $sender->phone }}@endif
@endcomponent
