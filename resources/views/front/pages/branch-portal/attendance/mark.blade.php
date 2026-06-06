<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --ink: #171c2b;
            --muted: #8a8f99;
            --page: #f7f8fb;
            --surface: #ffffff;
            --action: {{ $mode === 'punch_out' ? '#ef3128' : '#2fb23a' }};
            --action-soft: {{ $mode === 'punch_out' ? '#fff0ef' : '#ebf8ed' }};
            --action-border: {{ $mode === 'punch_out' ? '#f1c5c2' : '#c6e7ca' }};
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--page);
            color: var(--ink);
            font-family: Arial, sans-serif;
        }

        button {
            font: inherit;
        }

        .attendance-shell {
            display: flex;
            width: min(100%, 680px);
            min-height: 100vh;
            margin: 0 auto;
            flex-direction: column;
            background: var(--page);
        }

        .mark-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: max(16px, env(safe-area-inset-top)) 18px 14px;
            border-bottom: 1px solid #eceef2;
            background: #ffffff;
        }

        .back-button {
            display: grid;
            width: 46px;
            height: 46px;
            place-items: center;
            flex: 0 0 auto;
            border-radius: 13px;
            background: #f0f2f5;
            color: var(--ink);
            font-size: 20px;
            text-decoration: none;
        }

        .mark-header h1 {
            margin: 0;
            font-size: 21px;
        }

        .employee-strip {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 18px;
        }

        .employee-strip h2 {
            margin: 0;
            font-size: 19px;
        }

        .employee-strip p {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 11px;
            border-radius: 999px;
            background: var(--action-soft);
            color: var(--action);
            font-size: 11px;
            font-weight: 900;
            white-space: nowrap;
        }

        .status-badge::before {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--action);
            content: "";
        }

        .class-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            padding: 0 18px;
        }

        .meta-chip {
            padding: 6px 9px;
            border: 1px solid #e1e5eb;
            border-radius: 999px;
            background: #ffffff;
            color: #657080;
            font-size: 10px;
            font-weight: 800;
        }

        .camera-stage {
            display: flex;
            min-height: 0;
            flex: 1;
            align-items: center;
            justify-content: center;
            padding: 22px 18px;
        }

        .camera-circle {
            position: relative;
            width: min(82vw, 470px);
            aspect-ratio: 1;
            overflow: hidden;
            border: 4px solid var(--action);
            border-radius: 50%;
            background: #1f2732;
            box-shadow: 0 0 32px color-mix(in srgb, var(--action) 28%, transparent);
        }

        .camera-circle video,
        .camera-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .camera-circle video {
            transform: scaleX(-1);
        }

        .camera-placeholder {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 36px;
            color: #ffffff;
            text-align: center;
        }

        .camera-placeholder i {
            display: block;
            margin-bottom: 12px;
            font-size: 38px;
        }

        .camera-placeholder span {
            font-size: 13px;
            line-height: 1.45;
        }

        .camera-loading {
            position: absolute;
            right: 18%;
            bottom: 8%;
            left: 18%;
            padding: 8px 10px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.54);
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            text-align: center;
        }

        .camera-fallback {
            display: none;
            margin-top: 12px;
            text-align: center;
        }

        .camera-fallback label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border: 1px solid var(--action-border);
            border-radius: 11px;
            background: var(--action-soft);
            color: var(--action);
            font-size: 12px;
            font-weight: 900;
        }

        .bottom-actions {
            padding: 0 18px calc(18px + env(safe-area-inset-bottom));
        }

        .quote {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 14px;
            padding: 14px 16px;
            border: 1px solid var(--action-border);
            border-radius: 14px;
            background: var(--action-soft);
            color: #606775;
            font-size: 13px;
            font-style: italic;
        }

        .quote i {
            color: var(--action);
        }

        .punch-button {
            width: 100%;
            min-height: 68px;
            border: 0;
            border-radius: 17px;
            background: var(--action);
            color: #ffffff;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.03em;
            box-shadow: 0 14px 28px color-mix(in srgb, var(--action) 28%, transparent);
        }

        .punch-button:disabled {
            cursor: not-allowed;
            filter: grayscale(0.35);
            opacity: 0.48;
            box-shadow: none;
        }

        .punch-button i {
            margin-right: 9px;
        }

        .flash {
            margin: 12px 18px 0;
            padding: 12px 14px;
            border: 1px solid #f1c5c2;
            border-radius: 12px;
            background: #fff0ef;
            color: #b52f29;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.45;
        }

        .confirm-layer {
            position: fixed;
            z-index: 100;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 22px;
            background: rgba(17, 20, 26, 0.58);
        }

        .confirm-layer.open {
            display: flex;
        }

        .confirm-card {
            position: relative;
            width: min(100%, 420px);
            margin-top: 50px;
            padding: 92px 24px 24px;
            border-radius: 28px;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.24);
        }

        .confirm-photo {
            position: absolute;
            top: -72px;
            left: 50%;
            width: 142px;
            height: 142px;
            border: 4px solid var(--action);
            border-radius: 50%;
            object-fit: cover;
            transform: translateX(-50%);
            background: #e9edf2;
        }

        .confirm-card h2 {
            margin: 0;
            font-size: 21px;
        }

        .confirm-card p {
            margin: 8px 0 0;
            color: #9a9da5;
            font-size: 14px;
        }

        .confirm-time {
            display: inline-block;
            margin-top: 22px;
            padding: 14px 20px;
            border-radius: 14px;
            background: #f6f7fa;
            color: var(--ink);
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 0.02em;
        }

        .confirm-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 24px;
        }

        .confirm-actions button {
            min-height: 58px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 900;
        }

        .cancel-button {
            border: 1px solid #e0e3e8;
            background: #ffffff;
            color: #6f747d;
        }

        .confirm-button {
            border: 1px solid var(--action);
            background: var(--action);
            color: #ffffff;
        }

        .confirm-button:disabled {
            opacity: 0.55;
        }

        .desktop-blocker {
            width: min(92%, 500px);
            margin: 12vh auto;
            padding: 34px 24px;
            border: 1px solid #e2e6ec;
            border-radius: 20px;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 18px 50px rgba(22, 37, 55, 0.1);
        }

        .desktop-blocker i {
            color: var(--action);
            font-size: 44px;
        }

        .desktop-blocker h1 {
            margin: 18px 0 8px;
            font-size: 22px;
        }

        .desktop-blocker p {
            margin: 0 0 18px;
            color: var(--muted);
            line-height: 1.5;
        }

        .desktop-blocker a {
            color: var(--action);
            font-weight: 800;
        }

        @media (max-height: 740px) {
            .employee-strip {
                padding-top: 12px;
                padding-bottom: 10px;
            }

            .camera-stage {
                padding-top: 12px;
                padding-bottom: 14px;
            }

            .camera-circle {
                width: min(66vh, 74vw);
            }

            .quote {
                padding-top: 10px;
                padding-bottom: 10px;
            }

            .punch-button {
                min-height: 58px;
            }
        }
    </style>
</head>
<body>
@if(!$is_mobile_device)
    <section class="desktop-blocker">
        <i class="fa-solid fa-mobile-screen-button"></i>
        <h1>Mobile Attendance Only</h1>
        <p>Open this page on a phone so the employee can use the camera.</p>
        <a href="{{ route('branch.portal.attendance.index') }}">Back to attendance</a>
    </section>
@else
    <div class="attendance-shell">
        <header class="mark-header">
            <a href="{{ route('branch.portal.attendance.index') }}" class="back-button" aria-label="Back to attendance">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <h1>Mark Attendance</h1>
        </header>

        @if(session('error_message'))
            <div class="flash">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error_message') }}
            </div>
        @endif

        @if($errors->any())
            <div class="flash">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <section class="employee-strip">
            <div>
                <h2>{{ $roster->employee_name }}</h2>
                <p>{{ \Carbon\Carbon::parse($roster->roster_date)->format('M d, Y') }}</p>
            </div>
            <span class="status-badge">
                {{ $mode === 'punch_out' ? 'Punch Out' : 'Ready' }}
            </span>
        </section>

        <div class="class-meta">
            <span class="meta-chip">{{ $roster->category }}</span>
            <span class="meta-chip">{{ $roster->branch_name }}</span>
            <span class="meta-chip">{{ $scheduled_time ?: 'Time not set' }}</span>
        </div>

        <section class="camera-stage">
            <div>
                <div class="camera-circle">
                    <video id="cameraVideo" autoplay muted playsinline></video>
                    <img id="capturedPreview" alt="Captured attendance photo" hidden>
                    <div class="camera-placeholder" id="cameraPlaceholder">
                        <div>
                            <i class="fa-solid fa-camera"></i>
                            <span>Allow camera access to mark attendance.</span>
                        </div>
                    </div>
                    <div class="camera-loading" id="cameraLoading">Starting front camera...</div>
                </div>

                <div class="camera-fallback" id="cameraFallback">
                    <label for="fallbackPhoto">
                        <i class="fa-solid fa-camera-retro"></i>
                        Open Phone Camera
                    </label>
                    <input type="file" id="fallbackPhoto" accept="image/*" capture="user" hidden>
                </div>
            </div>
        </section>

        <section class="bottom-actions">
            <div class="quote">
                <i class="fa-solid fa-quote-left"></i>
                <span>
                    {{ $mode === 'punch_out'
                        ? 'Clocking out, but your impact stays!'
                        : 'Together, we achieve more - great to see you!' }}
                </span>
            </div>
            <button type="button" class="punch-button" id="punchButton" disabled>
                <i class="fa-solid {{ $mode === 'punch_out' ? 'fa-arrow-right-from-bracket' : 'fa-fingerprint' }}"></i>
                {{ $mode === 'punch_out' ? 'PUNCH OUT' : 'PUNCH IN' }}
            </button>
        </section>
    </div>

    <div class="confirm-layer" id="confirmLayer" role="dialog" aria-modal="true" aria-labelledby="confirmEmployeeName">
        <div class="confirm-card">
            <img id="confirmPhoto" class="confirm-photo" alt="Attendance photo">
            <h2 id="confirmEmployeeName">{{ $roster->employee_name }}</h2>
            <p>{{ $mode === 'punch_out' ? 'Punching Out' : 'Punching In' }}</p>
            <div class="confirm-time" id="confirmTime">--:--</div>
            <div class="confirm-actions">
                <button type="button" class="cancel-button" id="cancelConfirm">Cancel</button>
                <button type="button" class="confirm-button" id="confirmAttendance">Confirm</button>
            </div>
        </div>
    </div>

    <canvas id="captureCanvas" width="720" height="720" hidden></canvas>

    <form method="POST" action="{{ $submit_url }}" id="attendanceForm">
        @csrf
        <input type="hidden" name="photo" id="photoInput">
    </form>

    <script>
        const cameraVideo = document.getElementById('cameraVideo');
        const cameraPlaceholder = document.getElementById('cameraPlaceholder');
        const cameraLoading = document.getElementById('cameraLoading');
        const cameraFallback = document.getElementById('cameraFallback');
        const fallbackPhoto = document.getElementById('fallbackPhoto');
        const captureCanvas = document.getElementById('captureCanvas');
        const capturedPreview = document.getElementById('capturedPreview');
        const punchButton = document.getElementById('punchButton');
        const confirmLayer = document.getElementById('confirmLayer');
        const confirmPhoto = document.getElementById('confirmPhoto');
        const confirmTime = document.getElementById('confirmTime');
        const cancelConfirm = document.getElementById('cancelConfirm');
        const confirmAttendance = document.getElementById('confirmAttendance');
        const attendanceForm = document.getElementById('attendanceForm');
        const photoInput = document.getElementById('photoInput');
        const serverNowMilliseconds = Number(@json($server_now_milliseconds));
        const pageOpenedMilliseconds = Date.now();
        const appTimezone = @json($app_timezone);

        let cameraStream = null;
        let cameraReady = false;
        let capturedPhoto = '';

        function currentServerTimeLabel() {
            const elapsed = Date.now() - pageOpenedMilliseconds;
            const currentServerDate = new Date(serverNowMilliseconds + elapsed);

            return new Intl.DateTimeFormat('en-IN', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
                timeZone: appTimezone
            }).format(currentServerDate);
        }

        function drawSquareImage(source, sourceWidth, sourceHeight, mirror) {
            const context = captureCanvas.getContext('2d');
            const squareSize = Math.min(sourceWidth, sourceHeight);
            const sourceX = (sourceWidth - squareSize) / 2;
            const sourceY = (sourceHeight - squareSize) / 2;

            context.save();
            context.clearRect(0, 0, captureCanvas.width, captureCanvas.height);

            if (mirror) {
                context.translate(captureCanvas.width, 0);
                context.scale(-1, 1);
            }

            context.drawImage(
                source,
                sourceX,
                sourceY,
                squareSize,
                squareSize,
                0,
                0,
                captureCanvas.width,
                captureCanvas.height
            );
            context.restore();

            return captureCanvas.toDataURL('image/jpeg', 0.78);
        }

        function showCapturedPhoto(photoData) {
            capturedPhoto = photoData;
            capturedPreview.src = photoData;
            capturedPreview.hidden = false;
            cameraVideo.hidden = true;
            cameraPlaceholder.style.display = 'none';
            cameraLoading.style.display = 'none';
            punchButton.disabled = false;
        }

        async function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showCameraFallback('Use the phone camera button below.');
                return;
            }

            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: {
                        facingMode: 'user',
                        width: { ideal: 720 },
                        height: { ideal: 720 }
                    }
                });

                cameraVideo.srcObject = cameraStream;
                await cameraVideo.play();
                cameraReady = true;
                cameraPlaceholder.style.display = 'none';
                cameraLoading.style.display = 'none';
                punchButton.disabled = false;
            } catch (error) {
                showCameraFallback('Camera permission is needed. You can also open the phone camera below.');
            }
        }

        function showCameraFallback(message) {
            cameraReady = false;
            cameraLoading.style.display = 'none';
            cameraPlaceholder.style.display = 'grid';
            cameraPlaceholder.querySelector('span').textContent = message;
            cameraFallback.style.display = 'block';
            punchButton.disabled = true;
        }

        function captureCurrentPhoto() {
            if (capturedPhoto) {
                return capturedPhoto;
            }

            if (!cameraReady || !cameraVideo.videoWidth || !cameraVideo.videoHeight) {
                return '';
            }

            return drawSquareImage(cameraVideo, cameraVideo.videoWidth, cameraVideo.videoHeight, true);
        }

        punchButton.addEventListener('click', function() {
            const photoData = captureCurrentPhoto();
            if (!photoData) {
                showCameraFallback('Please open the phone camera and take a photo.');
                return;
            }

            capturedPhoto = photoData;
            confirmPhoto.src = photoData;
            confirmTime.textContent = currentServerTimeLabel();
            confirmLayer.classList.add('open');
        });

        fallbackPhoto.addEventListener('change', function(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            const image = new Image();

            image.onload = function() {
                const photoData = drawSquareImage(image, image.naturalWidth, image.naturalHeight, false);
                showCapturedPhoto(photoData);
                URL.revokeObjectURL(objectUrl);
            };

            image.onerror = function() {
                URL.revokeObjectURL(objectUrl);
                showCameraFallback('That photo could not be opened. Please try again.');
            };

            image.src = objectUrl;
        });

        cancelConfirm.addEventListener('click', function() {
            confirmLayer.classList.remove('open');
        });

        confirmLayer.addEventListener('click', function(event) {
            if (event.target === confirmLayer) {
                confirmLayer.classList.remove('open');
            }
        });

        confirmAttendance.addEventListener('click', function() {
            if (!capturedPhoto) {
                confirmLayer.classList.remove('open');
                return;
            }

            confirmAttendance.disabled = true;
            confirmAttendance.textContent = 'Saving...';
            cancelConfirm.disabled = true;
            punchButton.disabled = true;
            photoInput.value = capturedPhoto;

            if (cameraStream) {
                cameraStream.getTracks().forEach(function(track) {
                    track.stop();
                });
            }

            attendanceForm.submit();
        });

        window.addEventListener('beforeunload', function() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(track) {
                    track.stop();
                });
            }
        });

        startCamera();
    </script>
@endif
</body>
</html>
