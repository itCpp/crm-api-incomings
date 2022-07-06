<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Трафик</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
</head>

<body>

    <div class="mt-3 mb-3 text-center">
        <h1>{{ $name }}</h1>
        <h5>{{ mb_convert_case($month, MB_CASE_TITLE, 'UTF-8') }} {{ now()->format('Y') }}</h5>
    </div>

    @if ($next or $prev)

        <div class="mb-3 d-flex align-items-center justify-content-center">
            {!! $prev !!}
            {!! $next !!}
        </div>

    @endif

    <div style="width: 100%; max-width: 1000px;" class="mx-auto pb-4 d-flex align-items-center">

        <div class="flex-grow-1 me-1">
            <div class="progress">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $good }}%"
                    aria-valuenow="{{ $good }}" aria-valuemin="0" aria-valuemax="100"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $bad }}%"
                    aria-valuenow="{{ $bad }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <div class="ms-1">
            {{ \App\Http\Controllers\Controller::formatBytes($sum) }}
            @if ($traffic > 0)
                /
                {{ \App\Http\Controllers\Controller::formatBytes($traffic) }}
            @endif
        </div>

        <div class="ms-1" title="Текущая скорость" style="cursor: default;">
            <span class="badge bg-{{ $bad > 0 ? 'danger' : 'success' }}">{{ $limit }}{{ ((int) $limit == $limit) ? " b" : "" }}</span>
        </div>

    </div>

    <div class="d-flex justify-content-center my-5 align-items-end"
        style="height: 300px; border-bottom: 1px solid #eee;">

        @foreach ($rows as $row)
            <div class="mx-1 bg-success text-center position-relative d-flex align-items-start"
                style="height: {{ $row['percent'] }}%; width: 30px; border-top-left-radius: 0.25rem; border-top-right-radius: 0.25rem;">

                @if ($row['traf'] > 0)
                    <div style="position: absolute; font-size: 70%; transform: rotate(270deg); transform-origin: top left; width: 65px; left: 7px; top: -5px; cursor: default;"
                        class="text-nowrap d-flex">{!! $row['traffic'] !!}</div>
                @endif

                <div style="position: absolute; bottom: -30px; left: 7px; cursor: default; opacity: {{ $row['traf'] > 0 ? '1' : '0.3' }};"
                    title="{!! now()->create($row['date'])->format('d.m.Y') !!}">{!! now()->create($row['date'])->format('d') !!}</div>
            </div>
        @endforeach
    </div>

</body>

</html>
