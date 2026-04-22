<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Application for Discount - {{ $requestItem->employee->name ?? 'Employee' }}</title>
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
        .header {
            width: 100%;
            border-bottom: 1px solid #c8cfda;
            padding-bottom: 10px;
            margin-bottom: 10px;
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
        table.form { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.form th, table.form td { border: 1px solid #111; padding: 6px; }
        table.form th { text-align: left; font-weight: 700; }
        .small { font-size: 11px; }
        .name { font-weight: 700; text-transform: uppercase; }
        .sig-line { border-top: 1px solid #111; margin-top: 28px; }
    </style>
</head>
<body>
@php
    $schoolMap = ['stsn' => 'STSN', 'csta' => 'CSTA'];
    $employmentMap = ['probationary' => 'Probationary', 'regular' => 'Regular'];
    $deptMap = ['grade_school' => 'Grade School', 'junior_high' => 'Junior High School', 'senior_high' => 'Senior High School', 'college' => 'College'];
    $filedByName = strtoupper($requestItem->employee->name ?? ('Employee #' . $requestItem->emp_id));
@endphp

<div class="letter-wrapper">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('assets/images/logo-sm.jpg') }}" alt="School Logo" class="logo">
                </td>
                <td class="school-meta">
                    <h1 class="school-name">Colegio de Sta. Teresa de Avila, Inc.</h1>
                    <div class="school-address">1177 Quirino Highway, Brgy. Kaligayahan, Novaliches, Quezon City 1124 Philippines</div>
                    <div class="school-address">Tel. no. (02) 8-275-3916</div>
                </td>
                <td class="spacer-cell">&nbsp;</td>
            </tr>
        </table>
    </div>

    <div class="title">Application for Discount</div>

    <div class="small" style="margin-bottom:8px;">
        <strong>Term/Semester:</strong> <span class="line">{{ $requestItem->term_semester }}</span>
        &nbsp;&nbsp;<strong>School/Academic Year:</strong> <span class="line">{{ $requestItem->school_year }}</span>
    </div>

    <table class="form">
        <tr>
            <th>Date of Request</th>
            <td>{{ \Carbon\Carbon::parse($requestItem->date_request)->format('M d, Y') }}</td>
            <th>Department</th>
            <td>{{ $requestItem->employee_department }}</td>
        </tr>
        <tr>
            <th>Employee Name</th>
            <td>{{ $requestItem->employee->name ?? ('Employee #' . $requestItem->emp_id) }}</td>
            <th>Position</th>
            <td>{{ $requestItem->employee_position }}</td>
        </tr>
        <tr>
            <th>Date of Hire</th>
            <td>{{ $requestItem->date_hire ? \Carbon\Carbon::parse($requestItem->date_hire)->format('M d, Y') : '-' }}</td>
            <th>Employment Status</th>
            <td>{{ $employmentMap[$requestItem->employment_status] ?? '-' }}</td>
        </tr>
        <tr>
            <th>School</th>
            <td>{{ $schoolMap[$requestItem->school] ?? '-' }}</td>
            <th>Name of Student</th>
            <td>{{ $requestItem->student_name }}</td>
        </tr>
        <tr>
            <th>Student No</th>
            <td>{{ $requestItem->student_no }}</td>
            <th>Track & Strand/Program</th>
            <td>{{ $requestItem->track_program }}</td>
        </tr>
        <tr>
            <th>Grade/Year Level</th>
            <td>{{ $requestItem->grade_year_level }}</td>
            <th>Department</th>
            <td>{{ $deptMap[$requestItem->student_department] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Discount Applied</th>
            <td>{{ $requestItem->discount_applied === 'family_relative' ? 'Family Relative (sponsorship)' : "Employee's Privileges" }}</td>
            <th>Tuition Fee Discount Availed (%)</th>
            <td>{{ $requestItem->tuition_fee_discount_percent !== null ? number_format((float)$requestItem->tuition_fee_discount_percent, 2) . '%' : '-' }}</td>
        </tr>
        <tr>
            <th>Relationship</th>
            <td>{{ $requestItem->family_relationship ?? '-' }}</td>
            <th>Employee Privileges</th>
            <td>{{ $requestItem->privilege_child_order ? ($requestItem->privilege_child_order . ' child') : '-' }}</td>
        </tr>
        <tr>
            <th>Noted by (Remarks)</th>
            <td colspan="3">{{ $requestItem->remarks ?: '-' }}</td>
        </tr>
    </table>

    <div style="margin-top: 16px;">
        <table class="header-table">
            <tr>
                <td style="width:50%; text-align:center;">
                    <strong>Filed by:</strong>
                    <div class="sig-line" style="width: 320px; margin-left:auto; margin-right:auto;"></div>
                    <div class="name">{{ $filedByName }}</div>
                    <div class="small">Signature over Printed Name</div>
                </td>
                <td style="width:50%; text-align:center;">
                    <strong>Endorsed by:</strong>
                    <div class="sig-line" style="width: 320px; margin-left:auto; margin-right:auto;"></div>
                    <div class="small">Human Resource Officer</div>
                </td>
            </tr>
            <tr>
                <td style="padding-top:18px; text-align:center;">
                    <strong>Recommending Approval by:</strong>
                    <div class="sig-line" style="width: 320px; margin-left:auto; margin-right:auto;"></div>
                    <div class="small">Executive Vice President</div>
                </td>
                <td style="padding-top:18px; text-align:center;">
                    <strong>Noted by:</strong>
                    <div class="sig-line" style="width: 320px; margin-left:auto; margin-right:auto;"></div>
                    <div class="small">Vice President for Finance</div>
                </td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
