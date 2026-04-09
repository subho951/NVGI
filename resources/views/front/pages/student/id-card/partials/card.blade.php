<?php

$brand = $brand ?? [];

$normalize = function ($value, $fallback = '-') {
    $value = preg_replace('/\s+/', ' ', trim((string) $value));
    if ($value === '' || strcasecmp($value, 'No Name') === 0) {
        return $fallback;
    }

    return $value;
};

$upper = function ($value) use ($normalize) {
    $value = $normalize($value, '');

    if ($value === '') {
        return '';
    }

    return function_exists('mb_strtoupper') ? mb_strtoupper($value, 'UTF-8') : strtoupper($value);
};

$studentName = $normalize($student->full_name ?? '', 'Student');
$studentNameDisplay = $upper($studentName);

$studentId = trim((string)($student->student_id_serial ?? ''));
$studentIdDisplay = ($studentId !== '') ? $studentId : '-';

$studentPhoto = (($student->photo ?? '') !== '')
    ? config('constants.app_url') . config('constants.uploads_url_path') . $student->photo
    : config('constants.no_image_avatar');

$classLabel = $normalize($student->class_name ?? '', '-');
$classLabelDisplay = $upper($classLabel);

$guardianName = $normalize($student->father_name ?? '', '');
if ($guardianName === '') {
    $guardianName = $normalize($student->mother_name ?? '', '');
}
if ($guardianName === '') {
    $guardianName = $normalize($student->emergency_name ?? '', '-');
}
if ($guardianName === '') {
    $guardianName = '-';
}
$guardianNameDisplay = ($guardianName === '-') ? '-' : $upper($guardianName);

$dobDisplay = '-';
$dobValue = trim((string)($student->dob ?? ''));
if ($dobValue !== '' && strtotime($dobValue)) {
    $dobDisplay = date('d.m.Y', strtotime($dobValue));
}

$bloodGroup = trim((string)($student->blood_group ?? ''));
$bloodGroupDisplay = ($bloodGroup !== '') ? $upper($bloodGroup) : '-';

$address = $normalize($student->permanent_address ?? '', '-');
$addressDisplay = ($address !== '-') ? $upper($address) : '-';

$pinDisplay = $normalize($student->permanent_pincode ?? '', '-');

$contact = trim((string)($student->father_mobile ?? ''));
if ($contact === '') {
    $contact = trim((string)($student->emergency_phone ?? ''));
}
if ($contact === '') {
    $contact = trim((string)($student->mother_mobile ?? ''));
}
$contactDisplay = ($contact !== '') ? $contact : '-';

$cardBackgroundUrl = trim((string)($cardBackgroundUrl ?? ''));
if ($cardBackgroundUrl === '') {
    $cardBackgroundUrl = config('constants.uploads_url') . 'student/id-card-bg.jpeg';
}
// $cardBackgroundUrl = config('constants.uploads_url') . 'student/id-card-bg.jpeg';
?>
<div class="id-card" style="background-image: url('{{ $cardBackgroundUrl }}');">
    <div class="id-card__content">
        <div class="student-photo-frame">
            <img src="{{ $studentPhoto }}" alt="{{ e($studentName) }}" class="student-photo">
            <div class="student-code">{{ $studentIdDisplay }}</div>
        </div>

        <div class="student-details">
            <div class="detail-list">
                <div class="student-detail student-detail--name">
                    <span class="student-detail-label">NAME:</span>
                    <span class="student-detail-value">{{ $studentNameDisplay }}</span>
                </div>
                <div class="student-detail">
                    <span class="student-detail-label">C/O:</span>
                    <span class="student-detail-value">{{ $guardianNameDisplay }}</span>
                </div>
                <div class="student-detail">
                    <span class="student-detail-label">CLASS:</span>
                    <span class="student-detail-value">{{ $classLabelDisplay }}</span>
                </div>
                <div class="student-detail student-detail--inline">
                    <span class="student-detail-label">D.O.B.:</span>
                    <span class="student-detail-value">{{ $dobDisplay }}</span>
                    <span class="student-detail-separator">|</span>
                    <span class="student-detail-label">B/G:</span>
                    <!-- <span class="student-detail-value">{{ $bloodGroupDisplay }}</span> -->
                    <span class="student-detail-value"></span>
                </div>
                <div class="student-detail student-detail--address">
                    <span class="student-detail-label">ADDRESS:</span>
                    <span class="student-detail-value" style="font-size: 2.00mm;">
                        <span class="student-address-line">{{ $addressDisplay }}</span>
                        <span class="student-address-line student-address-line--pin">PIN: {{ $pinDisplay }}</span>
                    </span>
                </div>
                <div class="student-detail student-detail--contact">
                    <span class="student-detail-label">CONTACT:</span>
                    <span class="student-detail-value">{{ $contactDisplay }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
