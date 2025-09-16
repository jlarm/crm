@if($getRecord())
    @php
        $record = $getRecord();
        $opened = $record->wasOpened() ? '✅ Opened' : '❌ Not opened';
        $clicked = $record->wasClicked() ? '✅ Clicked' : '❌ Not clicked';
        $bounced = $record->wasBounced() ? '⚠️ Bounced' : '✅ Delivered';
        $openCount = $record->openCount();
        $clickCount = $record->clickCount();
    @endphp

    <div class="space-y-2">
        <div><strong>Status:</strong> {!! $bounced !!}</div>
        <div><strong>Opens:</strong> {!! $opened !!}
            @if($openCount > 1)
                <span class="text-sm text-gray-600">({{ $openCount }} times)</span>
            @endif
        </div>
        <div><strong>Clicks:</strong> {!! $clicked !!}
            @if($clickCount > 1)
                <span class="text-sm text-gray-600">({{ $clickCount }} times)</span>
            @endif
        </div>
    </div>
@else
    <p class="text-gray-500">No tracking data available.</p>
@endif