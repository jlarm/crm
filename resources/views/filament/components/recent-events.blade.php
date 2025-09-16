@if($getRecord())
    @php
        $record = $getRecord();
        $events = $record->trackingEvents()
            ->orderBy('event_timestamp', 'desc')
            ->limit(10)
            ->get();
    @endphp

    @if($events->isEmpty())
        <p class="text-gray-500">No tracking events recorded yet.</p>
    @else
        <div class="space-y-3">
            @foreach($events as $event)
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ match($event->event_type) {
                                'delivered' => 'bg-green-100 text-green-800',
                                'opened' => 'bg-blue-100 text-blue-800',
                                'clicked' => 'bg-yellow-100 text-yellow-800',
                                'bounced' => 'bg-red-100 text-red-800',
                                'complained' => 'bg-red-100 text-red-800',
                                'unsubscribed' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            } }}">
                            {{ ucfirst($event->event_type) }}
                        </span>
                        <span class="text-sm text-gray-600">
                            {{ $event->event_timestamp->format('M j, Y g:i A') }}
                        </span>
                    </div>
                    @if($event->url)
                        <div class="text-xs text-gray-500 max-w-xs truncate">
                            <a href="{{ $event->url }}" target="_blank" class="hover:text-blue-600">
                                {{ $event->url }}
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@else
    <p class="text-gray-500">No events recorded.</p>
@endif