<?php
use App\Helpers\Helper;

$siteLogo = ((Helper::getSettingValue('site_logo') != '') ? config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_logo') : config('constants.no_image'));
$creatorName = trim((string)$transaction->creator_name);
$updaterName = trim((string)$transaction->updater_name);
$unitName = trim((string)$transaction->unit_name);
$branchName = trim((string)$transaction->branch_name);
$paymentMode = trim((string)$transaction->payment_mode);
$paymentReference = trim((string)$transaction->payment_reference);
$amount = (float)$transaction->transaction_amount;
$amountInWords = Helper::getIndianCurrency($amount);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ $transaction->txn_no }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?=((Helper::getSettingValue('site_favicon') != '')?config('constants.app_url') . config('constants.uploads_url_path') . Helper::getSettingValue('site_favicon'):env('NO_IMAGE'))?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

        :root {
            --ink: #1a2f45;
            --ink-soft: #62748a;
            --line: #d9e3ed;
            --paper: #ffffff;
            --bg: #f4f7fb;
            --accent: #c39a4f;
            --income: #13864f;
            --expense: #b04533;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: radial-gradient(circle at top right, #e8eef6 0%, #f4f7fb 48%, #f6f9fd 100%);
            color: var(--ink);
            font-family: 'Sora', sans-serif;
            padding: 20px 14px;
        }
        .toolbar {
            max-width: 900px;
            margin: 0 auto 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .toolbar .btn {
            border: 0;
            border-radius: 9px;
            min-height: 38px;
            padding: 0 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }
        .btn-back {
            background: #e5edf5;
            color: #1a2f45;
        }
        .btn-print {
            background: #183a59;
            color: #fff;
        }
        .invoice-shell {
            max-width: 900px;
            margin: 0 auto;
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 42px rgba(16, 33, 52, .12);
        }
        .top-bar {
            height: 8px;
            background: linear-gradient(90deg, #11263a 0%, #1c456c 45%, #c39a4f 100%);
        }
        .invoice-body {
            padding: 22px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            border-bottom: 1px dashed var(--line);
            padding-bottom: 18px;
            margin-bottom: 18px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand img {
            width: 66px;
            height: 66px;
            object-fit: contain;
            border: 1px solid #dce6f0;
            border-radius: 10px;
            padding: 6px;
            background: #fff;
        }
        .brand h1 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            letter-spacing: .2px;
            font-size: 1.65rem;
        }
        .brand p {
            margin: 4px 0 0;
            color: var(--ink-soft);
            font-size: .88rem;
        }
        .meta-box {
            text-align: right;
            min-width: 235px;
        }
        .meta-box .label {
            color: var(--ink-soft);
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .7px;
        }
        .meta-box .value {
            font-size: .96rem;
            font-weight: 700;
            margin-top: 4px;
            margin-bottom: 9px;
        }
        .txn-badge {
            display: inline-block;
            color: #fff;
            font-size: .72rem;
            font-weight: 600;
            border-radius: 999px;
            padding: 5px 10px;
            letter-spacing: .4px;
            background: linear-gradient(120deg, #13864f, #16a565);
        }
        .txn-badge.expense {
            background: linear-gradient(120deg, #9f3f2f, #c45237);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 15px;
        }
        .grid-card {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 13px;
            background: #fbfdff;
        }
        .grid-card .title {
            margin: 0 0 8px;
            color: #51667b;
            font-size: .74rem;
            font-weight: 600;
            letter-spacing: .6px;
            text-transform: uppercase;
        }
        .grid-card p {
            margin: 4px 0;
            font-size: .89rem;
            color: #22384f;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .invoice-table th,
        .invoice-table td {
            border: 1px solid #d7e1ec;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        .invoice-table th {
            background: #163149;
            color: #fff;
            font-size: .78rem;
            letter-spacing: .6px;
            text-transform: uppercase;
        }
        .invoice-table td {
            font-size: .89rem;
            color: #253a50;
        }
        .amount-tag {
            font-size: 1.08rem;
            font-weight: 700;
            color: {{ ($transaction->type == 'INCOME') ? 'var(--income)' : 'var(--expense)' }};
            white-space: nowrap;
        }
        .summary {
            margin-top: 14px;
            display: flex;
            justify-content: flex-end;
        }
        .summary-card {
            width: 320px;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            background: #f8fbff;
        }
        .summary-card .head {
            background: #12304b;
            color: #fff;
            padding: 9px 12px;
            font-size: .81rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .summary-card .body {
            padding: 12px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 7px;
            font-size: .89rem;
        }
        .summary-row strong {
            color: #172c43;
        }
        .amount-words {
            margin-top: 10px;
            font-size: .82rem;
            color: #3f556b;
            line-height: 1.5;
            border-top: 1px dashed #d0dbe6;
            padding-top: 8px;
        }
        .invoice-footer {
            margin-top: 16px;
            border-top: 1px dashed var(--line);
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: .78rem;
            color: #5a6f84;
        }
        @media (max-width: 860px) {
            .invoice-header {
                flex-direction: column;
            }
            .meta-box {
                text-align: left;
                min-width: auto;
            }
            .grid {
                grid-template-columns: 1fr;
            }
            .summary {
                justify-content: stretch;
            }
            .summary-card {
                width: 100%;
            }
        }
        @media print {
            body {
                padding: 0;
                background: #fff;
            }
            .toolbar {
                display: none !important;
            }
            .invoice-shell {
                box-shadow: none;
                border-radius: 0;
                border: 0;
                max-width: none;
                margin: 0;
            }
            .invoice-body {
                padding: 0;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <a href="{{ url('finance/list') }}" class="btn btn-back">
            <i class="fa-solid fa-arrow-left"></i> Back to Ledger
        </a>
        <button type="button" class="btn btn-print" onclick="window.print();">
            <i class="fa-solid fa-print"></i> Print Invoice
        </button>
    </div>

    <div class="invoice-shell">
        <div class="top-bar"></div>
        <div class="invoice-body">
            <div class="invoice-header">
                <div class="brand">
                    <img src="{{ $siteLogo }}" alt="NVGI Logo">
                    <div>
                        <h1>NVGI</h1>
                        <p>Official Transaction Invoice</p>
                    </div>
                </div>
                <div class="meta-box">
                    <div class="label">Invoice Number</div>
                    <div class="value">INV-{{ $transaction->txn_no }}</div>

                    <div class="label">Transaction No</div>
                    <div class="value">{{ $transaction->txn_no }}</div>

                    <span class="txn-badge {{ (($transaction->type == 'EXPENSE')?'expense':'') }}">{{ $transaction->type }}</span>
                </div>
            </div>

            <div class="grid">
                <div class="grid-card">
                    <p class="title">Issued Information</p>
                    <p><strong>Issued At:</strong> {{ (($transaction->transaction_timestamp)?date('d M Y h:i A', strtotime($transaction->transaction_timestamp)):'--') }}</p>
                    <p><strong>Created By:</strong> {{ (($creatorName != '')?$creatorName:'System') }}</p>
                    <p><strong>Last Updated By:</strong> {{ (($updaterName != '')?$updaterName:'System') }}</p>
                </div>
                <div class="grid-card">
                    <p class="title">Transaction Context</p>
                    <p><strong>Unit:</strong> {{ ($unitName != '') ? $unitName : '--' }}</p>
                    <p><strong>Branch:</strong> {{ ($branchName != '') ? $branchName : '--' }}</p>
                    <p><strong>Payment Mode:</strong> {{ ($paymentMode != '') ? $paymentMode : '--' }}</p>
                    <p><strong>Payment Reference:</strong> {{ ($paymentReference != '') ? $paymentReference : '--' }}</p>
                    <p><strong>Fee ID:</strong> {{ (int)$transaction->fee_id }}</p>
                    <p><strong>Logged On:</strong> {{ date('d M Y h:i A', strtotime($transaction->created_at)) }}</p>
                    <p><strong>Status:</strong> {{ ((int)$transaction->status === 1) ? 'ACTIVE' : 'INACTIVE' }}</p>
                </div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 55px;">Sl</th>
                        <th>Description</th>
                        <th style="width: 150px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <strong>{{ $transaction->particulars }}</strong><br>
                            @if($transaction->note != '')
                                <span style="color:#5a6f84; font-size:.82rem;">Note: {{ $transaction->note }}</span>
                            @endif
                        </td>
                        <td class="amount-tag">INR {{ number_format($amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="summary">
                <div class="summary-card">
                    <div class="head">Payment Summary</div>
                    <div class="body">
                        <div class="summary-row">
                            <span>Type</span>
                            <strong>{{ $transaction->type }}</strong>
                        </div>
                        <div class="summary-row">
                            <span>Total Amount</span>
                            <strong>INR {{ number_format($amount, 2) }}</strong>
                        </div>
                        <div class="amount-words">
                            <strong>In Words:</strong> {{ trim($amountInWords) }} Only
                        </div>
                    </div>
                </div>
            </div>

            <div class="invoice-footer">
                <span>This is a computer generated invoice by NVGI Finance Module.</span>
                <span>Generated on {{ date('d M Y h:i A') }}</span>
            </div>
        </div>
    </div>
</body>
</html>
