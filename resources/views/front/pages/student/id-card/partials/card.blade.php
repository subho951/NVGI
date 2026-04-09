<?php

$brand = $brand ?? [];

$studentName = trim((string)($student->full_name ?? ''));
if ($studentName === '') {
    $studentName = 'Student';
}

$studentPhoto = (($student->photo ?? '') !== '')
    ? config('constants.app_url') . config('constants.uploads_url_path') . $student->photo
    : config('constants.no_image_avatar');

$classLabel = trim((string)($student->class_name ?? ''));
if ($classLabel === '') {
    $classLabel = '-';
}

$unitLabel = trim((string)($student->unit_name ?? ''));
if ($unitLabel === '') {
    $unitLabel = '-';
}

$branchLabel = trim((string)($student->branch_name ?? ''));
if ($branchLabel === '') {
    $branchLabel = '-';
}

$schoolName = trim((string)($brand['site_name'] ?? 'School'));
if ($schoolName === '') {
    $schoolName = 'School';
}

$schoolTagline = trim((string)($brand['site_tagline'] ?? ''));
$schoolLogoUrl = trim((string)($brand['site_logo_url'] ?? ''));
$schoolInitials = trim((string)($brand['site_initials'] ?? 'ID'));
?>
<div class="id-card">
    <div class="id-card__strip">IDENTITY CARD</div>
    <div class="id-card__main">
        <div class="brand-row">
            @if($schoolLogoUrl !== '')
                <img src="{{ $schoolLogoUrl }}" alt="{{ e($schoolName) }}" class="brand-logo">
            @else
                <div class="brand-initials">{{ $schoolInitials }}</div>
            @endif
            <div class="brand-copy">
                <div class="brand-name">{{ strtoupper($schoolName) }}</div>
                @if($schoolTagline !== '')
                    <div class="brand-tagline">{{ $schoolTagline }}</div>
                @endif
            </div>
        </div>

        <div class="id-card__body">
            <div class="photo-stack">
                <img src="{{ $studentPhoto }}" alt="{{ e($studentName) }}" class="student-photo">
                <div class="student-code">{{ $student->student_id_serial }}</div>
            </div>

            <div class="student-details">
                <div>
                    <h3 class="student-name">{{ $studentName }}</h3>
                    <div class="detail-list">
                        <div class="student-detail">
                            <span class="student-detail-label">CLASS / SECTION:</span>
                            <span class="student-detail-value">{{ $classLabel }}</span>
                        </div>
                        <div class="student-detail">
                            <span class="student-detail-label">UNIT / BRANCH:</span>
                            <span class="student-detail-value">{{ $unitLabel }} / {{ $branchLabel }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="barcode-box">
                        <div class="barcode-bars"></div>
                        <div class="barcode-text">{{ $student->student_id_serial }}</div>
                    </div>
                    <div class="sign-box">
                        <div class="sign-line">Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
