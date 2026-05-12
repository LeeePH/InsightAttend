<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My schedule</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .header { text-align: center; margin-bottom: 12px; }
        .header img { width: 54px; height: 54px; border-radius: 12px; object-fit: cover; margin-bottom: 6px; }
        .school-name { font-size: 18px; font-weight: 800; margin-bottom: 2px; }
        .report-name { font-size: 13px; font-weight: 700; margin-bottom: 3px; }
        .meta { font-size: 9px; color: #444; }
        .band { background: #8b4513; color: #fff; text-align: center; font-weight: 800; padding: 7px 8px; margin-top: 10px; }
        .band-sub { background: #f4ede6; color: #2d1708; text-align: center; font-weight: 700; padding: 6px 8px; border: 1px solid #8b4513; border-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 7px 6px; vertical-align: top; }
        th { background: #f6f6f6; text-align: center; font-weight: 800; }
        .center { text-align: center; }
        .times span { display: block; }
        .times span + span { margin-top: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('assets/images/logo-sm.jpg') }}" alt="School logo">
        <div class="school-name">Colegio de Sta. Teresa De Avila</div>
        <div class="report-name">EMPLOYEE SCHEDULE</div>
        <div class="meta">{{ $employee->name }} • Generated {{ $generatedAt->format('F j, Y g:i A') }}</div>
    </div>

    <div class="band">WEEKLY CLASS-STYLE TIMETABLE</div>
    <div class="band-sub">Prepared employee schedule for printing and PDF export</div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">CODE</th>
                <th>COURSE DESCRIPTION</th>
                <th style="width: 12%;">DAY/S</th>
                <th style="width: 22%;">TIME</th>
                <th style="width: 10%;">ROOM</th>
                <th style="width: 12%;">SECTION</th>
                <th style="width: 16%;">FACULTY</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($scheduleRows as $row)
                <tr>
                    <td class="center">{{ $row['course_code'] ?: '-' }}</td>
                    <td>{{ $row['course_name'] ?: '-' }}</td>
                    <td class="center">{{ $dayShort($row['day_of_week']) }}</td>
                    <td class="center times">
                        @foreach ($row['times'] as $time)
                            <span>{{ $time['display'] }}</span>
                        @endforeach
                    </td>
                    <td class="center">{{ $row['room'] }}</td>
                    <td class="center">{{ $row['section_label'] ?: '—' }}</td>
                    <td class="center">{{ $employee->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center">No entries.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
