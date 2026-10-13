@if ($note)
{!! $note['text'] !!} {!! $note['url'] ?? '' !!}

@endif
{!! $bodyText !!}
@if ($signature)

--
{!! $signature['name'] !!}
{!! $signature['company'] !!}
@if ($signature['phone'])
{!! $signature['phone'] !!}
@endif
{!! $signature['email'] !!}
@endif
