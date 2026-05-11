<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee schedule</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .header { text-align: center; margin-bottom: 10px; }
        .school-name { font-size: 17px; font-weight: 800; margin-bottom: 2px; }
        .school-address { font-size: 9px; color: #333; margin-bottom: 4px; }
        .dept-title { font-size: 13px; font-weight: 800; margin-bottom: 3px; text-transform: uppercase; }
        .term-line { font-size: 10px; font-weight: 700; margin-bottom: 8px; }
        .meta { font-size: 8px; color: #555; margin-top: 3px; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.grid th, table.grid td { border: 1px solid #000; padding: 6px 6px; vertical-align: middle; }
        table.grid thead th { background: #f6f6f6; text-align: center; font-weight: 800; }
        .c { text-align: center; }
        .l { text-align: left; }
        .times .t { display: block; }
    </style>
</head>
<body>
    @php
        $settings = $exportSettings ?? [];
        $deptKey = $selectedDepartmentKey ?: null;
        $deptTitle = $deptKey && isset($departmentTitles[$deptKey])
            ? $departmentTitles[$deptKey]
            : 'All Departments';

        $grouped = $entries;
    @endphp

    <div class="header">
        <div class="school-name">{{ $settings['school_name'] ?? 'Colegio de Sta. Teresa De Avila' }}</div>
        <div class="school-address">{{ $settings['school_address'] ?? '' }}</div>
        <div class="dept-title">{{ $deptTitle }}</div>
        <div class="term-line">{{ $settings['semester_label'] ?? '1st Semester' }} | S.Y. {{ $settings['school_year'] ?? now()->format('Y').'-'.now()->addYear()->format('Y') }}</div>
        <div class="meta">Generated {{ $generatedAt->format('F j, Y g:i A') }}</div>
    </div>

    <table class="grid">
        <thead>
            <tr>
                <th style="width:10%;">CODE</th>
                <th style="width:26%;">COURSE DESCRIPTION</th>
                <th style="width:10%;">DAY/S</th>
                <th style="width:18%;">TIME</th>
                <th style="width:12%;">ROOM</th>
                <th style="width:24%;">FACULTY</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grouped as $row)
                @php
                    $times = collect($row->resolvedTimeBlocks())->map(function ($block) {
                        return \Carbon\Carbon::parse($block['time_start'])->format('g:i A') . '-' . \Carbon\Carbon::parse($block['time_end'])->format('g:i A');
                    })->values();
                @endphp
                <tr>
                    <td class="c">{{ $row->course?->code ?: '-' }}</td>
                    <td class="l">{{ $row->course?->name ?: '-' }}</td>
                    <td class="c">{{ $dayShort((int) $row->day_of_week) }}</td>
                    <td class="c times">
                        @foreach($times as $t)
                            <span class="t">{{ $t }}</span>
                        @endforeach
                    </td>
                    <td class="c">{{ $row->room }}</td>
                    <td class="l">{{ $row->employee->name }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="c">No entries.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
