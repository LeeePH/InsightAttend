<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schedule Preview</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5ede5;
            color: #2d1708;
        }
        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1.25rem 3rem;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1.25rem;
        }
        .actions a,
        .actions button {
            border: none;
            border-radius: 999px;
            padding: 0.8rem 1.15rem;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        .actions .primary {
            background: #8b4513;
            color: #fff;
        }
        .actions .secondary {
            background: #fff;
            color: #6b3410;
            border: 1px solid #d7b99d;
        }
        .paper {
            background: #fff;
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 22px 55px rgba(72, 42, 19, 0.12);
        }
        .school-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .school-header img {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            object-fit: cover;
        }
        .school-title {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0;
        }
        .school-subtitle,
        .meta {
            color: #7b5a45;
            margin: 0.2rem 0 0;
        }
        .band {
            background: #8b4513;
            color: #fff;
            text-align: center;
            padding: 0.7rem 1rem;
            font-weight: 800;
            border-radius: 12px 12px 0 0;
            margin-top: 1.5rem;
        }
        .band-sub {
            border: 1px solid #d7b99d;
            border-top: 0;
            padding: 0.75rem 1rem;
            text-align: center;
            color: #5c3922;
            background: #fbf7f2;
            border-radius: 0 0 12px 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.25rem;
        }
        th, td {
            border: 1px solid #b78d65;
            padding: 0.8rem 0.75rem;
            vertical-align: top;
            font-size: 0.95rem;
        }
        th {
            background: #f4ede6;
            text-align: center;
            color: #3d2814;
            font-weight: 800;
        }
        .times span {
            display: block;
        }
        .times span + span {
            margin-top: 0.3rem;
        }
        .empty {
            text-align: center;
            color: #7b5a45;
            padding: 2rem 1rem;
        }
        @media print {
            body {
                background: #fff;
            }
            .page {
                padding: 0;
            }
            .actions {
                display: none;
            }
            .paper {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="actions">
            <a href="{{ route('employee.my_schedule') }}" class="secondary">Back to My Schedule</a>
            <a href="{{ route('employee.my_schedule.pdf') }}" class="primary">Export PDF</a>
            <button type="button" class="secondary" onclick="window.print()">Print</button>
        </div>

        <div class="paper">
            <div class="school-header">
                <img src="{{ asset('assets/images/logo-sm.jpg') }}" alt="School logo">
                <div>
                    <p class="school-title">Colegio de Sta. Teresa De Avila</p>
                    <p class="school-subtitle">Employee Schedule Preview</p>
                    <p class="meta">{{ $employee->name }} • Generated {{ $generatedAt->format('F j, Y g:i A') }}</p>
                </div>
            </div>

            <div class="band">WEEKLY CLASS-STYLE TIMETABLE</div>
            <div class="band-sub">Prepared schedule preview for printing or PDF export</div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 12%;">Code</th>
                        <th>Course Description</th>
                        <th style="width: 12%;">Day/s</th>
                        <th style="width: 22%;">Time</th>
                        <th style="width: 12%;">Room</th>
                        <th style="width: 18%;">Faculty</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($scheduleRows as $row)
                        <tr>
                            <td style="text-align:center;">{{ $row['course_code'] ?: '-' }}</td>
                            <td>{{ $row['course_name'] ?: '-' }}</td>
                            <td style="text-align:center;">{{ $dayShort($row['day_of_week']) }}</td>
                            <td class="times" style="text-align:center;">
                                @foreach ($row['times'] as $time)
                                    <span>{{ $time['display'] }}</span>
                                @endforeach
                            </td>
                            <td style="text-align:center;">{{ $row['room'] }}</td>
                            <td style="text-align:center;">{{ $employee->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">No schedule entries are available for preview yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
