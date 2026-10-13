@component('mail::message')

{!! $message !!}

Thanks,<br>
{{ $sender->name }}<br>
{{ $sender->email }}<br>
{{ format_phone((string) $sender->phone) }}
@endcomponent
