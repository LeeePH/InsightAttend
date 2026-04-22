<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Subsitution Form - {{ $requestItem->absent_teacher_name }}</title>
    <style>
        @page { margin: 0.35in; size: letter portrait; }
        body { font-family: Arial, sans-serif; color: #111; font-size: 12px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: middle; }
        .logo { width: 62px; height: 62px; }
        .school-meta { text-align: center; }
        .school-name { margin: 0; font-size: 16px; font-weight: 700; color: #6d3b0b; letter-spacing: 0.4px; }
        .school-lines { font-size: 11px; margin: 0; color: #555; }
        .title { text-align: center; margin: 10px 0 8px; font-size: 20px; font-weight: 700; letter-spacing: 0.6px; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.grid th, table.grid td { border: 1px solid #111; padding: 6px; font-size: 11px; }
        table.grid th { font-weight: 700; text-align: center; }
        .label-row td { font-size: 11px; }
        .sign-wrap { margin-top: 20px; font-size: 11px; }
        .sig-line { border-top: 1px solid #111; width: 210px; display: inline-block; margin-top: 30px; }
        .muted { color: #444; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width:78px;"><img src="{{ public_path('assets/images/logo-sm.jpg') }}" class="logo" alt="logo"></td>
            <td class="school-meta">
                <p class="school-name">COLEGIO DE STA. TERESA DE AVILA</p>
                <p class="school-lines">1177 Quirino Highway Brgy . Kaligayahan Novaliches Quezon City</p>
                <p class="school-lines">Tel. No. (02) 8-275-3916</p>
            </td>
            <td style="width:78px;"></td>
        </tr>
    </table>

    <div class="title">CLASS SUBSITUTION FORM</div>

    <table class="grid">
        <tr class="label-row">
            <td colspan="3">Name of Absent Teacher: <strong>{{ $requestItem->absent_teacher_name }}</strong></td>
            <td colspan="4">Name of Substitute Teacher: <strong>{{ $requestItem->substitute_teacher_name }}</strong></td>
        </tr>
        <tr>
            <th style="width:31%;">Subject/s</th>
            <th style="width:15%;">Year/Section</th>
            <th style="width:12%;">Date</th>
            <th style="width:12%;">Time</th>
            <th style="width:10%;">Room</th>
            <th style="width:12%;">No. of Hours</th>
            <th style="width:8%;">&nbsp;</th>
        </tr>
        @php $rows = (array) $requestItem->entries; @endphp
        @foreach($rows as $row)
        <tr>
            <td>{{ $row['subject'] ?? '' }}</td>
            <td>{{ $row['year_section'] ?? '' }}</td>
            <td>{{ !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('m/d/Y') : '' }}</td>
            <td>{{ $row['time'] ?? '' }}</td>
            <td>{{ $row['room'] ?? '' }}</td>
            <td>{{ $row['hours'] ?? '' }}</td>
            <td></td>
        </tr>
        @endforeach
        @for($i = count($rows); $i < 8; $i++)
        <tr>
            <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td>
        </tr>
        @endfor
    </table>

    <div class="sign-wrap">
        <div class="muted">Conforme: Prepared by:</div>
        <div style="margin-top: 34px; display:flex; justify-content: space-between;">
            <div style="width:220px;"></div>
            <div style="text-align:center; width:220px;">
                <span class="sig-line"></span>
                <div>Signature over Printed Name</div>
                <div>(Substitute Teacher)</div>
            </div>
            <div style="text-align:center; width:220px;">
                <span class="sig-line"></span>
                <div>Signature over Printed Name</div>
                <div>(Absent Teacher)</div>
            </div>
        </div>
        <div style="margin-top: 28px;" class="muted">Approved: Noted:</div>
        <div style="margin-top: 34px; text-align:right;">
            <span class="sig-line" style="width:230px;"></span>
            <div>Dean Human Resource Officer</div>
        </div>
    </div>
</body>
</html>
