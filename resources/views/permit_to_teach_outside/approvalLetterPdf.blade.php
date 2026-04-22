<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Permit to Teach (Outside School) - {{ $requestItem->employee->name ?? 'Employee' }}</title>
    <style>
        @page { margin: 0.35in; size: letter portrait; }
        body { font-family: Arial, sans-serif; color: #111; font-size: 12px; line-height: 1.35; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { vertical-align: middle; }
        .logo { width: 62px; height: 62px; }
        .school-meta { text-align: center; }
        .school-name { margin: 0; font-size: 14px; font-weight: 700; text-transform: uppercase; }
        .school-lines { font-size: 12px; margin: 0; }
        .accent-line { border-top: 2px solid #8a5a2b; margin: 8px 0 12px; }
        .title { font-size: 13px; font-weight: 700; margin-bottom: 8px; }
        .line { border-bottom: 1px solid #111; display: inline-block; min-width: 180px; height: 14px; vertical-align: baseline; }
        .section { margin-top: 10px; border-top: 1px solid #777; padding-top: 8px; }
        .small-gap { margin-top: 6px; }
        .checkbox { font-family: DejaVu Sans, sans-serif; }
    </style>
</head>
<body>
@php
    $employment = $requestItem->employment_status;
    $institution = $requestItem->institution_type;
    $level = $requestItem->program_level;
@endphp
    <table class="header-table">
        <tr>
            <td style="width:74px;"><img src="{{ public_path('assets/images/logo-sm.jpg') }}" class="logo" alt="logo"></td>
            <td class="school-meta">
                <p class="school-name">COLEGIO DE STA. TERESA DE AVILA</p>
                <p class="school-lines">1177 Quirino Highway, Barangay Kaligayahan,</p>
                <p class="school-lines">Novaliches, Quezon City, Metro Manila, 1124 Philippines</p>
                <p class="school-lines">Email: cstas.hsfn@gmail.com</p>
                <p class="school-lines">Contact no.: (02)827-4381</p>
            </td>
            <td style="width:74px;"></td>
        </tr>
    </table>

    <div class="accent-line"></div>
    <div class="title">PERMIT TO TEACH (OUTSIDE SCHOOL) APPLICATION FORM</div>

    <div>Name of Faculty/Employee: <span class="line" style="min-width: 300px;">{{ $requestItem->employee->name ?? '' }}</span></div>
    <div class="small-gap">Position / Rank: <span class="line" style="min-width: 315px;">{{ $requestItem->position_rank }}</span></div>
    <div class="small-gap">Department / School: <span class="line" style="min-width: 295px;">{{ $requestItem->department_school }}</span></div>
    <div class="small-gap">Employment Status:
        <span class="checkbox">{{ $employment === 'full_time' ? '☑' : '☐' }}</span> Full-time
        <span class="checkbox">{{ $employment === 'part_time' ? '☑' : '☐' }}</span> Part-time
        <span class="checkbox">{{ $employment === 'probationary' ? '☑' : '☐' }}</span> Probationary
        <span class="checkbox">{{ $employment === 'regular' ? '☑' : '☐' }}</span> Regular
    </div>

    <div class="section">
        <strong>A. Outside Teaching Details</strong>
        <div class="small-gap">Name of Other School/Institution: <span class="line" style="min-width: 270px;">{{ $requestItem->other_school_name }}</span></div>
        <div class="small-gap">School Address: <span class="line" style="min-width: 360px;">{{ $requestItem->other_school_address }}</span></div>
        <div class="small-gap">Type of Institution:
            <span class="checkbox">{{ $institution === 'public' ? '☑' : '☐' }}</span> Public
            <span class="checkbox">{{ $institution === 'private' ? '☑' : '☐' }}</span> Private
            <span class="checkbox">{{ $institution === 'review_center' ? '☑' : '☐' }}</span> Review Center
            <span class="checkbox">{{ $institution === 'others' ? '☑' : '☐' }}</span> Others:
            <span class="line" style="min-width: 120px;">{{ $requestItem->institution_type === 'others' ? $requestItem->institution_type_others : '' }}</span>
        </div>
        <div class="small-gap">Subject(s) to be Taught: <span class="line" style="min-width: 300px;">{{ $requestItem->subjects_to_teach }}</span></div>
        <div class="small-gap">Program / Level:
            <span class="checkbox">{{ $level === 'basic_ed' ? '☑' : '☐' }}</span> Basic Ed
            <span class="checkbox">{{ $level === 'senior_high' ? '☑' : '☐' }}</span> Senior High
            <span class="checkbox">{{ $level === 'college' ? '☑' : '☐' }}</span> College
            <span class="checkbox">{{ $level === 'graduate' ? '☑' : '☐' }}</span> Graduate
        </div>
        <div class="small-gap">No. of Units / Hours per Week: <span class="line" style="min-width: 250px;">{{ $requestItem->units_or_hours_per_week }}</span></div>
        <div class="small-gap">Teaching Schedule in Other School:</div>
        <div style="border: 1px solid #777; min-height: 56px; padding: 6px; margin-top: 4px;">{!! nl2br(e($requestItem->teaching_schedule)) !!}</div>
        <div class="small-gap">Duration of Engagement:</div>
        <div class="small-gap">From <span class="line" style="min-width: 120px;">{{ $requestItem->engagement_from ? \Carbon\Carbon::parse($requestItem->engagement_from)->format('m/d/Y') : '' }}</span> to <span class="line" style="min-width: 120px;">{{ $requestItem->engagement_to ? \Carbon\Carbon::parse($requestItem->engagement_to)->format('m/d/Y') : '' }}</span></div>
    </div>

    <div class="section">
        <strong>B. Certification by Applicant</strong>
        <div class="small-gap">I hereby certify that:</div>
        <ul style="margin-top: 4px; margin-bottom: 6px;">
            <li>My teaching engagement in another institution will not conflict with my official working hours, teaching load, or responsibilities in Colegio de Sta. Teresa De Avila.</li>
            <li>I will prioritize my duties and performance in this institution.</li>
            <li>I understand that failure to comply may result in revocation of this permit and possible administrative action.</li>
        </ul>
        <div>Signature of Applicant: <span class="line" style="min-width: 220px;"></span></div>
        <div class="small-gap">Date: <span class="line" style="min-width: 120px;"></span></div>
    </div>

    <div class="section">
        <strong>C. Endorsement</strong>
        <div class="small-gap">Immediate Supervisor / Program Head:</div>
        <div>I recommend approval of this request.</div>
        <div class="small-gap">Name &amp; Signature: <span class="line" style="min-width: 220px;"></span></div>
        <div class="small-gap">Date: <span class="line" style="min-width: 120px;"></span></div>
    </div>

    <div class="section">
        <strong>D. HR Review</strong>
        <div class="small-gap"><span class="checkbox">☐</span> No conflict with teaching load</div>
        <div><span class="checkbox">☐</span> No conflict with work schedule</div>
        <div class="small-gap">HR Officer: <span class="line" style="min-width: 220px;"></span></div>
        <div class="small-gap">Remarks: <span class="line" style="min-width: 320px;"></span></div>
        <div class="small-gap">Date: <span class="line" style="min-width: 120px;"></span></div>
    </div>

    <div class="section">
        <strong>E. Management Approval</strong>
        <div class="small-gap"><span class="checkbox">☐</span> Approved</div>
        <div><span class="checkbox">☐</span> Disapproved</div>
        <div class="small-gap">Authorized Signatory: <span class="line" style="min-width: 220px;"></span></div>
        <div class="small-gap">Position: <span class="line" style="min-width: 180px;"></span></div>
        <div class="small-gap">Date: <span class="line" style="min-width: 120px;"></span></div>
    </div>
</body>
</html>
