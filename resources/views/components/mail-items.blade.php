@props(['items'])

<table cellpadding="0" cellspacing="0" style="width:100%;margin:8px 0 12px;font-size:14px;">
    @foreach ($items as $item)
        <tr>
            <td style="padding:8px 0;border-bottom:1px solid #f5f5f4;">{{ $item->animal_name }} <span style="color:#78716c;">&times; {{ $item->quantity }}</span></td>
            <td align="right" style="padding:8px 0;border-bottom:1px solid #f5f5f4;white-space:nowrap;"><strong>{{ inr($item->lineTotal(), 2) }}</strong></td>
        </tr>
    @endforeach
    <tr>
        <td style="padding:10px 0;"><strong>{{ __('Total') }}</strong></td>
        <td align="right" style="padding:10px 0;white-space:nowrap;"><strong style="font-size:16px;">{{ inr($items->sum(fn ($i) => $i->lineTotal()), 2) }}</strong></td>
    </tr>
</table>
