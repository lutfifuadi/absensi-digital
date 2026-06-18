@extends('layouts/layoutMaster')

@section('title', 'Asisten AI')

@section('page-style')
<style>
    /* ── ANIMATIONS ────────────────────────────────────────── */
    @keyframes slideInUp {
        from { opacity: 0; transform: translateY(15px); }
        to   { opacity: 1; transform: translateY(0);   }
    }
    .slide-in-up {
        animation: slideInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .slide-in-up-delay-1 {
        animation: slideInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
    }

    /* ── HERO LOGO PLACEHOLDER & GLOW ─────────────────────── */
    .das-hero__logo-wrapper {
        position: relative;
    }
    .das-hero__logo-placeholder {
        width: 64px;
        height: 64px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(255, 255, 255, 0.15);
        font-size: 1.8rem;
        position: relative;
        z-index: 2;
    }
    .das-hero__logo-glow {
        position: absolute;
        inset: -5px;
        background: var(--das-primary, #7367f0);
        filter: blur(15px);
        opacity: 0.25;
        z-index: 1;
        border-radius: 50%;
    }

    /* ── CHAT PANEL OVERRIDES ──────────────────────────────── */
    .chat-panel-body {
        height: 500px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        scroll-behavior: smooth;
    }

    /* User bubble */
    .chat-bubble-user {
        background: linear-gradient(135deg, #7367f0, #9e95f5);
        color: #fff;
        border-radius: 5px;
        padding: 10px 16px;
        max-width: 80%;
        margin-bottom: 10px;
        word-wrap: break-word;
        box-shadow: 0 2px 8px rgba(115, 103, 240, 0.2);
    }

    /* AI bubble */
    .chat-bubble-ai {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--das-border);
        border-radius: 5px;
        padding: 10px 16px;
        max-width: 80%;
        margin-bottom: 10px;
        word-wrap: break-word;
        backdrop-filter: blur(6px);
    }
    .chat-bubble-ai p {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0.5rem;
    }
    .markdown-body p:last-child {
        margin-bottom: 0;
    }
    .markdown-body ul, .markdown-body ol {
        margin-bottom: 0.5rem;
        padding-left: 1.25rem;
    }
    .markdown-body li {
        margin-bottom: 0.25rem;
    }

    /* Timestamp */
    .chat-ts {
        font-size: 10px;
        opacity: 0.5;
        margin-top: 4px;
        display: block;
    }
    .chat-ts-user {
        color: rgba(255, 255, 255, 0.7);
    }
    .chat-ts-ai {
        color: rgba(255, 255, 255, 0.4);
    }

    /* Empty state icon */
    .chat-empty-icon {
        font-size: 4rem;
        color: rgba(255, 255, 255, 0.1);
    }

    /* ── EXAMPLE CARD ITEMS ────────────────────────────────── */
    .example-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--das-border);
        border-radius: var(--das-radius);
        backdrop-filter: blur(6px);
        transition: all 0.25s ease;
        height: 100%;
    }
    .example-card:hover {
        border-color: var(--das-border-hover);
        background: rgba(255, 255, 255, 0.06);
        transform: translateY(-2px);
    }
    .example-card__icon {
        width: 36px;
        height: 36px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .example-card__title {
        font-size: 0.82rem;
        font-weight: 700;
        color: #e2e8f0;
    }
    .example-card__item {
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.55);
        padding: 3px 0;
        list-style: none;
    }
    .example-card__item::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: var(--das-primary);
        margin-right: 8px;
        vertical-align: middle;
        opacity: 0.6;
    }
    .example-card__item:hover {
        color: rgba(255, 255, 255, 0.85);
    }

    /* ── DAS ALERT FOR ERROR ───────────────────────────────── */
    .das-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.75rem 1rem;
        border-radius: 5px;
        border: 1px solid;
        backdrop-filter: blur(6px);
        font-size: 0.82rem;
    }
    .das-alert--warning {
        background: rgba(255, 159, 67, 0.06);
        border-color: rgba(255, 159, 67, 0.2);
        color: rgba(255, 255, 255, 0.75);
    }
    .das-alert--warning .das-alert__icon {
        color: #ff9f43;
        font-size: 1rem;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* ── FOOTER DIVIDER ────────────────────────────────────── */
    .chat-footer {
        border-top: 1px solid var(--das-border);
        padding: 0.85rem 1.25rem;
    }

    /* ── GHOST BUTTON DANGER ───────────────────────────────── */
    .das-btn--ghost-danger {
        background: transparent;
        border-color: rgba(234, 84, 85, 0.3);
        color: #ea5455 !important;
    }
    .das-btn--ghost-danger:hover {
        background: rgba(234, 84, 85, 0.12);
        color: #ea5455 !important;
        border-color: rgba(234, 84, 85, 0.5);
    }

    /* ── TYPING INDICATOR ────────────────────────────────── */
    .typing-indicator {
        display: flex;
        gap: 3px;
    }
    .typing-indicator span {
        width: 4px;
        height: 4px;
        background: var(--das-primary);
        border-radius: 50%;
        animation: typingBounce 1.4s infinite ease-in-out both;
    }
    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
    @keyframes typingBounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    /* ── RESPONSIVE ────────────────────────────────────────── */
    @media (max-width: 767px) {
        .chat-bubble-user,
        .chat-bubble-ai {
            max-width: 90%;
        }
        .chat-panel-body {
            height: 400px;
        }
    }
</style>
@endsection

@section('content')

<div class="das-hero mb-4 slide-in-up">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder">
                    <i class="ti tabler-message-chatbot text-info"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>
            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot"></span>
                    Kecerdasan Buatan
                </div>
                <h4 class="das-hero__title text-gradient-gold">Asisten AI</h4>
                <p class="das-hero__subtitle">Tanya atau edit data dengan percakapan natural menggunakan Google Gemini AI.</p>
            </div>
        </div>
        <div class="das-hero__actions" style="gap:0.5rem;">
            {{-- Kosong, atau bisa ditambahkan nanti --}}
        </div>
    </div>
</div>

<div class="row slide-in-up">
    <div class="col-12">
        @livewire('admin.ai-chat')
    </div>
</div>

<div class="row mt-3 slide-in-up-delay-1">
    <div class="col-12">
        <div class="das-panel">
            <div class="das-panel__head">
                <div class="das-panel__title">
                    <span class="das-panel__icon-dot --info"></span>
                    Contoh Perintah
                </div>
            </div>
            <div class="das-panel__body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="example-card p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="example-card__icon" style="background:rgba(115,103,240,0.15);color:var(--das-primary);">
                                    <i class="ti tabler-users"></i>
                                </div>
                                <span class="example-card__title">Data Siswa</span>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="example-card__item">Cari siswa bernama Andi</li>
                                <li class="example-card__item">Tampilkan data siswa NISN 0102039759</li>
                                <li class="example-card__item">Edit alamat siswa ID 5 menjadi Jl. Merdeka No. 10</li>
                                <li class="example-card__item">Ubah status siswa ID 3 menjadi nonaktif</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="example-card p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="example-card__icon" style="background:rgba(40,199,111,0.15);color:var(--das-success);">
                                    <i class="ti tabler-school"></i>
                                </div>
                                <span class="example-card__title">Data Guru & Kelas</span>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="example-card__item">Cari guru dengan NIP 197002021993011004</li>
                                <li class="example-card__item">Tampilkan semua data guru</li>
                                <li class="example-card__item">Edit jabatan guru ID 2 menjadi Kepala Sekolah</li>
                                <li class="example-card__item">Cari kelas X IPA 1</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="example-card p-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="example-card__icon" style="background:rgba(255,159,67,0.15);color:var(--das-warning);">
                                    <i class="ti tabler-chart-bar"></i>
                                </div>
                                <span class="example-card__title">Statistik</span>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <li class="example-card__item">Berapa total siswa?</li>
                                <li class="example-card__item">Tampilkan statistik data</li>
                                <li class="example-card__item">Total guru dan kelas</li>
                                <li class="example-card__item">Berapa jumlah user?</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
