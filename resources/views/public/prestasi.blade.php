<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Papan Prestasi — {{ $namaSekolah }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    :root {
      --bg:       #060a12;
      --surface:  #0d1321;
      --surface2: #111827;
      --border:   rgba(255,255,255,0.06);
      --gold:     #ffd700;
      --silver:   #c0c8d8;
      --bronze:   #cd7f32;
      --primary:  #7367f0;
      --success:  #28c76f;
      --warning:  #ff9f43;
      --danger:   #ea5455;
      --text:     #e2e8f0;
      --muted:    #64748b;
      --radius:   5px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      height: 100dvh;
      max-height: 100dvh;
      overflow: hidden;
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      -webkit-font-smoothing: antialiased;
      display: flex;
      flex-direction: column;
    }

    ::-webkit-scrollbar { width: 3px; height: 3px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(115,103,240,0.4); border-radius: 2px; }

    /* ═══════════════════════════════════
       HEADER — fixed height
    ═══════════════════════════════════ */
    .header {
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      padding: 0.6rem 1.25rem;
      background: linear-gradient(135deg, #10152a 0%, #0d1321 100%);
      border-bottom: 1px solid rgba(115,103,240,0.2);
      box-shadow: 0 2px 16px rgba(0,0,0,0.4);
      z-index: 100;
    }

    .header-brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      min-width: 0;
    }

    .header-logo {
      width: 44px;
      height: 44px;
      border-radius: var(--radius);
      background: rgba(255,215,0,0.1);
      border: 1px solid rgba(255,215,0,0.25);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      color: var(--gold);
      flex-shrink: 0;
      box-shadow: 0 0 20px rgba(255,215,0,0.15);
      overflow: hidden;
    }

    .header-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      padding: 4px;
    }

    .header-title h1 {
      font-size: 1rem;
      font-weight: 800;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 260px;
    }

    .header-title p {
      font-size: 0.68rem;
      color: var(--muted);
      margin-top: 1px;
      font-weight: 500;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .header-clock {
      text-align: center;
      flex-shrink: 0;
    }

    #live-clock {
      font-size: 1.75rem;
      font-weight: 900;
      color: #fff;
      letter-spacing: 2px;
      font-variant-numeric: tabular-nums;
      line-height: 1;
    }

    #live-date {
      font-size: 0.68rem;
      color: var(--muted);
      margin-top: 3px;
      font-weight: 500;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-shrink: 0;
    }

    .live-badge {
      display: flex;
      align-items: center;
      gap: 0.4rem;
      background: rgba(234,84,85,0.12);
      border: 1px solid rgba(234,84,85,0.35);
      border-radius: var(--radius);
      padding: 5px 12px;
      font-size: 0.7rem;
      font-weight: 700;
      color: var(--danger);
      letter-spacing: 0.5px;
    }

    .live-dot {
      width: 7px;
      height: 7px;
      background: var(--danger);
      border-radius: 50%;
      animation: pulse 1.4s ease-in-out infinite;
    }

    @keyframes pulse {
      0%,100% { opacity:1; transform:scale(1); }
      50% { opacity:0.4; transform:scale(1.5); }
    }

    .btn-refresh {
      width: 34px;
      height: 34px;
      border-radius: var(--radius);
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      color: var(--muted);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 1rem;
      transition: all 0.2s ease;
    }

    .btn-refresh:hover {
      background: rgba(115,103,240,0.15);
      border-color: rgba(115,103,240,0.4);
      color: var(--primary);
    }

    .btn-refresh.spinning i {
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin { 100% { transform: rotate(360deg); } }

    /* ═══════════════════════════════════
       ANNOUNCE BAR — fixed height
    ═══════════════════════════════════ */
    .announce-bar {
      flex-shrink: 0;
      background: rgba(115,103,240,0.08);
      border-bottom: 1px solid rgba(115,103,240,0.15);
      padding: 4px 1.25rem;
      overflow: hidden;
      white-space: nowrap;
    }

    .announce-bar marquee {
      font-size: 0.76rem;
      color: rgba(255,255,255,0.55);
      font-weight: 500;
    }

    .announce-bar marquee span {
      color: var(--gold);
      margin: 0 0.5rem;
    }

    /* ═══════════════════════════════════
       MAIN WRAPPER — flex grow, overflow scroll
    ═══════════════════════════════════ */
    .main-wrap {
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
      padding: 0.75rem 1rem 0.75rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    /* ═══════════════════════════════════
       STATS ROW
    ═══════════════════════════════════ */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 0.6rem;
      flex-shrink: 0;
    }

    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 0.75rem 1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
    }

    .stat-card--gold::before  { background: linear-gradient(90deg, var(--gold), transparent); }
    .stat-card--green::before { background: linear-gradient(90deg, var(--success), transparent); }
    .stat-card--purple::before { background: linear-gradient(90deg, var(--primary), transparent); }

    .stat-card__icon {
      width: 40px;
      height: 40px;
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0;
    }

    .stat-card--gold .stat-card__icon   { background: rgba(255,215,0,0.1);    color: var(--gold); }
    .stat-card--green .stat-card__icon  { background: rgba(40,199,111,0.1);   color: var(--success); }
    .stat-card--purple .stat-card__icon { background: rgba(115,103,240,0.1);  color: var(--primary); }

    .stat-card__val {
      font-size: 1.6rem;
      font-weight: 900;
      color: #fff;
      line-height: 1;
    }

    .stat-card__label {
      font-size: 0.65rem;
      color: var(--muted);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 3px;
    }

    /* ═══════════════════════════════════
       CONTENT GRID — flex grow, panels scroll internal
    ═══════════════════════════════════ */
    .content-grid {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 0.75rem;
      flex: 1;
      min-height: 0;
    }

    /* ═══════════════════════════════════
       PANEL — internal scroll
    ═══════════════════════════════════ */
    .panel {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      min-height: 0;
    }

    .panel__head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.6rem 1rem;
      border-bottom: 1px solid var(--border);
      background: rgba(255,255,255,0.02);
      flex-shrink: 0;
    }

    .panel__body-scroll {
      overflow-y: auto;
      flex: 1;
      min-height: 0;
    }

    .panel__title {
      font-size: 0.8rem;
      font-weight: 700;
      color: rgba(255,255,255,0.7);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .panel__dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .panel__dot--gold   { background: var(--gold); box-shadow: 0 0 6px var(--gold); }
    .panel__dot--green  { background: var(--success); box-shadow: 0 0 6px var(--success); }
    .panel__dot--purple { background: var(--primary); box-shadow: 0 0 6px var(--primary); }

    /* ═══════════════════════════════════
       TAB SWITCH
    ═══════════════════════════════════ */
    .tab-switch {
      display: flex;
      background: rgba(255,255,255,0.04);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 3px;
      gap: 2px;
    }

    .tab-btn {
      padding: 5px 14px;
      border: none;
      border-radius: 3px;
      font-size: 0.72rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s ease;
      color: var(--muted);
      background: transparent;
      letter-radius: 0.3px;
    }

    .tab-btn.active {
      background: rgba(115,103,240,0.2);
      color: #fff;
      border: 1px solid rgba(115,103,240,0.35);
    }

    /* ═══════════════════════════════════
       PODIUM — TOP 3
    ═══════════════════════════════════ */
    .podium-wrap {
      display: flex;
      align-items: flex-end;
      justify-content: center;
      gap: 0.6rem;
      padding: 0.875rem 1rem 0.75rem;
      flex-shrink: 0;
    }

    .podium-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.4rem;
      padding: 0.75rem 0.625rem;
      border-radius: var(--radius);
      border: 1px solid var(--border);
      position: relative;
      min-width: 0;
      flex: 1;
      max-width: 160px;
    }

    .podium-card:hover { transform: translateY(-3px); }

    .podium-card--gold {
      background: linear-gradient(180deg, rgba(255,215,0,0.1) 0%, rgba(255,215,0,0.03) 100%);
      border-color: rgba(255,215,0,0.25);
      box-shadow: 0 0 30px rgba(255,215,0,0.1), inset 0 1px 0 rgba(255,215,0,0.15);
      flex: 1.2;
    }

    .podium-card--silver {
      background: linear-gradient(180deg, rgba(192,200,216,0.08) 0%, rgba(192,200,216,0.02) 100%);
      border-color: rgba(192,200,216,0.15);
    }

    .podium-card--bronze {
      background: linear-gradient(180deg, rgba(205,127,50,0.08) 0%, rgba(205,127,50,0.02) 100%);
      border-color: rgba(205,127,50,0.15);
    }

    .podium-crown {
      font-size: 1.5rem;
      color: var(--gold);
      filter: drop-shadow(0 0 8px rgba(255,215,0,0.6));
      margin-bottom: -4px;
    }

    .podium-avatar {
      width: 42px;
      height: 42px;
      border-radius: var(--radius);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
      font-weight: 800;
      flex-shrink: 0;
      position: relative;
    }

    .podium-avatar--gold   { background: rgba(255,215,0,0.15); color: var(--gold); border: 2px solid rgba(255,215,0,0.4); }
    .podium-avatar--silver { background: rgba(192,200,216,0.1); color: var(--silver); border: 2px solid rgba(192,200,216,0.25); }
    .podium-avatar--bronze { background: rgba(205,127,50,0.1); color: var(--bronze); border: 2px solid rgba(205,127,50,0.25); }

    .podium-rank {
      position: absolute;
      top: -8px;
      right: -8px;
      width: 20px;
      height: 20px;
      border-radius: 3px;
      font-size: 0.6rem;
      font-weight: 900;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .podium-rank--gold   { background: var(--gold);   color: #1a1a2e; }
    .podium-rank--silver { background: var(--silver); color: #1a1a2e; }
    .podium-rank--bronze { background: var(--bronze); color: #fff; }

    .podium-name {
      font-size: 0.7rem;
      font-weight: 700;
      color: #fff;
      text-align: center;
      line-height: 1.3;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      width: 100%;
    }

    .podium-kelas {
      font-size: 0.58rem;
      color: var(--muted);
      font-weight: 600;
    }

    .podium-score {
      font-size: 1.1rem;
      font-weight: 900;
      line-height: 1;
    }

    .podium-score--gold   { color: var(--gold); text-shadow: 0 0 12px rgba(255,215,0,0.5); }
    .podium-score--silver { color: var(--silver); }
    .podium-score--bronze { color: var(--bronze); }

    .podium-score-label {
      font-size: 0.58rem;
      color: var(--muted);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .podium-badges {
      display: flex;
      gap: 3px;
      justify-content: center;
      flex-wrap: wrap;
      min-height: 20px;
    }

    .mini-badge {
      width: 22px;
      height: 22px;
      border-radius: 3px;
      background: rgba(255,215,0,0.08);
      color: var(--gold);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.65rem;
    }

    /* ═══════════════════════════════════
       TABLE
    ═══════════════════════════════════ */
    .p-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.78rem;
    }

    .p-table thead tr {
      border-bottom: 1px solid var(--border);
    }

    .p-table thead th {
      padding: 0.6rem 0.875rem;
      text-align: left;
      font-size: 0.65rem;
      font-weight: 700;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      white-space: nowrap;
    }

    .p-table tbody tr {
      border-bottom: 1px solid rgba(255,255,255,0.03);
      transition: background 0.15s;
    }

    .p-table tbody tr:hover {
      background: rgba(255,255,255,0.025);
    }

    .p-table tbody td {
      padding: 0.65rem 0.875rem;
      vertical-align: middle;
    }

    .rank-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px;
      height: 28px;
      border-radius: var(--radius);
      font-weight: 800;
      font-size: 0.7rem;
      background: rgba(255,255,255,0.04);
      color: var(--muted);
    }

    .student-avatar {
      width: 30px;
      height: 30px;
      border-radius: var(--radius);
      background: rgba(115,103,240,0.12);
      border: 1px solid rgba(115,103,240,0.2);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.65rem;
      font-weight: 800;
      color: #a5a2f7;
      flex-shrink: 0;
    }

    .kelas-chip {
      display: inline-block;
      padding: 2px 8px;
      border-radius: var(--radius);
      font-size: 0.62rem;
      font-weight: 600;
      background: rgba(255,255,255,0.04);
      color: var(--muted);
      border: 1px solid rgba(255,255,255,0.06);
    }

    .progress-wrap {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .progress-bar-outer {
      flex: 1;
      height: 4px;
      background: rgba(255,255,255,0.05);
      border-radius: 2px;
      overflow: hidden;
      min-width: 60px;
    }

    .progress-bar-inner {
      height: 100%;
      border-radius: 2px;
      transition: width 0.6s ease;
    }

    .status-chip {
      display: inline-block;
      padding: 3px 8px;
      border-radius: var(--radius);
      font-size: 0.62rem;
      font-weight: 700;
      letter-spacing: 0.3px;
    }

    .status-chip--good    { background: rgba(40,199,111,0.12); color: var(--success); border: 1px solid rgba(40,199,111,0.2); }
    .status-chip--medium  { background: rgba(255,159,67,0.12); color: var(--warning); border: 1px solid rgba(255,159,67,0.2); }
    .status-chip--bad     { background: rgba(234,84,85,0.12); color: var(--danger); border: 1px solid rgba(234,84,85,0.2); }

    /* ═══════════════════════════════════
       BADGE PANEL (kanan)
    ═══════════════════════════════════ */
    .badge-list {
      padding: 0.625rem;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      overflow-y: auto;
      flex: 1;
    }

    .badge-item {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.5rem 0.625rem;
      border-radius: var(--radius);
      background: rgba(255,255,255,0.02);
      border: 1px solid var(--border);
      border-left: 3px solid var(--gold);
    }

    .badge-item__icon {
      width: 30px;
      height: 30px;
      border-radius: var(--radius);
      background: rgba(255,215,0,0.1);
      color: var(--gold);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
      flex-shrink: 0;
    }

    .badge-item__name {
      font-size: 0.78rem;
      font-weight: 700;
      color: #fff;
      line-height: 1.3;
    }

    .badge-item__req {
      font-size: 0.62rem;
      color: var(--muted);
      margin-top: 2px;
      font-weight: 500;
    }

    .badge-type-chip {
      display: inline-block;
      padding: 1px 6px;
      border-radius: 3px;
      font-size: 0.56rem;
      font-weight: 700;
      background: rgba(115,103,240,0.15);
      color: #a5a2f7;
      border: 1px solid rgba(115,103,240,0.2);
      text-transform: uppercase;
      letter-spacing: 0.3px;
      margin-left: 4px;
    }

    /* ═══════════════════════════════════
       RECENT ACHIEVEMENTS
    ═══════════════════════════════════ */
    .achievements-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 0.5rem;
      padding: 0.625rem 0.875rem;
    }

    .achievement-card {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.6rem 0.75rem;
      border-radius: var(--radius);
      background: rgba(255,255,255,0.02);
      border: 1px solid var(--border);
    }

    .achievement-avatar {
      width: 30px;
      height: 30px;
      border-radius: var(--radius);
      background: rgba(115,103,240,0.12);
      border: 1px solid rgba(115,103,240,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.65rem;
      font-weight: 800;
      color: #a5a2f7;
      flex-shrink: 0;
    }

    .achievement-badge-icon {
      width: 34px;
      height: 34px;
      border-radius: var(--radius);
      background: rgba(255,215,0,0.1);
      color: var(--gold);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .achievement-info {
      flex: 1;
      min-width: 0;
    }

    .achievement-name {
      font-size: 0.78rem;
      font-weight: 700;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .achievement-detail {
      font-size: 0.62rem;
      color: var(--muted);
      margin-top: 1px;
    }

    .achievement-badge-name {
      font-size: 0.7rem;
      font-weight: 600;
      color: var(--gold);
    }

    .achievement-date {
      font-size: 0.6rem;
      color: var(--muted);
      white-space: nowrap;
      flex-shrink: 0;
    }

    /* ═══════════════════════════════════
       SKELETON LOADER
    ═══════════════════════════════════ */
    .skeleton {
      background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
      background-size: 200% 100%;
      animation: skeleton-shimmer 1.5s infinite;
      border-radius: var(--radius);
    }

    @keyframes skeleton-shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }

    /* ═══════════════════════════════════
       EMPTY STATE
    ═══════════════════════════════════ */
    .empty-state {
      text-align: center;
      padding: 3rem 1rem;
      color: var(--muted);
    }

    .empty-state i {
      font-size: 2.5rem;
      opacity: 0.3;
      margin-bottom: 0.75rem;
      display: block;
    }

    .empty-state p {
      font-size: 0.8rem;
      font-weight: 500;
    }

    /* ═══════════════════════════════════
       FOOTER — compact, fixed shrink
    ═══════════════════════════════════ */
    .footer {
      flex-shrink: 0;
      text-align: center;
      padding: 0.5rem 1rem;
      border-top: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
    }

    .footer__school {
      font-size: 0.72rem;
      font-weight: 700;
      color: rgba(255,255,255,0.4);
    }

    .footer__powered {
      font-size: 0.6rem;
      color: var(--muted);
    }

    #last-updated {
      font-size: 0.6rem;
      color: rgba(255,255,255,0.2);
    }

    /* ═══════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════ */
    @media (max-width: 1024px) {
      .content-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 768px) {
      .header { padding: 0.5rem 0.875rem; }
      .header-title h1 { font-size: 0.82rem; max-width: 120px; }
      #live-clock { font-size: 1.1rem; }
      .main-wrap { padding: 0.5rem; gap: 0.5rem; }
      .stats-row { grid-template-columns: repeat(3,1fr); gap: 0.4rem; }
      .stat-card { padding: 0.5rem 0.625rem; gap: 0.5rem; }
      .stat-card__val { font-size: 1.2rem; }
      .podium-wrap { gap: 0.4rem; padding: 0.625rem; }
      .podium-card { padding: 0.5rem 0.375rem; }
      .podium-name { font-size: 0.62rem; }
      .achievements-grid { grid-template-columns: 1fr 1fr; }
    }

    @media (max-width: 480px) {
      .header-clock { display: none; }
      .stats-row { grid-template-columns: 1fr; }
      .podium-score { font-size: 0.95rem; }
      .podium-avatar { width: 34px; height: 34px; font-size: 0.75rem; }
      .achievements-grid { grid-template-columns: 1fr; }
      .header-title h1 { max-width: 100px; }
    }
  </style>
</head>
<body>

  <!-- ═══════════════ HEADER ═══════════════ -->
  <header class="header">
    <div class="header-brand">
      <div class="header-logo">
        @if(!empty($logoUrl))
          <img src="{{ $logoUrl }}" alt="Logo">
        @else
          <i class="ti tabler-trophy"></i>
        @endif
      </div>
      <div class="header-title">
        <h1>{{ $namaSekolah }}</h1>
        <p>Papan Prestasi Siswa</p>
      </div>
    </div>

    <div class="header-clock">
      <div id="live-clock">00:00:00</div>
      <div id="live-date">—</div>
    </div>

    <div class="header-right">
      <div class="live-badge">
        <span class="live-dot"></span>
        LIVE
      </div>
      <button class="btn-refresh" id="btnRefresh" onclick="refreshAll()" title="Refresh data">
        <i class="ti tabler-refresh"></i>
      </button>
    </div>
  </header>

  <!-- ═══════════════ ANNOUNCE BAR ═══════════════ -->
  <div class="announce-bar">
    <marquee behavior="scroll" direction="left" scrollamount="4" id="announceText">
      <span>🏆</span> Selamat kepada para siswa berprestasi atas dedikasi kehadiran yang luar biasa! <span>⭐</span> Terus semangat dan pertahankan prestasimu. <span>🎖️</span> Kedisiplinan adalah kunci kesuksesan. <span>🏅</span> Data diperbarui otomatis setiap 60 detik.
    </marquee>
  </div>

  <!-- ═══════════════ MAIN ═══════════════ -->
  <div class="main-wrap">

    <!-- STATS ROW -->
    <div class="stats-row">
      <div class="stat-card stat-card--gold">
        <div class="stat-card__icon"><i class="ti tabler-award"></i></div>
        <div>
          <div class="stat-card__val" id="statBadge">—</div>
          <div class="stat-card__label">Badge Tersedia</div>
        </div>
      </div>
      <div class="stat-card stat-card--green">
        <div class="stat-card__icon"><i class="ti tabler-medal"></i></div>
        <div>
          <div class="stat-card__val" id="statSiswa">—</div>
          <div class="stat-card__label">Siswa Berprestasi</div>
        </div>
      </div>
      <div class="stat-card stat-card--purple">
        <div class="stat-card__icon"><i class="ti tabler-school"></i></div>
        <div>
          <div class="stat-card__val" id="statKelas">—</div>
          <div class="stat-card__label">Kelas Berpartisipasi</div>
        </div>
      </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="content-grid">

      <!-- LEADERBOARD PANEL (kiri) -->
      <div class="panel">
        <div class="panel__head">
          <div class="panel__title">
            <span class="panel__dot panel__dot--gold"></span>
            Papan Peringkat
          </div>
          <div class="tab-switch">
            <button class="tab-btn active" id="tabSiswaBtn" onclick="switchTab('siswa')">Siswa</button>
            <button class="tab-btn" id="tabKelasBtn" onclick="switchTab('kelas')">Kelas</button>
          </div>
        </div>

        <!-- TAB SISWA -->
        <div id="panelSiswa" style="display:flex;flex-direction:column;flex:1;min-height:0;overflow:hidden;">
          <!-- PODIUM TOP 3 -->
          <div class="podium-wrap" id="podiumWrap">
            <div style="text-align:center;width:100%;padding:1.5rem 0;color:var(--muted);font-size:0.8rem;">
              <i class="ti tabler-loader-2" style="animation:spin 1s linear infinite;display:block;font-size:1.5rem;margin-bottom:0.5rem;"></i>
              Memuat data...
            </div>
          </div>
          <!-- TABLE RANK 4+ -->
          <div id="siswaTableWrap" style="display:none;overflow-y:auto;flex:1;">
            <table class="p-table">
              <thead>
                <tr>
                  <th class="text-center" style="width:50px;">Rank</th>
                  <th>Nama Siswa</th>
                  <th>Kelas</th>
                  <th class="text-center">Hadir</th>
                  <th class="text-center">Skor</th>
                  <th class="text-center">Badge</th>
                </tr>
              </thead>
              <tbody id="siswaTableBody"></tbody>
            </table>
          </div>
        </div>

        <!-- TAB KELAS (hidden default) -->
        <div id="panelKelas" style="display:none;overflow-y:auto;flex:1;">
          <table class="p-table">
            <thead>
              <tr>
                <th class="text-center" style="width:50px;">Rank</th>
                <th>Kelas</th>
                <th class="text-center">Absensi</th>
                <th>Kehadiran</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody id="kelasTableBody">
              <tr>
                <td colspan="5">
                  <div class="empty-state">
                    <i class="ti tabler-loader-2" style="animation:spin 1s linear infinite;"></i>
                    <p>Memuat data...</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- BADGE PANEL (kanan) -->
      <div class="panel">
        <div class="panel__head">
          <div class="panel__title">
            <span class="panel__dot panel__dot--gold"></span>
            Badge Achievements
          </div>
        </div>
        <div class="badge-list" id="badgeList">
          <div style="text-align:center;padding:1.5rem;color:var(--muted);font-size:0.8rem;">
            <i class="ti tabler-loader-2" style="animation:spin 1s linear infinite;display:block;font-size:1.5rem;margin-bottom:0.5rem;"></i>
            Memuat badge...
          </div>
        </div>
      </div>
    </div>

    <!-- RECENT ACHIEVEMENTS -->
    <div class="panel" style="flex-shrink:0;max-height:220px;">
      <div class="panel__head">
        <div class="panel__title">
          <span class="panel__dot panel__dot--green"></span>
          Perolehan Badge Terbaru
        </div>
        <span style="font-size:0.65rem;color:var(--muted);" id="achievementCount">—</span>
      </div>
      <div id="achievementsWrap" style="overflow-y:auto;overflow-x:hidden;flex:1;">
        <div class="empty-state">
          <i class="ti tabler-loader-2" style="animation:spin 1s linear infinite;"></i>
          <p>Memuat data...</p>
        </div>
      </div>
    </div>

  </div><!-- /main-wrap -->

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer__school">{{ $namaSekolah }}</div>
    <div class="footer__powered">Powered by E-Absensi Digital &bull; Sistem Presensi Cerdas</div>
    <div id="last-updated">Terakhir diperbarui: —</div>
  </footer>

  <script>
  // ─── CLOCK ────────────────────────────────────────────────────────────────
  const DAYS = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const MONTHS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];

  function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('live-clock').textContent = `${h}:${m}:${s}`;
    document.getElementById('live-date').textContent =
      `${DAYS[now.getDay()]}, ${now.getDate()} ${MONTHS[now.getMonth()]} ${now.getFullYear()}`;
  }
  updateClock();
  setInterval(updateClock, 1000);

  // ─── UTILS ────────────────────────────────────────────────────────────────
  function getInitials(name) {
    if (!name) return '?';
    return name.trim().split(/\s+/).map(w => w[0]).join('').substring(0, 2).toUpperCase();
  }

  function formatDate(dateStr) {
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
  }

  function setLastUpdated() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2,'0');
    const m = String(now.getMinutes()).padStart(2,'0');
    document.getElementById('last-updated').textContent = `Terakhir diperbarui: ${h}:${m}`;
  }

  // ─── TABS ─────────────────────────────────────────────────────────────────
  function switchTab(tab) {
    const pSiswa = document.getElementById('panelSiswa');
    const pKelas = document.getElementById('panelKelas');
    pSiswa.style.display = tab === 'siswa' ? 'flex' : 'none';
    pKelas.style.display = tab === 'kelas' ? 'block' : 'none';
    document.getElementById('tabSiswaBtn').classList.toggle('active', tab === 'siswa');
    document.getElementById('tabKelasBtn').classList.toggle('active', tab === 'kelas');
  }

  // ─── RENDER HELPERS ───────────────────────────────────────────────────────
  function renderBadgeIcons(badges, max = 3) {
    if (!badges || !badges.length) return '<span style="color:rgba(255,255,255,0.15);font-size:0.6rem;">—</span>';
    return badges.slice(0, max).map(b =>
      `<span class="mini-badge"><i class="ti ${b.badge?.icon || 'tabler-award'}"></i></span>`
    ).join('');
  }

  function renderRankBadge(rank) {
    return `<span class="rank-badge">${rank}</span>`;
  }

  // ─── LOAD STUDENT LEADERBOARD ─────────────────────────────────────────────
  let studentData = [];

  async function loadStudentLeaderboard() {
    try {
      const res = await fetch('/api/v1/innovation/leaderboard/students?limit=20');
      const json = await res.json();
      studentData = json.data || [];
      document.getElementById('statSiswa').textContent = studentData.length;
      renderStudentLeaderboard(studentData);
    } catch(e) {
      document.getElementById('podiumWrap').innerHTML = `
        <div class="empty-state" style="width:100%">
          <i class="ti tabler-cloud-off"></i><p>Gagal memuat data siswa</p>
        </div>`;
    }
  }

  function renderStudentLeaderboard(data) {
    const podium = document.getElementById('podiumWrap');
    const tableWrap = document.getElementById('siswaTableWrap');
    const tbody = document.getElementById('siswaTableBody');

    if (!data.length) {
      podium.innerHTML = `<div class="empty-state" style="width:100%"><i class="ti tabler-trophy-off"></i><p>Belum ada data peringkat siswa. Hitung ulang skor terlebih dahulu.</p></div>`;
      tableWrap.style.display = 'none';
      return;
    }

    // TOP 3 PODIUM
    const top3 = data.slice(0, 3);
    const configs = [
      { cls: 'silver', rank: 2, idx: 1 },
      { cls: 'gold',   rank: 1, idx: 0 },
      { cls: 'bronze', rank: 3, idx: 2 },
    ];

    let podiumHtml = '';
    configs.forEach(cfg => {
      const item = top3[cfg.idx];
      if (!item) { podiumHtml += `<div style="flex:1;max-width:180px;"></div>`; return; }
      const nama = item.siswa?.nama_lengkap || 'Siswa';
      const kelas = item.siswa?.kelas?.nama || '—';
      const skor = item.score ?? 0;
      const badges = item.siswa?.student_badges || [];

      podiumHtml += `
        <div class="podium-card podium-card--${cfg.cls}">
          ${cfg.cls === 'gold' ? `<div class="podium-crown"><i class="ti tabler-crown"></i></div>` : ''}
          <div class="podium-avatar podium-avatar--${cfg.cls}" style="position:relative;">
            ${getInitials(nama)}
            <span class="podium-rank podium-rank--${cfg.cls}">${cfg.rank}</span>
          </div>
          <div class="podium-name">${nama}</div>
          <div class="podium-kelas">${kelas}</div>
          <div class="podium-score podium-score--${cfg.cls}">${skor}</div>
          <div class="podium-score-label">Skor</div>
          <div class="podium-badges">${renderBadgeIcons(badges)}</div>
        </div>`;
    });
    podium.innerHTML = podiumHtml;

    // TABLE RANK 4+
    const rest = data.slice(3);
    if (rest.length) {
      tbody.innerHTML = rest.map((item, i) => {
        const rank = i + 4;
        const nama = item.siswa?.nama_lengkap || '—';
        const nis  = item.siswa?.nis || '—';
        const kelas = item.siswa?.kelas?.nama || '—';
        const hadir = item.total_present ?? 0;
        const total = item.total_attendance ?? 0;
        const skor = item.score ?? 0;
        const scoreColor = skor > 80 ? 'var(--gold)' : skor > 40 ? 'var(--success)' : 'var(--muted)';
        const badges = item.siswa?.student_badges || [];
        return `<tr>
          <td style="text-align:center;">${renderRankBadge(rank)}</td>
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <div class="student-avatar">${getInitials(nama)}</div>
              <div>
                <div style="font-weight:700;color:#fff;font-size:0.78rem;">${nama}</div>
                <div style="font-size:0.62rem;color:var(--muted);">NIS: ${nis}</div>
              </div>
            </div>
          </td>
          <td><span class="kelas-chip">${kelas}</span></td>
          <td style="text-align:center;font-weight:600;color:var(--success);font-size:0.78rem;">${hadir}<span style="color:rgba(255,255,255,0.2);font-weight:400;">/${total}</span></td>
          <td style="text-align:center;"><span style="font-weight:800;color:${scoreColor};font-size:0.9rem;">${skor}</span></td>
          <td style="text-align:center;"><div style="display:flex;gap:2px;justify-content:center;">${renderBadgeIcons(badges)}</div></td>
        </tr>`;
      }).join('');
      tableWrap.style.display = '';
    } else {
      tableWrap.style.display = 'none';
    }
  }

  // ─── LOAD KELAS LEADERBOARD ───────────────────────────────────────────────
  async function loadKelasLeaderboard() {
    try {
      const res = await fetch('/api/v1/innovation/leaderboard');
      const json = await res.json();
      const data = json.data || [];
      document.getElementById('statKelas').textContent = data.length;
      renderKelasLeaderboard(data);
    } catch(e) {
      document.getElementById('kelasTableBody').innerHTML = `<tr><td colspan="5"><div class="empty-state"><i class="ti tabler-cloud-off"></i><p>Gagal memuat data kelas</p></div></td></tr>`;
    }
  }

  function renderKelasLeaderboard(data) {
    const tbody = document.getElementById('kelasTableBody');
    if (!data.length) {
      tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><i class="ti tabler-trophy-off"></i><p>Belum ada data peringkat kelas.</p></div></td></tr>`;
      return;
    }
    tbody.innerHTML = data.map((item, i) => {
      const rank = i + 1;
      const pct = parseFloat(item.percentage || 0);
      const barColor = pct >= 85 ? 'var(--success)' : pct >= 70 ? 'var(--warning)' : 'var(--danger)';
      let statusCls = 'good', statusTxt = 'Sangat Baik';
      if (pct < 70) { statusCls = 'bad'; statusTxt = 'Butuh Perhatian'; }
      else if (pct < 85) { statusCls = 'medium'; statusTxt = 'Cukup Baik'; }
      const rankIcon = rank === 1 ? `<span style="font-size:1.1rem;">🏆</span>` : rank === 2 ? `<span style="font-size:1rem;">🥈</span>` : rank === 3 ? `<span style="font-size:1rem;">🥉</span>` : renderRankBadge(rank);
      return `<tr>
        <td style="text-align:center;">${rankIcon}</td>
        <td>
          <div style="font-weight:700;color:#fff;">${item.kelas?.nama || '—'}</div>
          <div style="font-size:0.62rem;color:var(--muted);">${item.kelas?.jurusan || 'Semua Jurusan'}</div>
        </td>
        <td style="text-align:center;font-size:0.78rem;">${item.total_present}/<span style="color:var(--muted);">${item.total_attendance}</span></td>
        <td>
          <div class="progress-wrap">
            <div class="progress-bar-outer">
              <div class="progress-bar-inner" style="width:${pct}%;background:${barColor};"></div>
            </div>
            <span style="font-size:0.72rem;font-weight:700;color:#fff;min-width:38px;">${pct.toFixed(1)}%</span>
          </div>
        </td>
        <td style="text-align:center;"><span class="status-chip status-chip--${statusCls}">${statusTxt}</span></td>
      </tr>`;
    }).join('');
  }

  // ─── LOAD BADGES ──────────────────────────────────────────────────────────
  async function loadBadges() {
    try {
      const res = await fetch('/api/v1/innovation/badges');
      const json = await res.json();
      const data = json.data || [];
      document.getElementById('statBadge').textContent = data.length;
      const container = document.getElementById('badgeList');
      if (!data.length) {
        container.innerHTML = `<div class="empty-state"><i class="ti tabler-award-off"></i><p>Belum ada badge.</p></div>`;
        return;
      }
      container.innerHTML = data.map(b => `
        <div class="badge-item">
          <div class="badge-item__icon"><i class="ti ${b.icon || 'tabler-award'}"></i></div>
          <div>
            <div class="badge-item__name">${b.name}<span class="badge-type-chip">${b.badge_type === 'individual' ? 'Individu' : 'Kelas'}</span></div>
            <div class="badge-item__req">${b.requirement_days} hari ${b.requirement_type === 'consecutive' ? 'beruntun' : 'akumulasi'}</div>
          </div>
        </div>`).join('');
    } catch(e) {}
  }

  // ─── LOAD RECENT ACHIEVEMENTS ─────────────────────────────────────────────
  async function loadRecentAchievements() {
    try {
      const res = await fetch('/api/v1/innovation/badges/history');
      const json = await res.json();
      const data = json.data || [];
      document.getElementById('achievementCount').textContent = data.length ? `${data.length} perolehan` : '—';
      const wrap = document.getElementById('achievementsWrap');
      if (!data.length) {
        wrap.innerHTML = `<div class="empty-state"><i class="ti tabler-medal-off"></i><p>Belum ada perolehan badge.</p></div>`;
        return;
      }
      wrap.innerHTML = `<div class="achievements-grid">` + data.map(item => `
        <div class="achievement-card">
          <div class="achievement-avatar">${getInitials(item.siswa?.nama_lengkap)}</div>
          <div class="achievement-info">
            <div class="achievement-name">${item.siswa?.nama_lengkap || '—'}</div>
            <div class="achievement-detail">${item.siswa?.kelas?.nama || '—'}</div>
            <div class="achievement-badge-name"><i class="ti ${item.badge?.icon || 'tabler-award'}" style="margin-right:3px;font-size:0.7rem;"></i>${item.badge?.name || '—'}</div>
          </div>
          <div class="achievement-date">${formatDate(item.earned_at)}</div>
        </div>`).join('') + `</div>`;
    } catch(e) {}
  }

  // ─── REFRESH ALL ──────────────────────────────────────────────────────────
  async function refreshAll() {
    const btn = document.getElementById('btnRefresh');
    btn.classList.add('spinning');
    btn.disabled = true;
    try {
      await Promise.all([
        loadStudentLeaderboard(),
        loadKelasLeaderboard(),
        loadBadges(),
        loadRecentAchievements(),
      ]);
      setLastUpdated();
    } finally {
      btn.classList.remove('spinning');
      btn.disabled = false;
    }
  }

  // ─── INIT ─────────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', async () => {
    await refreshAll();
    setInterval(refreshAll, 60000);
  });
  </script>

</body>
</html>
