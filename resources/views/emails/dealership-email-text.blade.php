@if ($note)
{!! $note['text'] !!} {!! $note['url'] ?? '' !!}

@endif
{!! $bodyText !!}
@if ($signature)

--
{!! $signature['name'] !!}
@if (! empty($signature['title']))
{!! $signature['title'] !!}
@endif
{!! $signature['company'] !!}
@if ($signature['phone'])
{!! $signature['phone'] !!}
@endif
{!! $signature['email'] !!}
@endif
