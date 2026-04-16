<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Out · Facial recognition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link href="{{ URL::asset('assets/css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert@1.1.3/dist/sweetalert.css" rel="stylesheet">
    <style>
        :root {
            --to-bg: #070b12;
            --to-surface: rgba(255, 255, 255, 0.07);
            --to-border: rgba(255, 255, 255, 0.12);
            --to-text: #f1f5f9;
            --to-muted: rgba(241, 245, 249, 0.65);
            --to-accent: #f472b6;
            --to-accent-2: #e879f9;
            --to-accent-soft: rgba(244, 114, 182, 0.16);
            --to-radius: 20px;
            --to-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);
            --to-font: "DM Sans", system-ui, sans-serif;
            --to-mono: "JetBrains Mono", ui-monospace, monospace;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            min-height: 100%;
            margin: 0;
            font-family: var(--to-font);
            background: var(--to-bg);
            color: var(--to-text);
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 120% 80% at 95% -15%, rgba(244, 114, 182, 0.2), transparent 48%),
                radial-gradient(ellipse 80% 60% at 0% 20%, rgba(232, 121, 249, 0.12), transparent 45%),
                radial-gradient(ellipse 70% 50% at 50% 100%, rgba(244, 114, 182, 0.06), transparent 50%),
                url('{{ asset('images.jpg') }}') center center / cover no-repeat fixed;
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: linear-gradient(165deg, rgba(7, 11, 18, 0.85) 0%, rgba(7, 11, 18, 0.75) 45%, rgba(7, 11, 18, 0.9) 100%);
            z-index: -1;
        }

        .to-back {
            position: fixed;
            top: clamp(14px, 3vw, 24px);
            left: clamp(14px, 3vw, 24px);
            z-index: 20;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--to-muted);
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid transparent;
            transition: color 0.2s, background 0.2s, border-color 0.2s;
        }

        .to-back:hover {
            color: var(--to-text);
            background: var(--to-surface);
            border-color: var(--to-border);
        }

        .to-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(72px, 12vw, 100px) clamp(16px, 4vw, 32px) clamp(28px, 5vw, 48px);
        }

        .to-container {
            width: min(100%, 480px);
            padding: clamp(24px, 5vw, 36px);
            border-radius: var(--to-radius);
            background: var(--to-surface);
            border: 1px solid var(--to-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: var(--to-shadow);
        }

        .to-flash {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            line-height: 1.45;
            border: 1px solid var(--to-border);
        }

        .to-flash--ok {
            background: rgba(52, 211, 153, 0.12);
            border-color: rgba(52, 211, 153, 0.35);
            color: #d1fae5;
        }

        .to-flash--err {
            background: rgba(248, 113, 113, 0.12);
            border-color: rgba(248, 113, 113, 0.35);
            color: #fecaca;
        }

        .to-flash button {
            flex-shrink: 0;
            background: transparent;
            border: none;
            color: inherit;
            opacity: 0.75;
            cursor: pointer;
            font-size: 1.25rem;
            line-height: 1;
            padding: 0 4px;
        }

        .to-flash button:hover { opacity: 1; }

        .to-badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #fbcfe8;
            background: var(--to-accent-soft);
            border: 1px solid rgba(244, 114, 182, 0.4);
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 10px;
        }

        .to-headline {
            margin: 0 0 6px;
            font-size: clamp(1.35rem, 4vw, 1.65rem);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .to-lead {
            margin: 0 0 22px;
            font-size: 0.9rem;
            color: var(--to-muted);
            line-height: 1.5;
        }

        .to-clock-card {
            text-align: center;
            padding: 18px 16px 20px;
            margin-bottom: 22px;
            border-radius: 14px;
            background: rgba(0, 0, 0, 0.22);
            border: 1px solid var(--to-border);
        }

        .to-clock-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--to-muted);
            margin-bottom: 8px;
        }

        .to-clock {
            font-family: var(--to-mono);
            font-size: clamp(1.75rem, 7vw, 2.35rem);
            font-weight: 600;
            letter-spacing: -0.02em;
            color: #fff;
        }

        #video-container {
            position: relative;
            width: 100%;
            max-width: 320px;
            height: 240px;
            margin: 0 auto;
            border-radius: 14px;
            overflow: hidden;
            background: #000;
            border: 1px solid var(--to-border);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
            outline: 2px solid rgba(244, 114, 182, 0.18);
            outline-offset: 2px;
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .to-actions {
            text-align: center;
            margin-top: 18px;
        }

        .to-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 48px;
            padding: 0 22px;
            font-family: var(--to-font);
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 12px;
            border: 1px solid var(--to-border);
            background: rgba(255, 255, 255, 0.08);
            color: #fff !important;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.2s, background 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .to-btn:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.22);
            transform: translateY(-1px);
        }

        .to-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .to-btn--primary {
            width: 100%;
            margin-top: 16px;
            border: none;
            background: linear-gradient(135deg, #db2777 0%, #f472b6 100%);
            color: #fffbeb !important;
            font-weight: 700;
            box-shadow: 0 12px 32px rgba(219, 39, 119, 0.38);
        }

        .to-btn--primary:hover:not(:disabled) {
            filter: brightness(1.06);
            box-shadow: 0 16px 40px rgba(219, 39, 119, 0.45);
        }

        .to-btn--ghost {
            width: 100%;
            margin-top: 12px;
        }

        .status-box {
            text-align: center;
            padding: 14px 16px;
            border-radius: 12px;
            margin-top: 18px;
            font-size: 0.9rem;
            line-height: 1.45;
            border: 1px solid transparent;
        }

        .status-processing {
            background: rgba(244, 114, 182, 0.1);
            border-color: rgba(244, 114, 182, 0.28);
            color: #fce7f3;
        }

        .status-success {
            background: rgba(52, 211, 153, 0.12);
            border-color: rgba(52, 211, 153, 0.35);
            color: #d1fae5;
        }

        .status-error {
            background: rgba(248, 113, 113, 0.1);
            border-color: rgba(248, 113, 113, 0.3);
            color: #fecaca;
        }

        .employee-info {
            text-align: center;
            margin-top: 18px;
            padding: 16px;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--to-border);
        }

        .employee-info h4 {
            color: #fff;
            margin: 0 0 6px;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .employee-info p {
            color: var(--to-muted);
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <a href="{{ route('welcome') }}" class="to-back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to home</a>

    <div class="to-page">
        <div class="to-container">
            @if(session('success'))
                <div class="to-flash to-flash--ok" role="alert">
                    <span><strong>Success.</strong> {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="to-flash to-flash--err" role="alert">
                    <span><strong>Error.</strong> {{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
                </div>
            @endif

            <span class="to-badge">Time out</span>
            <h1 class="to-headline">Facial recognition</h1>
            <p class="to-lead">End your shift the same way: look into the camera and your time out will be saved when you are recognized.</p>

            <div class="to-clock-card" aria-live="polite">
                <div class="to-clock-label">Asia / Manila</div>
                <div class="to-clock" id="clock">—</div>
            </div>

            <div id="video-container">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
            </div>

            <div class="to-actions">
                <button type="button" id="startCamera" class="to-btn">
                    <i class="fa fa-video" aria-hidden="true"></i> Start camera
                </button>
            </div>

            <div id="statusBox" class="status-box status-processing" style="display: none;">
                <span id="statusText">Processing…</span>
            </div>

            <div id="employeeInfo" class="employee-info" style="display: none;">
                <h4 id="employeeName"></h4>
                <p id="employeePosition"></p>
            </div>

            <form id="timeOutForm" method="POST" action="{{ route('timeout.store') }}" style="display: none;">
                @csrf
                <input type="hidden" name="employee_id" id="employeeId">
                <button type="submit" class="to-btn to-btn--primary">
                    <i class="fa fa-check-circle" aria-hidden="true"></i> Time out now
                </button>
            </form>

            <a href="{{ route('welcome') }}" class="to-btn to-btn--ghost"><i class="fa fa-home" aria-hidden="true"></i> Back to home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert@1.1.3/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        function updateClock() {
            const el = document.getElementById('clock');
            if (!el) return;
            const now = new Date();
            const options = { timeZone: 'Asia/Manila', hour12: true, hour: 'numeric', minute: '2-digit', second: '2-digit' };
            el.textContent = now.toLocaleTimeString('en-US', options);
        }
        updateClock();
        setInterval(updateClock, 1000);

        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');
        let startCameraBtn = document.getElementById('startCamera');
        let statusBox = document.getElementById('statusBox');
        let statusText = document.getElementById('statusText');
        let employeeInfo = document.getElementById('employeeInfo');
        let employeeName = document.getElementById('employeeName');
        let employeePosition = document.getElementById('employeePosition');
        let timeOutForm = document.getElementById('timeOutForm');
        let employeeIdInput = document.getElementById('employeeId');

        let modelsLoaded = false;
        let employeeDataLoaded = false;
        let faceMatcher = null;
        let detectedEmployee = null;
        let isSubmitting = false;
        let autoScanInterval = null;
        const FACE_DETECTION_SCORE_THRESHOLD = 0.80;
        const employeesUrl = @json(route('timein.employees'));
        const employeeBaseUrl = @json(url('/timein/employee'));

        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
            faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/'),
            faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/')
        ]).then(async () => {
            modelsLoaded = true;
            await loadEmployeeData();
        }).catch(err => {
            console.error('Error loading Face API models:', err);
            showStatus('Error loading facial recognition models. Please refresh the page.', 'error');
        });

        async function loadEmployeeData() {
            try {
                const response = await fetch(employeesUrl);
                const data = await response.json();

                if (data.employees && data.employees.length > 0) {
                    const labeledDescriptors = data.employees.map(emp => {
                        const descriptor = new Float32Array(JSON.parse(emp.face_descriptor));
                        return new faceapi.LabeledFaceDescriptors(emp.name, [descriptor]);
                    });
                    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.35);
                }
                employeeDataLoaded = true;
            } catch (err) {
                console.error('Error loading employee data:', err);
                employeeDataLoaded = true;
            }
        }

        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
                video.srcObject = stream;
                video.play();

                startCameraBtn.disabled = true;
                startCameraBtn.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i> Camera active';

                showStatus('Camera started. Looking for face…', 'processing');

                video.addEventListener('play', () => {
                    const displaySize = { width: 320, height: 240 };
                    faceapi.matchDimensions(canvas, displaySize);

                    setInterval(async () => {
                        const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                            .withFaceLandmarks()
                            .withFaceDescriptors();

                        const resizedDetections = faceapi.resizeResults(detections, displaySize);

                        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                        faceapi.draw.drawDetections(canvas, resizedDetections);
                        faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
                    }, 100);
                });

                if (autoScanInterval) clearInterval(autoScanInterval);
                autoScanInterval = setInterval(autoScanFace, 1200);
            } catch (err) {
                console.error('Error accessing camera:', err);
                showStatus('Could not access camera. Please grant camera permissions.', 'error');
            }
        }

        startCameraBtn.addEventListener('click', startCamera);
        window.addEventListener('load', startCamera);

        async function autoScanFace() {
            if (isSubmitting) return;

            if (!modelsLoaded) {
                showStatus('Models still loading. Please wait…', 'error');
                return;
            }

            if (!employeeDataLoaded) {
                showStatus('Loading registered faces… Please wait.', 'processing');
                return;
            }

            if (!faceMatcher) {
                showStatus('No employee data available. Please contact administrator.', 'error');
                return;
            }

            try {
                const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection || detection.detection.score < FACE_DETECTION_SCORE_THRESHOLD) {
                    showStatus('Please position your face toward the camera.', 'error');
                    return;
                }

                const bestMatch = faceMatcher.findBestMatch(detection.descriptor);

                if (bestMatch.label === 'unknown') {
                    showStatus('Face not recognized. You may not be registered in the system.', 'error');
                    employeeInfo.style.display = 'none';
                    timeOutForm.style.display = 'none';
                } else {
                    const response = await fetch(`${employeeBaseUrl}/${encodeURIComponent(bestMatch.label)}`);
                    const empData = await response.json();

                    if (empData.employee) {
                        detectedEmployee = empData.employee;
                        employeeName.textContent = empData.employee.name;
                        employeePosition.textContent = empData.employee.position;
                        employeeIdInput.value = empData.employee.id;

                        employeeInfo.style.display = 'block';
                        timeOutForm.style.display = 'block';

                        showStatus('Face recognized. Recording your time out…', 'success');

                        isSubmitting = true;
                        if (autoScanInterval) {
                            clearInterval(autoScanInterval);
                            autoScanInterval = null;
                        }
                        setTimeout(() => {
                            timeOutForm.submit();
                        }, 1500);
                    }
                }
            } catch (err) {
                console.error('Error scanning face:', err);
                showStatus('Error scanning face. Please try again.', 'error');
            }
        }

        function showStatus(message, type) {
            statusBox.style.display = 'block';
            statusText.textContent = message;
            statusBox.className = 'status-box status-' + type;
        }

        timeOutForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!detectedEmployee) {
                swal('Error', 'No employee selected', 'error');
                return;
            }

            this.submit();
        });
    </script>
</body>
</html>
