<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time In · Facial recognition</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link href="{{ URL::asset('assets/css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert@1.1.3/dist/sweetalert.css" rel="stylesheet">
    <style>
        :root {
            --ti-bg: #070b12;
            --ti-surface: rgba(255, 255, 255, 0.07);
            --ti-border: rgba(255, 255, 255, 0.12);
            --ti-text: #f1f5f9;
            --ti-muted: rgba(241, 245, 249, 0.65);
            --ti-accent: #34d399;
            --ti-accent-2: #38bdf8;
            --ti-accent-soft: rgba(52, 211, 153, 0.18);
            --ti-radius: 20px;
            --ti-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);
            --ti-font: "DM Sans", system-ui, sans-serif;
            --ti-mono: "JetBrains Mono", ui-monospace, monospace;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            min-height: 100%;
            margin: 0;
            font-family: var(--ti-font);
            background: var(--ti-bg);
            color: var(--ti-text);
            -webkit-font-smoothing: antialiased;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 120% 80% at 10% -20%, rgba(52, 211, 153, 0.18), transparent 50%),
                radial-gradient(ellipse 90% 70% at 100% 0%, rgba(56, 189, 248, 0.14), transparent 45%),
                radial-gradient(ellipse 70% 50% at 50% 100%, rgba(52, 211, 153, 0.06), transparent 50%),
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

        .ti-back {
            position: fixed;
            top: clamp(14px, 3vw, 24px);
            left: clamp(14px, 3vw, 24px);
            z-index: 20;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--ti-muted);
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid transparent;
            transition: color 0.2s, background 0.2s, border-color 0.2s;
        }

        .ti-back:hover {
            color: var(--ti-text);
            background: var(--ti-surface);
            border-color: var(--ti-border);
        }

        .ti-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(72px, 12vw, 100px) clamp(16px, 4vw, 32px) clamp(28px, 5vw, 48px);
        }

        .ti-container {
            width: min(100%, 480px);
            padding: clamp(24px, 5vw, 36px);
            border-radius: var(--ti-radius);
            background: var(--ti-surface);
            border: 1px solid var(--ti-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: var(--ti-shadow);
        }

        .ti-flash {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            line-height: 1.45;
            border: 1px solid var(--ti-border);
        }

        .ti-flash--ok {
            background: rgba(52, 211, 153, 0.12);
            border-color: rgba(52, 211, 153, 0.35);
            color: #d1fae5;
        }

        .ti-flash--err {
            background: rgba(248, 113, 113, 0.12);
            border-color: rgba(248, 113, 113, 0.35);
            color: #fecaca;
        }

        .ti-flash button {
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

        .ti-flash button:hover { opacity: 1; }

        .ti-badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--ti-accent);
            background: var(--ti-accent-soft);
            border: 1px solid rgba(52, 211, 153, 0.35);
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 10px;
        }

        .ti-headline {
            margin: 0 0 6px;
            font-size: clamp(1.35rem, 4vw, 1.65rem);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .ti-lead {
            margin: 0 0 22px;
            font-size: 0.9rem;
            color: var(--ti-muted);
            line-height: 1.5;
        }

        .ti-clock-card {
            text-align: center;
            padding: 18px 16px 20px;
            margin-bottom: 22px;
            border-radius: 14px;
            background: rgba(0, 0, 0, 0.22);
            border: 1px solid var(--ti-border);
        }

        .ti-clock-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ti-muted);
            margin-bottom: 8px;
        }

        .ti-clock {
            font-family: var(--ti-mono);
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
            border: 1px solid var(--ti-border);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
            outline: 2px solid rgba(52, 211, 153, 0.15);
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

        .ti-actions {
            text-align: center;
            margin-top: 18px;
        }

        .ti-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 48px;
            padding: 0 22px;
            font-family: var(--ti-font);
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 12px;
            border: 1px solid var(--ti-border);
            background: rgba(255, 255, 255, 0.08);
            color: #fff !important;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.2s, background 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .ti-btn:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.22);
            transform: translateY(-1px);
        }

        .ti-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .ti-btn--primary {
            width: 100%;
            margin-top: 16px;
            border: none;
            background: linear-gradient(135deg, #059669 0%, #34d399 100%);
            color: #052e22 !important;
            font-weight: 700;
            box-shadow: 0 12px 32px rgba(16, 185, 129, 0.35);
        }

        .ti-btn--primary:hover:not(:disabled) {
            filter: brightness(1.05);
            box-shadow: 0 16px 40px rgba(16, 185, 129, 0.42);
        }

        .ti-btn--ghost {
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
            background: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.25);
            color: #bae6fd;
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
            border: 1px solid var(--ti-border);
        }

        .employee-info h4 {
            color: #fff;
            margin: 0 0 6px;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .employee-info p {
            color: var(--ti-muted);
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <a href="{{ route('welcome') }}" class="ti-back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back to home</a>

    <div class="ti-page">
        <div class="ti-container">
            @if(session('success'))
                <div class="ti-flash ti-flash--ok" role="alert">
                    <span><strong>Success.</strong> {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="ti-flash ti-flash--err" role="alert">
                    <span><strong>Error.</strong> {{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
                </div>
            @endif

            <span class="ti-badge">Time in</span>
            <h1 class="ti-headline">Facial recognition</h1>
            <p class="ti-lead">Allow the camera, face the lens, and your time in will be recorded when you are recognized.</p>

            <div class="ti-clock-card" aria-live="polite">
                <div class="ti-clock-label">Asia / Manila</div>
                <div class="ti-clock" id="clock">—</div>
            </div>

            <div id="video-container">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas"></canvas>
            </div>

            <div class="ti-actions">
                <button type="button" id="startCamera" class="ti-btn">
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

            <form id="timeInForm" method="POST" action="{{ route('timein.store') }}" style="display: none;">
                @csrf
                <input type="hidden" name="employee_id" id="employeeId">
                <button type="submit" class="ti-btn ti-btn--primary">
                    <i class="fa fa-check-circle" aria-hidden="true"></i> Time in now
                </button>
            </form>

            <a href="{{ route('welcome') }}" class="ti-btn ti-btn--ghost"><i class="fa fa-home" aria-hidden="true"></i> Back to home</a>
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
        let timeInForm = document.getElementById('timeInForm');
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
                    timeInForm.style.display = 'none';
                } else {
                    const response = await fetch(`${employeeBaseUrl}/${encodeURIComponent(bestMatch.label)}`);
                    const empData = await response.json();

                    if (empData.employee) {
                        detectedEmployee = empData.employee;
                        employeeName.textContent = empData.employee.name;
                        employeePosition.textContent = empData.employee.position;
                        employeeIdInput.value = empData.employee.id;

                        employeeInfo.style.display = 'block';
                        timeInForm.style.display = 'block';

                        showStatus('Face recognized. Recording your time in…', 'success');

                        isSubmitting = true;
                        if (autoScanInterval) {
                            clearInterval(autoScanInterval);
                            autoScanInterval = null;
                        }
                        setTimeout(() => {
                            timeInForm.submit();
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

        timeInForm.addEventListener('submit', function(e) {
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
