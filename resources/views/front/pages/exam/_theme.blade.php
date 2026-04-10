<style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap');

    .exam-ui {
        --exam-ink: #102133;
        --exam-soft: #586a7d;
        --exam-border: #d7e2ec;
        --exam-surface: #ffffff;
        --exam-primary: #143b5f;
        --exam-primary-strong: #102f4a;
        --exam-secondary: #0f766e;
        --exam-accent: #c9a45b;
        font-family: 'Manrope', sans-serif;
        color: var(--exam-ink);
        padding-bottom: 24px;
    }

    .exam-ui a {
        text-decoration: none;
    }

    .exam-hero-card {
        position: relative;
        overflow: hidden;
        border: 0;
        border-radius: 20px;
        padding: 24px;
        background: linear-gradient(135deg, #10253a 0%, #173c5d 55%, #0f6b67 100%);
        color: #fff;
        box-shadow: 0 20px 42px rgba(13, 31, 48, .22);
        margin-bottom: 18px;
    }

    .exam-hero-card::after {
        content: '';
        position: absolute;
        inset: -12% -6% auto auto;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, .18) 0%, rgba(255, 255, 255, 0) 70%);
        pointer-events: none;
    }

    .exam-kicker {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 12px;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .22);
        color: rgba(255, 255, 255, .94);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .7px;
        text-transform: uppercase;
    }

    .exam-hero-card h2 {
        font-family: 'Playfair Display', serif;
        margin: 10px 0 8px;
        font-size: 1.95rem;
        letter-spacing: .2px;
    }

    .exam-hero-card p {
        margin: 0;
        max-width: 860px;
        color: rgba(255, 255, 255, .9);
        font-size: .95rem;
        line-height: 1.65;
    }

    .exam-hero-actions .btn {
        border-radius: 999px;
        font-weight: 700;
        padding: .55rem 1rem;
        box-shadow: none;
    }

    .exam-hero-actions .btn-light {
        color: var(--exam-primary);
    }

    .exam-stat-card {
        border: 1px solid var(--exam-border);
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 12px 28px rgba(18, 33, 53, .06);
        padding: 16px 18px;
        height: 100%;
    }

    .exam-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(20, 59, 95, .12), rgba(15, 118, 110, .12));
        color: var(--exam-primary);
        font-size: 1.05rem;
        flex: 0 0 auto;
    }

    .exam-stat-label {
        color: var(--exam-soft);
        font-size: .72rem;
        letter-spacing: .75px;
        text-transform: uppercase;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .exam-stat-value {
        color: var(--exam-ink);
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
        margin: 0;
    }

    .exam-stat-positive {
        color: #15803d;
    }

    .exam-stat-warning {
        color: #b45309;
    }

    .exam-stat-mini {
        color: var(--exam-soft);
        font-size: .76rem;
        margin-top: 3px;
    }

    .exam-card {
        border: 1px solid var(--exam-border);
        border-radius: 18px;
        box-shadow: 0 14px 32px rgba(18, 33, 53, .06);
        overflow: hidden;
        background: var(--exam-surface);
    }

    .exam-card + .exam-card {
        margin-top: 16px;
    }

    .exam-card .card-header {
        padding: 16px 18px;
        border-bottom: 1px solid var(--exam-border);
    }

    .exam-card .card-body {
        padding: 18px;
    }

    .exam-card-header {
        background: linear-gradient(90deg, #f8fbff 0%, #edf7ff 48%, #eefaf7 100%);
    }

    .exam-card-header h5 {
        margin: 0;
        color: var(--exam-ink);
        font-size: 1rem;
        font-weight: 800;
    }

    .exam-card-subtitle {
        color: var(--exam-soft);
        font-size: .82rem;
        margin-top: 4px;
    }

    .exam-section-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        border: 1px solid rgba(20, 59, 95, .12);
        background: #fff;
        color: var(--exam-primary);
        font-size: .76rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .exam-note {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 16px;
        background: linear-gradient(90deg, #edf7ff 0%, #eefbf7 100%);
        border: 1px solid #cfe1ef;
        color: #18324a;
        font-size: .9rem;
    }

    .exam-inline-hint {
        color: var(--exam-soft);
        font-size: .78rem;
        margin-top: 4px;
    }

    .exam-form-table-wrap,
    .exam-table-wrap {
        border: 1px solid var(--exam-border);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    .exam-form-table,
    .exam-table {
        margin-bottom: 0 !important;
    }

    .exam-form-table thead th,
    .exam-table thead th {
        background: #152d45;
        color: #fff;
        border-bottom: 0;
        font-size: .76rem;
        letter-spacing: .6px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .exam-form-table tbody td,
    .exam-table tbody td {
        vertical-align: middle;
        color: #203040;
        font-size: .88rem;
    }

    .exam-form-table tbody tr:nth-child(odd),
    .exam-table tbody tr:nth-child(odd) {
        background: #fbfdff;
    }

    .exam-form-table tbody tr:hover,
    .exam-table tbody tr:hover {
        background: #f2f8ff;
    }

    .exam-ui .form-control,
    .exam-ui .form-select {
        min-height: 40px;
        border-radius: 10px;
        border-color: #d3deea;
    }

    .exam-ui .form-control:focus,
    .exam-ui .form-select:focus {
        border-color: #7da7ca;
        box-shadow: 0 0 0 .2rem rgba(20, 59, 95, .12);
    }

    .exam-row-add-btn,
    .exam-row-remove-btn,
    .exam-action-btn,
    .exam-submit-btn {
        border-radius: 999px;
        font-weight: 700;
    }

    .exam-row-add-btn,
    .exam-row-remove-btn,
    .exam-action-btn {
        min-height: 36px;
    }

    .exam-submit-btn {
        border: 0;
        min-height: 44px;
        background: linear-gradient(135deg, #143b5f 0%, #1a8d76 100%);
        color: #fff;
        box-shadow: 0 10px 18px rgba(20, 59, 95, .18);
    }

    .exam-submit-btn:hover,
    .exam-submit-btn:focus {
        background: linear-gradient(135deg, #102f4a 0%, #167465 100%);
        color: #fff;
    }

    .exam-table-meta {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .exam-table-name {
        font-size: .95rem;
        font-weight: 800;
        color: var(--exam-ink);
    }

    .exam-table-subtext {
        font-size: .79rem;
        color: var(--exam-soft);
    }

    .exam-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .exam-tag {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid #dce6f0;
        background: #fff;
        color: #163b57;
        font-size: .74rem;
        font-weight: 800;
    }

    .exam-description {
        color: #4b5f73;
        font-size: .87rem;
        line-height: 1.55;
    }

    .exam-status-pill {
        border-radius: 999px;
        padding: 6px 11px;
        font-size: .74rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .exam-status-active {
        background: rgba(34, 197, 94, .12);
        color: #15803d;
    }

    .exam-status-blocked {
        background: rgba(245, 158, 11, .14);
        color: #b45309;
    }

    .exam-action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .exam-action-btn {
        padding: 7px 12px;
        box-shadow: none;
    }

    .exam-empty-state {
        padding: 30px 18px;
        text-align: center;
        color: var(--exam-soft);
    }

    .exam-ui .dataTables_wrapper .dt-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .exam-ui .dataTables_wrapper .dt-buttons .btn {
        border-radius: 999px;
        font-weight: 700;
    }

    .exam-ui .dataTables_wrapper .dataTables_filter {
        margin-bottom: 14px;
        color: var(--exam-soft);
    }

    .exam-ui .dataTables_wrapper .dataTables_filter label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .6px;
    }

    .exam-ui .dataTables_wrapper .dataTables_filter input,
    .exam-ui .dataTables_wrapper .dataTables_length select {
        min-height: 38px;
        border-radius: 999px;
        border: 1px solid #d3deea;
        padding: .45rem .85rem;
        box-shadow: none;
    }

    .exam-ui .dataTables_wrapper .dataTables_info,
    .exam-ui .dataTables_wrapper .dataTables_paginate {
        color: var(--exam-soft);
        font-size: .82rem;
    }

    .exam-ui .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 999px !important;
    }

    @media (max-width: 767px) {
        .exam-hero-card {
            padding: 18px;
        }

        .exam-hero-card h2 {
            font-size: 1.5rem;
        }

        .exam-card .card-header,
        .exam-card .card-body {
            padding: 14px 16px;
        }

        .exam-stat-card {
            padding: 14px;
        }

        .exam-table-name {
            font-size: .92rem;
        }

        .exam-action-group {
            gap: 6px;
        }
    }
</style>
