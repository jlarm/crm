@component('mail::message')
***Dealership:*** {{ $dealership->name }}

{!! $message !!}

<x-mail::button :url="$link">
    View Dealership
</x-mail::button>

Thanks,<br>
{{ $sender->name }}
@endcomponent
