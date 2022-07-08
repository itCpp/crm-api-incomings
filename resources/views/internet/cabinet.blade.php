@extends('internet.index')

@section("body")

<div class="mt-3 mb-3 text-center">
    <h1 class="mb-0">{{ $row->title ?? $row->name }}</h1>
    <small>{{ $period }}</small>
</div>

<div class="w-100 mx-auto pb-4" style="max-width: 1000px;">

    <div class="d-flex align-items-center">

        <div class="flex-grow-1 me-1">

            <div class="progress">

                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress['good'] ?? 0 }}%" aria-valuenow="{{ $progress['good'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100" title="Выделенный лимит"></div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $progress['add'] ?? 0 }}%" aria-valuenow="{{ $progress['add'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100" title="Добавленный трафик"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $progress['bad'] ?? 0 }}%" aria-valuenow="{{ $progress['bad'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100" title="Перерасход"></div>

            </div>

        </div>

        <div class="ms-1">
            <span id="traffic-string">{{ $row->traffic_format }}</span>
            <span id="limit-string">{{ $row->traffic_limit ? " / " . $row->traffic_limit : ""}}</span>
        </div>

        <div class="ms-1" title="Текущая скорость" style="cursor: default;">
            <span class="badge bg-{{ ($progress['bad'] ?? 0) > 0 ? 'danger' : 'success' }}">{{ ($progress['bad'] ?? 0) > 0 ? $row->limit_down : $row->limit_up }}</span>
        </div>

    </div>

    <div class="w-100 d-flex justify-content-end">
        <span>Обнуление: <b>{{ $stop }}</b></span>
    </div>

</div>

<div class="mx-auto d-flex justify-content-center my-5 align-items-end" style="height: 300px; max-width: 1100px; border-bottom: 1px solid #eee;">

    @foreach ($days as $day)
    <div class="mx-1 bg-primary text-center position-relative d-flex align-items-start" style="height: {{ $day['percent'] }}%; width: 30px; border-top-left-radius: 0.25rem; border-top-right-radius: 0.25rem;">

        @if ($day['traffic'] > 0)
            <div style="position: absolute; font-size: 70%; transform: rotate(270deg); transform-origin: top left; width: 65px; left: 7px; top: -5px; cursor: default;" class="text-nowrap d-flex">{!! $day['format'] !!}</div>
        @endif

        <div style="position: absolute; bottom: -30px; left: 7px; cursor: default; opacity: {{ $day['traffic'] > 0 ? '1' : '0.3' }};" title="{!! now()->create($day['date'])->format('d.m.Y') !!}">{!! now()->create($day['date'])->format('d') !!}</div>
    </div>
    @endforeach

</div>

@if ($links)

    <hr />

    <div class="mx-auto text-center"><b class="text-center">История</b></div>

    <div class="mt-2 d-flex justify-content-center">
        {!! $links !!}
    </div>

@endif

{{-- @php dump($links, $progress, $row->toArray()) @endphp --}}

<script>
    // $(function () {
    //     const traffic = $('#traffic-string');
    //     const limit = $('#traffic-string');

    //     traffic.text(traffic.text().replace(".00", ""));
    //     limit.text(limit.text().replace(".00", ""));
    // });
</script>

@endsection