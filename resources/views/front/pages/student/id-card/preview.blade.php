<?php
use App\Helpers\Helper;
$brand = $brand ?? [];
$students = $students ?? collect();
$selectedCount = (($selected_count ?? 0) !== null) ? (int)$selected_count : 0;
$generatedAt = $generated_at ?? '';
$previewTitle = trim((string)($preview_title ?? ''));
if ($previewTitle === '') {
    $previewTitle = 'Student ID Card Preview';
}
$cardBackgroundUrl = trim((string)($card_background_url ?? ''));
if ($cardBackgroundUrl === '') {
    $cardBackgroundUrl = config('constants.uploads_url') . 'uploads/student/id-card-bg.jpeg';
}
// $cardBackgroundUrl = config('constants.uploads_url') . 'student/id-card-bg.jpeg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $previewTitle }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?=((Helper::getSettingValue('site_favicon') != '')?config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_favicon'):env('NO_IMAGE'))?>" />
    <style>
        :root{
            --paper-bg: #e5e7eb;
            --toolbar-bg: rgba(15, 23, 42, 0.92);
            --toolbar-text: #ffffff;
            --card-bg: #f8c933;
            --card-strip: #4b2f18;
        }
        *{
            box-sizing: border-box;
        }
        html, body{
            margin: 0;
            padding: 0;
            min-height: 100%;
            font-family: "Segoe UI", Arial, sans-serif;
            color: #111827;
            /* background: radial-gradient(circle at top, #f8fafc 0%, #dbe4f0 45%, #cbd5e1 100%); */
        }
        body{
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .preview-page{
            min-height: 100vh;
            padding: 16px;
        }
        .preview-toolbar{
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            border-radius: 16px;
            padding: 14px 16px;
            color: var(--toolbar-text);
            background: var(--toolbar-bg);
            backdrop-filter: blur(10px);
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.22);
            margin-bottom: 16px;
        }
        .preview-toolbar h1{
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: .2px;
        }
        .preview-toolbar .meta{
            font-size: 13px;
            color: rgba(255,255,255,.82);
            margin-top: 4px;
        }
        .toolbar-actions{
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .toolbar-btn{
            appearance: none;
            border: 0;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: transform .15s ease, box-shadow .15s ease, opacity .15s ease;
            box-shadow: 0 10px 24px rgba(0,0,0,.14);
        }
        .toolbar-btn:hover{
            transform: translateY(-1px);
            opacity: .96;
        }
        .toolbar-btn.primary{
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: #ffffff;
        }
        .toolbar-btn.secondary{
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            color: #0f172a;
        }
        .sheet-wrap{
            display: flex;
            justify-content: center;
        }
        .sheet-grid{
            display: grid;
            grid-template-columns: repeat(3, 85.6mm);
            gap: 2mm;
            justify-content: center;
            align-content: start;
            width: fit-content;
            max-width: 100%;
        }
        .id-card{
            width: 85.6mm;
            height: 54mm;
            display: flex;
            overflow: hidden;
            /* border-radius: 3mm; */
            background: linear-gradient(135deg, #f8dc63 0%, #f4c514 55%, #eab308 100%);
            border: 0.35mm solid rgba(75, 47, 24, 0.28);
            box-shadow: 0 5px 14px rgba(15, 23, 42, 0.16);
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .id-card__strip{
            width: 11mm;
            background: linear-gradient(180deg, #4b2f18 0%, #2f1a0c 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            font-size: 4mm;
            font-weight: 900;
            letter-spacing: 0.7mm;
            text-transform: uppercase;
            text-align: center;
        }
        .id-card__main{
            flex: 1;
            padding: 1.8mm 2mm 1.5mm 2mm;
            display: flex;
            flex-direction: column;
            min-width: 0;
            position: relative;
        }
        .brand-row{
            display: flex;
            align-items: flex-start;
            gap: 1.4mm;
            padding-bottom: 1mm;
            margin-bottom: 1mm;
            border-bottom: 0.3mm solid rgba(75, 47, 24, 0.28);
        }
        .brand-logo{
            width: 7.6mm;
            height: 7.6mm;
            border-radius: 50%;
            object-fit: cover;
            background: #ffffff;
            border: 0.3mm solid rgba(75, 47, 24, 0.35);
            flex: 0 0 auto;
        }
        .brand-initials{
            width: 7.6mm;
            height: 7.6mm;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            color: #4b2f18;
            font-size: 2.4mm;
            font-weight: 900;
            border: 0.3mm solid rgba(75, 47, 24, 0.35);
            flex: 0 0 auto;
        }
        .brand-copy{
            flex: 1;
            min-width: 0;
        }
        .brand-name{
            margin: 0;
            color: #1f1305;
            font-size: 4.05mm;
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: .15px;
            text-transform: uppercase;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .brand-tagline{
            margin-top: 0.4mm;
            color: #4b2f18;
            font-size: 2.15mm;
            font-weight: 700;
            line-height: 1.15;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .id-card__body{
            display: flex;
            gap: 2mm;
            min-width: 0;
            flex: 1;
        }
        .photo-stack{
            width: 18mm;
            flex: 0 0 18mm;
            display: flex;
            flex-direction: column;
            gap: 0.8mm;
            align-items: center;
        }
        .student-photo{
            width: 18mm;
            height: 22mm;
            object-fit: cover;
            border: 0.3mm solid #4b2f18;
            background: #ffffff;
        }
        .student-code{
            width: 100%;
            /* border-radius: 1mm; */
            padding: 0.8mm 1mm;
            background: #4b2f18;
            color: #ffffff;
            text-align: center;
            font-size: 2.3mm;
            font-weight: 900;
            letter-spacing: 0.2mm;
            line-height: 1.15;
        }
        .student-details{
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1mm;
        }
        .student-name{
            margin: 0 0 0.9mm 0;
            font-size: 3.55mm;
            font-weight: 900;
            line-height: 1.1;
            color: #1f1305;
            text-transform: uppercase;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .detail-list{
            display: flex;
            flex-direction: column;
            gap: 0.55mm;
        }
        .student-detail{
            display: flex;
            gap: 1mm;
            font-size: 2.35mm;
            line-height: 1.25;
            color: #2c1a00;
            font-weight: 700;
        }
        .student-detail-label{
            min-width: 21mm;
            white-space: nowrap;
        }
        .student-detail-value{
            font-weight: 800;
            min-width: 0;
            word-break: break-word;
        }
        .card-footer{
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1.5mm;
            margin-top: 1mm;
        }
        .barcode-box{
            flex: 1;
            min-width: 0;
        }
        .barcode-bars{
            height: 4.5mm;
            /* border-radius: 0.7mm; */
            background:
                repeating-linear-gradient(
                    90deg,
                    #1f1305 0,
                    #1f1305 0.35mm,
                    transparent 0.35mm,
                    transparent 0.75mm,
                    #1f1305 0.75mm,
                    #1f1305 1.05mm,
                    transparent 1.05mm,
                    transparent 1.55mm
                );
            opacity: 0.92;
        }
        .barcode-text{
            margin-top: 0.45mm;
            font-size: 2.1mm;
            font-weight: 800;
            text-align: center;
            letter-spacing: 0.25mm;
            color: #2c1a00;
        }
        .sign-box{
            width: 18mm;
            flex: 0 0 18mm;
            text-align: center;
            color: #2c1a00;
        }
        .sign-line{
            border-top: 0.3mm solid rgba(44, 26, 0, 0.6);
            padding-top: 0.8mm;
            font-size: 1.8mm;
            font-weight: 800;
            white-space: nowrap;
        }
        .id-card{
            position: relative;
            width: 85.6mm;
            height: 54mm;
            overflow: hidden;
            display: block;
            background-color: #f3c80c;
            background-position: center center;
            background-repeat: no-repeat;
            background-size: 100% 100%;
            /* border-radius: 0.8mm; */
            border: 0.25mm solid rgba(42, 23, 8, 0.16);
            box-shadow: 0 5px 14px rgba(15, 23, 42, 0.16);
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .id-card__content{
            position: absolute;
            inset: 0;
        }
        .student-photo-frame{
            position: absolute;
            left: 15.0mm;
            top: 17mm;
            width: 21.2mm;
            height: 28.8mm;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: #ffffff;
            border: 0.35mm solid #2b1709;
            z-index: 2;
        }
        .student-photo{
            width: 100%;
            flex: 1;
            min-height: 0;
            object-fit: cover;
            object-position: center top;
            display: block;
        }
        .student-code{
            min-height: 5.0mm;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5mm 0.8mm;
            background: #2b1709;
            color: #ffffff;
            font-family: "Arial Narrow", Arial, sans-serif;
            font-size: 2.05mm;
            font-weight: 900;
            letter-spacing: 0.18mm;
            line-height: 1;
            white-space: nowrap;
        }
        .student-details{
            position: absolute;
            top: 17.1mm;
            left: 38.0mm;
            right: 4.6mm;
            z-index: 2;
            display: flex;
            flex-direction: column;
            gap: 0.6mm;
            min-width: 0;
            color: #2b1607;
            font-family: "Arial Narrow", Arial, sans-serif;
            font-size: 2.55mm;
            line-height: 1.05;
            font-weight: 900;
            text-transform: uppercase;
        }
        .detail-list{
            display: flex;
            flex-direction: column;
            gap: 0.6mm;
        }
        .student-detail{
            display: flex;
            align-items: flex-start;
            gap: 0.9mm;
            min-width: 0;
            font-size: 2.55mm;
            line-height: 1.02;
            font-weight: 900;
        }
        .student-detail--name{
            font-size: 2.65mm;
        }
        .student-detail--name .student-detail-label{
            min-width: 12.3mm;
        }
        .student-detail--name .student-detail-value{
            font-size: 2.7mm;
        }
        .student-detail-label{
            flex: 0 0 auto;
            min-width: 12.0mm;
            white-space: nowrap;
        }
        .student-detail-value{
            flex: 1;
            min-width: 0;
            word-break: break-word;
        }
        .student-detail--inline{
            flex-wrap: nowrap;
            align-items: baseline;
            gap: 0.7mm;
            white-space: nowrap;
        }
        .student-detail--inline .student-detail-label{
            min-width: auto;
        }
        .student-detail-separator{
            flex: 0 0 auto;
            font-weight: 900;
            padding: 0 0.25mm;
        }
        .student-detail--address{
            gap: 0.8mm;
        }
        .student-detail--address .student-detail-label{
            min-width: 14.3mm;
        }
        .student-detail--address .student-detail-value{
            display: flex;
            flex-direction: column;
            gap: 0.15mm;
        }
        .student-address-line{
            display: block;
            width: 100%;
        }
        .student-address-line--pin{
            white-space: nowrap;
        }
        .student-detail--contact .student-detail-label{
            min-width: 13.8mm;
        }
        @media print{
            body{
                background: #ffffff;
            }
            .preview-page{
                padding: 0;
            }
            .preview-toolbar{
                display: none !important;
            }
            .sheet-wrap{
                display: block;
            }
            .sheet-grid{
                gap: 4mm;
                grid-template-columns: repeat(3, 85.6mm);
            }
            .id-card{
                box-shadow: none;
            }
            @page{
                size: A4 landscape;
                margin: 10mm 8mm;
            }
        }
        @media screen and (max-width: 1200px){
            .sheet-grid{
                transform: scale(0.95);
                transform-origin: top center;
            }
        }
        @media screen and (max-width: 992px){
            .sheet-grid{
                display: flex;
                flex-direction: column;
                gap: 14px;
                transform: none;
                width: 100%;
            }
            .id-card{
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="preview-page">
        <div class="preview-toolbar no-print">
            <div>
                <h1>{{ $previewTitle }}</h1>
                <div class="meta">{{ $selectedCount }} card{{ ($selectedCount === 1) ? '' : 's' }} ready for printing @if($generatedAt) | Generated {{ $generatedAt }} @endif</div>
            </div>
            <div class="toolbar-actions">
                <a href="{{ route('student.id-card.index') }}" class="toolbar-btn secondary">Back to Search</a>
                <button type="button" class="toolbar-btn primary" onclick="window.print()">Print</button>
            </div>
        </div>

        <div class="sheet-wrap">
            <div class="sheet-grid">
                @foreach($students as $student)
                    @include('front.pages.student.id-card.partials.card', [
                        'student' => $student,
                        'brand' => $brand,
                        'cardBackgroundUrl' => $cardBackgroundUrl,
                    ])
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>
