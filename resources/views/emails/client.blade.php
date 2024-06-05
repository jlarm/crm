@component('mail::message')

{!! $message !!}

Thanks,<br>
{{ $sender->name }}
@endcomponent
