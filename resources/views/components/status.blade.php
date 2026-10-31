@props(['status'])
<span class="chip {{ \App\Support\Itil::statusColor($status) }}">{{ \App\Support\Itil::statusLabel($status) }}</span>
