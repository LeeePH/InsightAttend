<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Approval Letter - {{ $leave->employee->name ?? 'Employee' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 28px;
            color: #111;
            line-height: 1.45;
        }
        .letter-wrapper {
            max-width: 900px;
            margin: 0 auto;
            border: 1.5px solid #1f2a3d;
            padding: 26px 30px 34px;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
            border-bottom: 1px solid #c8cfda;
            padding-bottom: 12px;
        }
        .logo {
            width: 76px;
            height: 76px;
            object-fit: cover;
        }
        .school-meta {
            flex: 1;
            text-align: center;
            margin-right: 76px;
        }
        .school-name {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }
        .school-address {
            margin: 6px 0 0;
            font-size: 14px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 20px 0 22px;
        }
        .meta {
            margin-bottom: 16px;
        }
        .meta p {
            margin: 0 0 6px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 18px 0 18px;
        }
        .info-table th,
        .info-table td {
            border: 1px solid #c8cfda;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 14px;
        }
        .info-table th {
            width: 190px;
            background: #f5f7fb;
            text-align: left;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 58px;
            gap: 30px;
        }
        .sign-block {
            width: 44%;
            text-align: center;
        }
        .sign-name {
            margin-bottom: 16px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .sign-line {
            border-top: 1px solid #111;
            width: 100%;
            margin: 0 auto 8px;
        }
        .sign-role {
            margin: 0;
            font-size: 13px;
        }
        .print-btn {
            position: fixed;
            top: 18px;
            right: 18px;
            border: 1px solid #8fa0bc;
            background: #f5f7fb;
            color: #1f2a3d;
            padding: 9px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
            .letter-wrapper { border: none; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>

    <div class="letter-wrapper">
        <div class="header">
            <img src="{{ asset('assets/images/logo-sm.jpg') }}" alt="School Logo" class="logo">
            <div class="school-meta">
                <h1 class="school-name">Colegio de Sta. Teresa de Avila</h1>
                <p class="school-address">06 Kingfisher Street, Zabarte Subd., Kaligayahan, Novaliches, and Quezon City.</p>
            </div>
        </div>

        <div class="title">Leave Approval Letter</div>

        <div class="meta">
            <p><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('F d, Y') }}</p>
            <p><strong>Employee:</strong> {{ $leave->employee->name ?? 'N/A' }}</p>
        </div>

        <p>
            This is to inform you that your leave request has been <strong>APPROVED</strong> by the administration.
            Please refer to the approved details below.
        </p>

        <table class="info-table">
            <tr>
                <th>Employee Name</th>
                <td>{{ $leave->employee->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{ $leave->employee->department ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Position</th>
                <td>{{ $leave->employee->position ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Leave Type</th>
                <td>{{ $leave->type_label }}</td>
            </tr>
            <tr>
                <th>Leave Dates</th>
                <td>
                    {{ \Carbon\Carbon::parse($leave->leave_date)->format('F d, Y') }}
                    @if($leave->leave_date_end)
                        to {{ \Carbon\Carbon::parse($leave->leave_date_end)->format('F d, Y') }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Reason</th>
                <td>{{ $leave->reason }}</td>
            </tr>
            @if($leave->remarks)
            <tr>
                <th>Admin Remarks</th>
                <td>{{ $leave->remarks }}</td>
            </tr>
            @endif
        </table>

        <div class="signatures">
            <div class="sign-block">
                <div class="sign-name">{{ $leave->employee->name ?? 'Employee' }}</div>
                <div class="sign-line"></div>
                <p class="sign-role">Employee Signature</p>
            </div>
            <div class="sign-block">
                <div class="sign-name">{{ $leave->approver->name ?? 'Administrator' }}</div>
                <div class="sign-line"></div>
                <p class="sign-role">Admin Signature</p>
            </div>
        </div>
    </div>
</body>
<script>
    window.onload = function () {
        window.print();
    };
</script>
</html>
