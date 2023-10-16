<div>
    @if(count($getRecord()->progresses) > 0)
    <span class="text-sm">{{ $getRecord()->progresses->sortByDesc('date')->first()->date->format('F d, Y') ?? '' }}</span><br />
{{--    <span class="text-sm text-gray-500">{{ Str::words($getRecord()->progresses->sortByDesc('date')->first()->details, 10) ?? '' }}</span>--}}
        @else
        <span class="text-sm">-</span>
    @endif
{{--    {{ $getState() }}--}}
</div>
