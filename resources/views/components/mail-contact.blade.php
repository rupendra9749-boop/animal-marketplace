@props(['user', 'label' => null])

{{-- A person's contact details, for the emails. --}}
<table cellpadding="0" cellspacing="0" style="margin:8px 0 16px;background:#fafaf9;border:1px solid #e7e5e4;border-radius:12px;width:100%;">
    <tr><td style="padding:12px 16px;font-size:14px;line-height:1.6;">
        @if ($label)<div style="font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:#78716c;">{{ $label }}</div>@endif
        <strong>{{ $user->name }}</strong><br>
        @if ($user->phone)&#128222; {{ $user->phone }}<br>@endif
        &#9993;&#65039; {{ $user->email }}<br>
        @if ($user->city)&#128205; {{ __($user->city) }}@if ($user->state), {{ __($user->state) }}@endif @endif
    </td></tr>
</table>
