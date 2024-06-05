@component('mail::message')

{!! $message !!}

Thanks,<br>
{{ $sender->name }}<br>
{{ $sender->email }}<br>
{{ $sender->phone }}
@endcomponent
