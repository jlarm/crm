<tr>
<td class="header">
@if (trim($slot) === 'Laravel')
        <img style="width:75px;height:auto;" src="{{ asset('img/logo.png') }}" alt="">
@else
{{ $slot }}
@endif
</td>
</tr>
