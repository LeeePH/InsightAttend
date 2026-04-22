<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Company Loan Application - {{ $requestItem->employee->name ?? 'Employee' }}</title>
    <style>
        @page { margin: 28px; }
        body {
            font-family: 'Times New Roman', serif;
            color: #111;
            line-height: 1.35;
            font-size: 12.5px;
        }
        .letter-wrapper {
            border: 1.5px solid #1f2a3d;
            padding: 18px 18px 22px;
        }
        .header {
            width: 100%;
            border-bottom: 1px solid #c8cfda;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
        }
        .logo {
            width: 64px;
            height: 64px;
        }
        .logo-cell {
            width: 74px;
            text-align: left;
        }
        .spacer-cell {
            width: 74px;
        }
        .school-meta {
            text-align: center;
        }
        .school-name {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }
        .school-address {
            margin: 6px 0 0;
            font-size: 12px;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            text-transform: uppercase;
            margin: 10px 0 10px;
            letter-spacing: 0.02em;
        }
        .section-title {
            font-weight: 700;
            text-align: center;
            border: 1px solid #111;
            padding: 6px 8px;
            margin: 10px 0 8px;
        }
        table.form {
            width: 100%;
            border-collapse: collapse;
        }
        table.form td, table.form th {
            border: 1px solid #111;
            padding: 6px 7px;
            vertical-align: top;
        }
        table.form th {
            text-align: left;
            background: #f5f5f5;
            width: 26%;
        }
        .muted { color: #333; }
        .checkline { margin: 0 0 4px; }
        .small { font-size: 11.5px; }
        .statement {
            margin-top: 10px;
        }
        .sig-row {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }
        .sig-cell {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding-top: 10px;
        }
        .sig-line {
            border-top: 1px solid #111;
            margin: 22px 18px 4px;
        }
        .box {
            border: 1px solid #111;
            padding: 10px 10px 12px;
            margin-top: 12px;
        }
        .page-break {
            page-break-before: always;
        }
        .box-title {
            text-align: center;
            font-weight: 700;
            margin-top: -2px;
            margin-bottom: 10px;
        }
        .line {
            display: inline-block;
            border-bottom: 1px solid #111;
            min-width: 220px;
            height: 14px;
            vertical-align: baseline;
        }
        .line.sm { min-width: 120px; }
        .line.lg { min-width: 280px; }
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col td {
            vertical-align: top;
            padding: 0;
        }
        .approved-by {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }
        .approved-by td {
            width: 50%;
            text-align: center;
            padding-top: 10px;
        }
        .name {
            font-weight: 700;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @php
        $employeeName = $requestItem->employee->name ?? 'Employee';
        $employeeNameUpper = mb_strtoupper($employeeName);
    @endphp

    <div class="letter-wrapper">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-cell">
                        <img src="{{ public_path('assets/images/logo-sm.jpg') }}" alt="School Logo" class="logo">
                    </td>
                    <td class="school-meta">
                        <h1 class="school-name">Colegio de Sta. Teresa de Avila</h1>
                        <p class="school-address">06 Kingfisher Street, Zabarte Subd., Kaligayahan, Novaliches, and Quezon City.</p>
                    </td>
                    <td class="spacer-cell">&nbsp;</td>
                </tr>
            </table>
        </div>

        <div class="title">Company Loan Application Form</div>

        <div class="section-title">Part I - Employee to supply the following information</div>

        <table class="form">
            <tr>
                <th>Name of Employee</th>
                <td>{{ $requestItem->employee->name ?? '—' }}</td>
            </tr>
            <tr>
                <th>Dept</th>
                <td>{{ $requestItem->employee->department ?? '—' }}</td>
                <th>Date Filed</th>
                <td>{{ \Carbon\Carbon::parse($requestItem->date_filed)->format('M d, Y') }}</td>
            </tr>
            <tr>
                <th>Position</th>
                <td>{{ $requestItem->employee->position ?? '—' }}</td>
                <th>Contact number</th>
                <td>{{ $requestItem->contact_number ?? '—' }}</td>
            </tr>
            <tr>
                <th>Civil Status</th>
                <td>{{ $requestItem->civil_status === 'married' ? 'Married' : 'Single' }}</td>
                <th>Hire Date</th>
                <td>{{ $requestItem->hire_date ? \Carbon\Carbon::parse($requestItem->hire_date)->format('M d, Y') : '—' }}</td>
            </tr>
            <tr>
                <th>Amount requested by employee</th>
                <td colspan="3">P {{ number_format((float) $requestItem->amount_requested, 2) }}</td>
            </tr>
            <tr>
                <th>Amount approved by the company</th>
                <td colspan="3">P {{ $requestItem->amount_approved !== null ? number_format((float) $requestItem->amount_approved, 2) : '__________' }}</td>
            </tr>
        </table>

    <p class="muted small" style="margin:10px 0 6px;">
        Below are the valid reasons in availing company loan. Please check the purpose of your loan application and attach supporting documents.
    </p>

    @php
        $flags = is_array($requestItem->purpose_flags) ? $requestItem->purpose_flags : [];
        $isChecked = function ($k) use ($flags) { return !empty($flags[$k]); };
        $box = function ($on) { return $on ? '[x]' : '[ ]'; };
    @endphp

    <div class="checkline">{!! $box($isChecked('hospitalization')) !!} Hospitalization/Medication <span class="small muted">(emergency treatment or hospitalization of employee or qualified dependent)</span></div>
    @if($isChecked('hospitalization'))
        <div class="small" style="margin-left:18px;">
            Patient's Name: <span class="line sm">{{ $requestItem->hospital_patient_name ?? '' }}</span>
            &nbsp; Relationship: <span class="line sm">{{ $requestItem->hospital_relationship ?? '' }}</span>
            &nbsp; Age: <span class="line sm">{{ $requestItem->hospital_age ?? '' }}</span>
        </div>
        <div class="small muted" style="margin-left:18px;">Attach: Hospital bills / discharge sheet / doctor's prescription / medical certificate</div>
    @endif

    <div class="checkline" style="margin-top:6px;">{!! $box($isChecked('calamity')) !!} Emergency house repair due to calamity</div>
    @if($isChecked('calamity'))
        <div class="small" style="margin-left:18px;">
            Detail: <span class="line lg">{{ $requestItem->calamity_details ?? '' }}</span>
        </div>
        <div class="small muted" style="margin-left:18px;">Attach: Proof of ownership, pictures showing damage, cost estimate of repairs</div>
    @endif

    <div class="checkline" style="margin-top:6px;">{!! $box($isChecked('bereavement')) !!} Bereavement</div>
    @if($isChecked('bereavement'))
        <div class="small" style="margin-left:18px;">
            Relationship: <span class="line sm">{{ $requestItem->bereavement_relationship ?? '' }}</span>
        </div>
        <div class="small muted" style="margin-left:18px;">Attach: Copy of registered death certificate</div>
    @endif

    <div class="checkline" style="margin-top:6px;">{!! $box($isChecked('tuition')) !!} Tuition Fee <span class="small muted">(for employee or dependent children)</span></div>
    @if($isChecked('tuition'))
        <div class="small" style="margin-left:18px;">
            Name of Child: <span class="line sm">{{ $requestItem->tuition_child_name ?? '' }}</span>
            &nbsp; Age: <span class="line sm">{{ $requestItem->tuition_child_age ?? '' }}</span>
            &nbsp; Level: <span class="line sm">{{ $requestItem->tuition_child_level ?? '' }}</span>
        </div>
        <div class="small muted" style="margin-left:18px;">Attach: Detailed assessment of fees</div>
    @endif

    <div class="checkline" style="margin-top:6px;">{!! $box($isChecked('dental')) !!} Dental</div>
    @if($isChecked('dental'))
        <div class="small" style="margin-left:18px;">
            Patient's Name: <span class="line sm">{{ $requestItem->dental_patient_name ?? '' }}</span>
            &nbsp; Relationship: <span class="line sm">{{ $requestItem->dental_relationship ?? '' }}</span>
            &nbsp; Age: <span class="line sm">{{ $requestItem->dental_age ?? '' }}</span>
        </div>
        <div class="small muted" style="margin-left:18px;">Attach: Dentist's prescription indicating the procedure, cost and schedule</div>
    @endif

    <div class="checkline" style="margin-top:6px;">{!! $box($isChecked('other')) !!} Other: <span class="line lg">{{ $requestItem->other_purpose ?? '' }}</span></div>

    <div class="statement small">
        <strong>Employee's statement:</strong><br>
        I hereby certify that the foregoing statements and information are true and correct. I understand that any dishonest information or false documents I presented to the HR to support my loan application will constitute a sufficient cause for my dismissal. I understand and agree that the approved total loan amount will be deducted from the salary. In case my employment with the company has ceased before full payment of the loan, the balance will be deducted from my quitclaim.
    </div>

    <table class="sig-row">
        <tr>
            <td class="sig-cell">
                <div class="name">{{ $employeeNameUpper }}</div>
                <div class="sig-line"></div>
                <div class="small muted">Signature over Printed Name</div>
                <strong>Employee</strong>
            </td>
        </tr>
    </table>

    <table class="sig-row" style="margin-top:4px;">
        <tr>
            <td class="sig-cell">
                <div class="small muted">Endorsed by:</div>
                <div class="name">Liza Rhoda F. Agcobao</div>
                <div class="small">Human Resource Manager</div>
            </td>
            <td class="sig-cell">
                <div class="small muted">Recommending Approval:</div>
                <div class="name">Josephine M. Roxas</div>
                <div class="small">Executive Vice President</div>
            </td>
            <td class="sig-cell">
                <div class="small muted">Approved by:</div>
                <div class="name">Alfredo S. Roxas</div>
                <div class="small">President</div>
            </td>
        </tr>
    </table>

    <div class="box page-break">
        <div class="box-title">PART II – to be filled up by Accounting Unit</div>
        <div class="small">
            To: Payroll
            <br><br>
            Please process for crediting to payroll account of <span class="line lg"></span>
            the amount of <span class="line sm"></span> (Employee)
            <br><br>
            loan application for <span class="line lg"></span> (₱ <span class="line sm"></span>) representing approved company loan.
            <br><br>
            Please deduct the same amount from employee’s payroll in <span class="line sm"></span> installments beginning
            <br>
            <span class="line lg"></span> payroll cut off.
            <br><br>
            Thank You.
        </div>

        <table class="approved-by">
            <tr>
                <td>
                    <div class="small muted">Approved by:</div>
                    <div class="name">Adoracion S. Roxas</div>
                    <div class="small">Chairman of the Board</div>
                </td>
                <td>
                    <div class="name">Ronaldo S. Roxas</div>
                    <div class="small">Vice President for Finance</div>
                </td>
            </tr>
        </table>

        <div class="small" style="margin-top:6px;">
            Date approved: <span class="line sm"></span>
        </div>
    </div>
</div>
</body>
</html>

