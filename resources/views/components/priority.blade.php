@props(['p'])
@php $pr = \App\Support\Itil::PRIORITIES[$p] ?? \App\Support\Itil::PRIORITIES[4]; @endphp
<span class="chip {{ $pr['class'] }}">{{ $pr['label'] }}</span>
