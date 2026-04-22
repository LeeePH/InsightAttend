<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Undertime Authorization Form - {{ $requestItem->employee_name }}</title>
    <style>
        @page { margin: 0.25in; size: letter portrait; }
        body {
            font-family: 'Times New Roman', serif;
            color: #111;
            line-height: 1.35;
            font-size: 12px;
        }
        .letter-wrapper {
            border: 1.5px solid #1f2a3d;
            padding: 16px;
            box-sizing: border-box;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; padding: 0; }
        .logo { width: 64px; height: 64px; }
        .logo-cell { width: 74px; text-align: left; }
        .spacer-cell { width: 74px; }
        .school-meta { text-align: center; }
        .school-name { font-size: 14px; font-weight: 700; text-transform: uppercase; margin: 0; }
        .school-address { margin: 4px 0 0; font-size: 11px; }
        .title { text-align: center; font-size: 18px; font-weight: 700; text-transform: uppercase; margin: 10px 0 8px; }
        .line {
            display: inline-block;
            border-bottom: 1px solid #111;
            min-width: 180px;
            height: 14px;
            vertical-align: baseline;
        }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.grid th, table.grid td { border: 1px solid #111; padding: 6px; }
        table.grid th { text-align: center; font-weight: 700; }
        .small { font-size: 11px; }
        .sig-line { border-top: 1px solid #111; margin-top: 30px; }
    </style>
</head>
<body>
@php
    $filedByName = strtoupper($requestItem->employee_name ?? 'Employee');
@endphp
<div class="letter-wrapper">
    <table class="header-table">
        <tr>
            <td class="logo-cell"><img src="{{ public_path('assets/images/logo-sm.jpg') }}" class="logo" alt="logo"></td>
            <td class="school-meta">
                <h1 class="school-name">Colegio de Sta. Teresa de Avila, Inc.</h1>
                <div class="school-address">1177 Quirino Highway, Brgy. Kaligayahan, Novaliches, Quezon City 1124 Philippines</div>
                <div class="school-address">Tel. no. (02) 8-275-3916</div>
            </td>
            <td class="spacer-cell">&nbsp;</td>
        </tr>
    </table>

    <div class="title">Undertime Authorization Form</div>

    <div style="margin-top: 6px;">
        <strong>NAME:</strong> <span class="line">{{ $requestItem->employee_name }}</span>
        &nbsp;&nbsp; <strong>DATE FILED:</strong> <span class="line">{{ \Carbon\Carbon::parse($requestItem->date_filed)->format('M d, Y') }}</span>
        &nbsp;&nbsp; <strong>POSITION:</strong> <span class="line">{{ $requestItem->employee_position }}</span>
    </div>
    <div style="margin-top: 6px;">
        <strong>DEPARTMENT:</strong> <span class="line" style="min-width: 260px;">{{ $requestItem->employee_department }}</span>
    </div>

    <table class="grid">
        <thead>
            <tr>
                <th rowspan="2">DATE</th>
                <th colspan="2">WORK SCHEDULE</th>
                <th colspan="2">UNDERTIME HOURS</th>
                <th rowspan="2">TOTAL NUMBER OF UNDERTIME HOURS</th>
                <th rowspan="2">REASON FOR UNDERTIME</th>
            </tr>
            <tr>
                <th>FROM</th>
                <th>TO</th>
                <th>FROM</th>
                <th>TO</th>
            </tr>
        </thead>
        <tbody>
            @foreach((array) $requestItem->entries as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $row['work_from'])->format('h:i A') }}</td>
                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $row['work_to'])->format('h:i A') }}</td>
                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $row['ut_from'])->format('h:i A') }}</td>
                <td>{{ \Carbon\Carbon::createFromFormat('H:i', $row['ut_to'])->format('h:i A') }}</td>
                <td style="text-align:center;">{{ $row['total_hours'] }}</td>
                <td>{{ $row['reason'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 16px;">
        <strong>Filed by:</strong>
        <div class="sig-line" style="width: 340px;"></div>
        <div style="width: 340px; text-align:center; font-weight:700;">{{ $filedByName }}</div>
        <div style="width: 340px; text-align:center;" class="small">Signature over Printed Name</div>
    </div>

    <div style="margin-top: 12px;">
        <strong>Verified by: Approved by:</strong>
        <div class="sig-line"></div>
        <div style="text-align:center;">Human Resource Officer Immediate Superior</div>
    </div>
</div>
</body>
</html>
