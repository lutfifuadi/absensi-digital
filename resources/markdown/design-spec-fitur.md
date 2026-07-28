# 🎨 Design Specification — Halaman Fitur (Features Page)

> **Halaman**: `resources/views/content/pages/pages-fitur.blade.php`
> **Tujuan**: Halaman marketing yang menampilkan seluruh 24 kategori fitur Sistem Presensi Sekolah secara menarik, informatif, dan profesional
> **Design System**: Mengikuti `pages-home.blade.php` — Dark Theme + Glass Morphism + AOS

---

## A. LAYOUT STRUCTURE

### Section Flow (Atas → Bawah)

```
┌─────────────────────────────────────────────────────────┐
│  1. HERO SECTION                                        │
│     ├── Hero Badge (animated dot)                       │
│     ├── Section Eyeline (gold, uppercase)               │
│     ├── Main Title (Trajan Pro)                         │
│     ├── Gradient Subtitle                               │
│     ├── Description paragraph                           │
│     └── CTA Buttons (Mulai Sekarang + Lihat Demo)      │
│  ─────────────────────────────────────────────────────  │
│  2. STATS BAR (4 metrik)                                │
│     ├── 24+ Fitur                                       │
│     ├── 12+ Role                                        │
│     ├── 6 Portal                                        │
│     └── 99.9% Uptime                                   │
│  ─────────────────────────────────────────────────────  │
│  3. CATEGORY FILTER / NAVIGATION PILLS                  │
│     ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐      │
│     │ Semua│ │Keamanan│ │Absen│ │AI &  │ │ ...  │      │
│     └──────┘ └──────┘ └──────┘ └──────┘ └──────┘      │
│  ─────────────────────────────────────────────────────  │
│  4. FEATURE GRID (24 kategori)                          │
│     ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐   │
│     │ Card 1  │ │ Card 2  │ │ Card 3  │ │ Card 4  │   │
│     └─────────┘ └─────────┘ └─────────┘ └─────────┘   │
│     ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐   │
│     │ Card 5  │ │ Card 6  │ │ Card 7  │ │ Card 8  │   │
│     └─────────┘ └─────────┘ └─────────┘ └─────────┘   │
│     ... (sampai 24 card) ...                            │
│  ─────────────────────────────────────────────────────  │
│  5. SPOTLIGHT SECTION (3 feature unggulan)              │
│     ├── Feature A (icon + mockup)                       │
│     ├── Feature B (mockup + icon) [reverse layout]      │
│     └── Feature C (icon + mockup)                       │
│  ─────────────────────────────────────────────────────  │
│  6. CTA SECTION                                         │
│     ├── Glow decorations                                │
│     ├── Title + gradient subtitle                       │
│     ├── Description                                     │
│     └── CTA Buttons                                     │
│  ─────────────────────────────────────────────────────  │
│  7. FOOTER (reuse dari layoutMaster)                    │
└─────────────────────────────────────────────────────────┘
```

### Container & Spacing
- **Container max-width**: 1140px (Bootstrap `container`)
- **Section padding desktop**: 84px 0 (atas-bawah)
- **Section padding tablet**: 64px 0
- **Section padding mobile**: 44px 0
- **Grid gap**: 16px (`g-3`) antar card

---

## B. CARD DESIGN — 24 Kategori Fitur

### Card Layout (per kartu)

```
┌──────────────────────────────────┐
│  ┌────────┐                     │
│  │  ICON  │  ← 42×42px, rounded│
│  └────────┘     10px            │
│                                  │
│  Nama Kategori Fitur             │  ← 13px, bold, white
│                                  │
│  Deskripsi singkat fitur dalam   │  ← 11.5px, muted2, 1.7 line-height
│  1-2 baris kalimat              │
│                                  │
│  ┌────────────────────────────┐  │
│  │ 🔢 12+ sub-fitur           │  │  ← feature count pill
│  └────────────────────────────┘  │
│                                  │
│  Explore →                      │  ← link teks, subtle
│  ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓  │  ← bottom gradient line (hidden default)
└──────────────────────────────────┘
```

### Card Base Style
```css
/* REUSE dari pages-home: .feature-card */
background: var(--surface);          /* #0d1120 */
border: 0.5px solid var(--border);   /* rgba(255,255,255,0.07) */
border-radius: var(--radius);        /* 14px */
padding: 22px;
height: 100%;                        /* stretch ke row tertinggi */
position: relative;
overflow: hidden;
transition: border-color .25s, transform .25s, box-shadow .3s;
```

### Hover Effect
```css
.feature-card:hover {
  border-color: rgba(108, 99, 255, 0.35);   /* primary glow */
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3),
              0 0 60px rgba(108, 99, 255, 0.06);
}

/* Bottom gradient line muncul */
.feature-card:hover .feature-card-line {
  opacity: 1;
}
/* Line: height 2px, linear-gradient(90deg, var(--primary), var(--info)) */
```

### Color Coding per Kategori

Setiap kategori punya **warna aksen** untuk icon background. Warna dihitung berdasarkan hue rotation dari warna utama agar terlihat bervariasi namun harmonis:

| No | Kategori | Icon BG | Icon Color | Accent Hue |
|----|----------|---------|------------|------------|
| 1 | 🔐 Keamanan & Akses | `rgba(234, 84, 85, 0.10)` | `#ea5455` (danger) | Merah |
| 2 | 📊 Dashboard & Analitik | `rgba(108, 99, 255, 0.10)` | `#a89ff7` (primary) | Ungu |
| 3 | 👥 Data Master | `rgba(0, 207, 232, 0.10)` | `#00cfe8` (info) | Cyan |
| 4 | ✅ Absensi Siswa | `rgba(40, 199, 111, 0.10)` | `#28c76f` (success) | Hijau |
| 5 | 👨‍🏫 Absensi Guru | `rgba(255, 159, 67, 0.10)` | `#ff9f43` (warning) | Oranye |
| 6 | 👥 Absensi Staff TU | `rgba(96, 165, 250, 0.10)` | `#60a5fa` | Biru |
| 7 | 📝 Izin & Sakit | `rgba(168, 159, 247, 0.10)` | `#a89ff7` | Lavender |
| 8 | 🎯 Kegiatan Khusus | `rgba(52, 211, 153, 0.10)` | `#34d399` | Emerald |
| 9 | ⚽ Ekstrakurikuler | `rgba(251, 191, 36, 0.10)` | `#fbbf24` | Kuning |
| 10 | 🎓 Pelepasan Kelas XII | `rgba(226, 185, 111, 0.10)` | `#e2b96f` (gold) | Gold |
| 11 | 📱 Portal Khusus | `rgba(139, 92, 246, 0.10)` | `#8b5cf6` | Violet |
| 12 | 👁️ Monitoring Real-Time | `rgba(34, 211, 238, 0.10)` | `#22d3ee` (info) | Cyan |
| 13 | 🎮 Gamifikasi | `rgba(236, 72, 153, 0.10)` | `#ec4899` | Pink |
| 14 | 📷 QR Code & Scan | `rgba(40, 199, 111, 0.10)` | `#28c76f` | Hijau |
| 15 | 🆔 Kartu Identitas | `rgba(96, 165, 250, 0.10)` | `#60a5fa` | Biru |
| 16 | ⚠️ Pelanggaran Siswa | `rgba(239, 68, 68, 0.10)` | `#ef4444` | Merah |
| 17 | 💬 Pengaduan Data | `rgba(251, 146, 60, 0.10)` | `#fb923c` | Oranye |
| 18 | 📡 Integrasi Eksternal | `rgba(99, 102, 241, 0.10)` | `#6366f1` | Indigo |
| 19 | 🤖 AI & Inovasi | `rgba(168, 85, 247, 0.10)` | `#a855f7` | Purple |
| 20 | ⚙️ Pengaturan Sistem | `rgba(148, 163, 184, 0.10)` | `#94a3b8` | Slate |
| 21 | 🛠️ Developer Tools | `rgba(34, 211, 238, 0.10)` | `#22d3ee` | Cyan |
| 22 | 📖 Panduan & Publik | `rgba(253, 224, 71, 0.10)` | `#fde047` | Kuning |
| 23 | 🔌 API REST | `rgba(52, 211, 153, 0.10)` | `#34d399` | Emerald |
| 24 | 🔒 Keamanan Lanjutan | `rgba(248, 113, 113, 0.10)` | `#f87171` | Merah |

### Sub-Feature Count Badge
```css
.feature-count {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.3px;
  padding: 3px 10px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.04);
  border: 0.5px solid var(--border);
  color: var(--muted2);
  margin-top: 12px;
}
```

### "Explore →" Link
```css
.feature-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 14px;
  font-size: 11px;
  font-weight: 700;
  color: var(--muted);
  text-decoration: none;
  transition: color .2s, gap .2s;
}

.feature-link:hover {
  color: var(--primary);
  gap: 8px;
}
```

---

## C. VISUAL HIERARCHY

### Typography Scale

| Element | Font | Size | Weight | Color | Letter Spacing |
|---------|------|------|--------|-------|----------------|
| Hero Eyeline | Trajan Pro | 11px | 400 | `var(--gold)` `#e2b96f` | 4px, uppercase |
| Hero Title | Trajan Pro | `clamp(1.9rem, 3vw, 2.8rem)` | 400 | `#fff` | 0.01em |
| Hero Subtitle | Product Sans | `clamp(1.4rem, 2vw, 1.9rem)` | 700 | gradient `#a89ff7 → #22d3ee` | — |
| Section Label | Product Sans | 9.5px | 700 | `var(--primary)` | 2px, uppercase |
| Section Title | Trajan Pro | `clamp(1.3rem, 2vw, 1.9rem)` | 400 | `#fff` | 0.03em |
| Section Desc | Product Sans | 13px | 500 | `var(--muted2)` | — |
| Card Title | Product Sans | 13px | 700 | `#fff` | — |
| Card Desc | Product Sans | 11.5px | 500 | `var(--muted2)` | — |
| Stat Value | Trajan Pro | `clamp(1.6rem, 2.5vw, 2.4rem)` | 400 | `#fff` | 0.02em |
| Stat Label | Product Sans | 9.5px | 700 | `var(--muted)` | 2px, uppercase |
| Hero Subtitle | Product Sans | 13.5px | 500 | `var(--muted2)` | — |
| Button | Product Sans | 13px | 700 | `#fff` | — |

### Spacing System (kelipatan 4px)
- **Section → Title**: 10px margin-bottom
- **Title → Description**: 10px margin-bottom
- **Description → Grid**: 32px (mb-4)
- **Grid gap**: 16px (g-3)
- **Card padding**: 22px
- **Card → Icon**: 14px margin-bottom
- **Icon → Title**: 0 (relative positioned)
- **Title → Desc**: 6px margin-bottom
- **Desc → Badge**: 12px margin-top

---

## D. ICON SYSTEM

### Library: Tabler Icons
Class format: `<i class="ti tabler-[nama-icon]"></i>`

### Rekomendasi Icon per Kategori

| No | Kategori | Icon Tabler | Code |
|----|----------|-------------|------|
| 1 | 🔐 Keamanan & Akses | shield-lock | `tabler-shield-lock` |
| 2 | 📊 Dashboard & Analitik | layout-dashboard | `tabler-layout-dashboard` |
| 3 | 👥 Data Master | database | `tabler-database` |
| 4 | ✅ Absensi Siswa | checklist | `tabler-checklist` |
| 5 | 👨‍🏫 Absensi Guru | school | `tabler-school` |
| 6 | 👥 Absensi Staff TU | users | `tabler-users` |
| 7 | 📝 Izin & Sakit | file-text | `tabler-file-text` |
| 8 | 🎯 Kegiatan Khusus | target | `tabler-target` |
| 9 | ⚽ Ekstrakurikuler | trophy | `tabler-trophy` |
| 10 | 🎓 Pelepasan Kelas XII | award | `tabler-award` |
| 11 | 📱 Portal Khusus | device-mobile | `tabler-device-mobile` |
| 12 | 👁️ Monitoring Real-Time | eye | `tabler-eye` |
| 13 | 🎮 Gamifikasi | gamepad-2 | `tabler-gamepad-2` |
| 14 | 📷 QR Code & Scan | qrcode | `tabler-qrcode` |
| 15 | 🆔 Kartu Identitas | id | `tabler-id` |
| 16 | ⚠️ Pelanggaran Siswa | alert-triangle | `tabler-alert-triangle` |
| 17 | 💬 Pengaduan Data | message-report | `tabler-message-report` |
| 18 | 📡 Integrasi Eksternal | plug-connected | `tabler-plug-connected` |
| 19 | 🤖 AI & Inovasi | brain | `tabler-brain` |
| 20 | ⚙️ Pengaturan Sistem | settings | `tabler-settings` |
| 21 | 🛠️ Developer Tools | terminal-2 | `tabler-terminal-2` |
| 22 | 📖 Panduan & Publik | book-2 | `tabler-book-2` |
| 23 | 🔌 API REST | api | `tabler-api` |
| 24 | 🔒 Keamanan Lanjutan | shield-checkered | `tabler-shield-checkered` |

---

## E. RESPONSIVE DESIGN

### Breakpoint Behavior

| Breakpoint | Grid Columns | Card Layout | Hero Layout | Detail |
|------------|-------------|-------------|-------------|--------|
| **Desktop (>1199px)** | **4 kolom** | `col-lg-3` | 2 kolom (copy + mockup) | Full visual |
| **Tablet Landscape (992–1199px)** | **3 kolom** | `col-md-4` | 2 kolom | Full visual |
| **Tablet Portrait (768–991px)** | **2 kolom** | `col-sm-6` | 1 kolom (mockup hidden) | Mockup → stacked |
| **Large Phone (576–767px)** | **2 kolom** | `col-6` | 1 kolom | Compact card |
| **Small Phone (<576px)** | **1 kolom** | `col-12` | 1 kolom | Full-width card |

### Responsive Specifics

#### Desktop (>1199px)
```
┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐
│      │ │      │ │      │ │      │  ← 4 kolom
│ Card │ │ Card │ │ Card │ │ Card │
│  1   │ │  2   │ │  3   │ │  4   │
└──────┘ └──────┘ └──────┘ └──────┘
```

#### Tablet (768–991px)
```
┌─────────┐ ┌─────────┐
│         │ │         │  ← 2 kolom
│  Card 1 │ │  Card 2 │
└─────────┘ └─────────┘
┌─────────┐ ┌─────────┐
│         │ │         │
│  Card 3 │ │  Card 4 │
└─────────┘ └─────────┘
```

#### Mobile (<576px)
```
┌───────────────┐
│               │  ← 1 kolom full-width
│    Card 1     │
└───────────────┘
┌───────────────┐
│    Card 2     │
└───────────────┘
```

### Category Pills — Scroll Horizontal di Mobile
```css
/* Desktop: wrap normal */
/* Mobile: horizontal scroll, hide scrollbar */
.category-pills {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none; /* Firefox */
  padding-bottom: 4px;
}
.category-pills::-webkit-scrollbar { display: none; }

.category-pill {
  flex-shrink: 0;
  white-space: nowrap;
}
```

---

## F. INTERAKSI & ANIMASI

### 1. Scroll Animations (AOS Library)

| Element | Animation | Delay | Duration |
|---------|-----------|-------|----------|
| Hero badge | `fade-up` | 0ms | 700ms |
| Hero title | `fade-up` | 100ms | 700ms |
| Hero subtitle | `fade-up` | 200ms | 700ms |
| Hero description | `fade-up` | 300ms | 700ms |
| Hero CTA buttons | `fade-up` | 400ms | 700ms |
| Stats bar items | `fade-up` | 0/80/160/240ms staggered | 700ms |
| Section label | `fade-up` | 0ms | 700ms |
| Section title | `fade-up` | 60ms | 700ms |
| Category pills | `fade-up` | 120ms | 700ms |
| Feature cards | `fade-up` | 0/60/120/180ms staggered per row | 700ms |
| Spotlight sections | `fade-up` | 0ms | 700ms |
| CTA box | `fade-up` | 0ms | 700ms |

```javascript
// AOS config (reuse dari pages-home)
AOS.init({
  duration: 700,
  once: true,
  easing: 'ease-out-quart',
  offset: 40
});
```

### 2. Hover States

#### Feature Card Hover
- `border-color`: `rgba(108, 99, 255, 0.35)` (primary glow)
- `transform`: `translateY(-5px)`
- `box-shadow`: `0 20px 40px rgba(0,0,0,0.3), 0 0 60px rgba(108,99,255,0.06)`
- Bottom gradient line fade-in: `opacity: 0 → 1`

#### Icon Hover
- `transform`: `scale(1.1)` dengan `transition: transform .2s ease`
- `background`: opacity meningkat dari `0.10 → 0.18`

#### Category Pill Hover
- `background`: `rgba(108, 99, 255, 0.15)` → active: `rgba(108, 99, 255, 0.25)`
- `border-color`: `rgba(108, 99, 255, 0.40)`
- `color`: `#a89ff7`

#### Button Hover
```css
/* Primary button */
.btn-primary-live:hover {
  background: #5b53e8;
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(108, 99, 255, 0.45);
}

/* Ghost button */
.btn-ghost-live:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.25);
  transform: translateY(-2px);
}
```

### 3. Transition Effects

```css
/* Base transition untuk semua card */
.feature-card {
  transition: border-color .25s ease,
              transform .25s ease,
              box-shadow .3s ease;
}

/* Icon scale */
.feature-icon {
  transition: transform .2s ease, background .2s ease;
}

/* Bottom line fade */
.feature-card-line {
  transition: opacity .25s ease;
}

/* Explore link gap animation */
.feature-link {
  transition: color .2s ease, gap .2s ease;
}
```

### 4. Decorative Glow Animations

```css
/* Hero section radial gradient (static, sudah ada) */
.section-hero {
  background:
    radial-gradient(ellipse at 6% 50%, rgba(108, 99, 255, .10) 0%, transparent 52%),
    radial-gradient(ellipse at 96% 20%, rgba(34, 211, 238, .05) 0%, transparent 45%),
    var(--bg);
}

/* CTA section glow (reuse dari pages-home) */
.cta-glow {
  position: absolute;
  width: 340px;
  height: 340px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(108, 99, 255, 0.12), transparent 70%);
  top: -160px;
  right: -80px;
  pointer-events: none;
}
```

---

## G. WIREFRAME SKETCH

### 1. Hero Section

```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║   ┌─────────────────────────────┐  ┌───────────────────────┐ ║
║   │                             │  │  ┌─────────────────┐  │ ║
║   │  ● Smart Attendance v3.0    │  │  │ ○ ○ ○  Browser  │  │ ║
║   │                             │  │  ├─────────────────┤  │ ║
║   │  SISTEM ABSENSI DIGITAL     │  │  │                 │  │ ║
║   │                             │  │  │   Dashboard     │  │ ║
║   │  Hadir. Tercatat.           │  │  │   Mockup /      │  │ ║
║   │  Terpantau.                 │  │  │   Feature       │  │ ║
║   │                             │  │  │   Showcase      │  │ ║
║   │  Real-Time. Tanpa Kertas.   │  │  │                 │  │ ║
║   │                             │  │  └─────────────────┘  │ ║
║   │  24+ fitur terintegrasi     │  │                       │ ║
║   │  untuk digitalisasi         │  │   float animation     │ ║
║   │  kehadiran sekolah Anda.    │  │   (8s ease-in-out)    │ ║
║   │                             │  │                       │ ║
║   │  [ Mulai Sekarang → ]       │  └───────────────────────┘ ║
║   │  [ ▶ Live Board Demo ]      │                             ║
║   │                             │                             ║
║   │  ─── Ideal untuk ──────     │                             ║
║   │  MA · SMP · SMK · SD        │                             ║
║   └─────────────────────────────┘                             ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

  Layout: row > col-lg-6 (copy) + col-lg-6 (mockup)
  Mobile: col-lg-6 (mockup) → hidden
```

### 2. Feature Grid

```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║              ┌──────────────────────────┐                     ║
║              │  FITUR UNGGULAN          │  ← section-label   ║
║              └──────────────────────────┘                     ║
║                                                               ║
║     24+ Fitur Lengkap untuk Sekolah Digital                   ║
║     Satu platform terintegrasi untuk semua kebutuhan          ║
║     manajemen kehadiran dan kedisiplinan sekolah Anda.        ║
║                                                               ║
║  ┌──────────────────────────────────────────────────────────┐ ║
║  │ [Semua] [Keamanan] [Absensi] [Analitik] [AI] [Portal]  │ ║  ← horizontal scroll pills
║  └──────────────────────────────────────────────────────────┘ ║
║                                                               ║
║  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐ ║
║  │  🔒        │ │  📊        │ │  👥        │ │  ✅        │ ║
║  │            │ │            │ │            │ │            │ ║
║  │ Keamanan & │ │ Dashboard  │ │ Data       │ │ Absensi    │ ║
║  │ Akses      │ │ & Analitik │ │ Master     │ │ Siswa      │ ║
║  │            │ │            │ │            │ │            │ ║
║  │ RBAC 12+   │ │ Live       │ │ Guru,      │ │ QR Code,   │ ║
║  │ role,      │ │ monitor,   │ │ Siswa,     │ │ bulk scan, │ ║
║  │ impersona- │ │ gamifikasi │ │ Kelas      │ │ auto-alpha │ ║
║  │ tion       │ │            │ │            │ │            │ ║
║  │            │ │            │ │            │ │            │ ║
║  │ 🔢 5+ sub  │ │ 🔢 8+ sub  │ │ 🔢 6+ sub  │ │ 🔢 7+ sub  │ ║
║  │            │ │            │ │            │ │            │ ║
║  │ Explore →  │ │ Explore →  │ │ Explore →  │ │ Explore →  │ ║
║  │▓▓▓▓▓▓▓▓▓▓▓▓│ │▓▓▓▓▓▓▓▓▓▓▓▓│ │▓▓▓▓▓▓▓▓▓▓▓▓│ │▓▓▓▓▓▓▓▓▓▓▓▓│ ║
║  └────────────┘ └────────────┘ └────────────┘ └────────────┘ ║
║                                                               ║
║  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐ ║
║  │  👨‍🏫        │ │  👥        │ │  📝        │ │  🎯        │ ║
║  │ Absensi    │ │ Absensi    │ │ Izin &     │ │ Kegiatan   │ ║
║  │ Guru       │ │ Staff TU   │ │ Sakit      │ │ Khusus     │ ║
║  │ ...        │ │ ...        │ │ ...        │ │ ...        │ ║
║  └────────────┘ └────────────┘ └────────────┘ └────────────┘ ║
║                                                               ║
║  ... (baris 3-6 dengan 4 kolom per baris, total 24 card)     ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

  Desktop: 4 kolom (col-lg-3)
  Tablet: 3 kolom (col-md-4)
  Mobile: 2 kolom (col-6) → Small phone: 1 kolom (col-12)
```

### 3. Single Feature Detail (Spotlight Section)

```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║  ┌─────────────────────┐  ┌─────────────────────────────────┐║
║  │                     │  │                                 │║
║  │   ┌─────────────┐   │  │   ┌─────────────────────────┐  │║
║  │   │   ICON XL   │   │  │   │                         │  │║
║  │   │   64×64px   │   │  │   │     Feature Mockup /    │  │║
║  │   └─────────────┘   │  │   │     Screenshot Area     │  │║
║  │                     │  │   │                         │  │║
║  │   Nama Fitur        │  │   │     (glass card with    │  │║
║  │   Spotlight         │  │   │      mockup content)    │  │║
║  │                     │  │   │                         │  │║
║  │   Deskripsi panjang │  │   └─────────────────────────┘  │║
║  │   tentang fitur ini │  │                                 │║
║  │   yang memberikan   │  │                                 │║
║  │   value proposition │  │                                 │║
║  │   yang kuat.        │  │                                 │║
║  │                     │  │                                 │║
║  │   • Sub fitur 1     │  │                                 │║
║  │   • Sub fitur 2     │  │                                 │║
║  │   • Sub fitur 3     │  │                                 │║
║  │                     │  │                                 │║
║  │   [ Pelajari → ]    │  │                                 │║
║  │                     │  │                                 │║
║  └─────────────────────┘  └─────────────────────────────────┘║
║                                                               ║
║  ┌─────────────────────────────────┐  ┌─────────────────────┐║
║  │                                 │  │                     │║
║  │   (Mirror layout — mockup di    │  │   ICON XL           │║
║  │    kiri, teks di kanan)         │  │                     │║
║  │                                 │  │   Nama Fitur 2      │║
║  │   [ Feature Mockup Area ]       │  │   Spotlight         │║
║  │                                 │  │                     │║
║  │                                 │  │   Deskripsi...      │║
║  │                                 │  │                     │║
║  └─────────────────────────────────┘  └─────────────────────┘║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

  Layout: row > col-lg-6 + col-lg-6
  Odd: teks kiri, visual kanan
  Even: visual kiri, teks kanan (reverse)
  Mobile: selalu stacked (teks atas, visual bawah)
```

### 4. CTA Section

```
╔═══════════════════════════════════════════════════════════════╗
║                                                               ║
║     ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░     ║
║     ░░                                                   ░░  ║
║     ░░              (radial glow ungu)                   ░░  ║
║     ░░                                                   ░░  ║
║     ░░     ┌──────────────────────────────────────┐      ░░  ║
║     ░░     │                                      │      ░░  ║
║     ░░     │     MULAI HARI INI                   │      ░░  ║
║     ░░     │     ← gold eyeline                   │      ░░  ║
║     ░░     │                                      │      ░░  ║
║     ░░     │  Siap Digitalisasi Sekolah Anda?     │      ░░  ║
║     ░░     │  ← Trajan Pro title                  │      ░░  ║
║     ░░     │                                      │      ░░  ║
║     ░░     │  Bergabung. Gratis. Sekarang.        │      ░░  ║
║     ░░     │  ← gradient subtitle                 │      ░░  ║
║     ░░     │                                      │      ░░  ║
║     ░░     │  Tingkatkan efisiensi administrasi   │      ░░  ║
║     ░░     │  lembaga Anda bersama ekosistem      │      ░░  ║
║     ░░     │  sekolah modern — tanpa biaya.       │      ░░  ║
║     ░░     │                                      │      ░░  ║
║     ░░     │  [ Daftar Gratis Sekarang → ]        │      ░░  ║
║     ░░     │  [ ▶ Lihat Live Demo ]               │      ░░  ║
║     ░░     │                                      │      ░░  ║
║     ░░     └──────────────────────────────────────┘      ░░  ║
║     ░░                                                   ░░  ║
║     ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░     ║
║                                                               ║
╚═══════════════════════════════════════════════════════════════╝

  .cta-box: background var(--surface), border-radius 20px, padding 64px 40px
  .cta-glow: radial-gradient ungu di pojok kanan atas
  .cta-glow-2: radial-gradient cyan di pojok kiri bawah
  Mobile: padding → 36px 18px, border-radius → 14px
```

---

## H. DATA STRUKTUR — 24 Kategori Fitur

Berikut array data yang akan diiterasi untuk render feature cards:

```php
@php
  $categories = [
    [
      'icon'     => 'tabler-shield-lock',
      'title'    => 'Keamanan & Akses',
      'desc'     => 'RBAC 12+ role, impersonation, dan kontrol akses granular untuk setiap pengguna sistem.',
      'count'    => '5+ sub-fitur',
      'color'    => 'rgba(234, 84, 85, 0.10)',
      'colorHex' => '#ea5455',
      'tags'     => ['keamanan'],
    ],
    [
      'icon'     => 'tabler-layout-dashboard',
      'title'    => 'Dashboard & Analitik',
      'desc'     => 'Live monitor, analitik mendalam, dan gamifikasi untuk memotivasi siswa.',
      'count'    => '8+ sub-fitur',
      'color'    => 'rgba(108, 99, 255, 0.10)',
      'colorHex' => '#a89ff7',
      'tags'     => ['analitik'],
    ],
    [
      'icon'     => 'tabler-database',
      'title'    => 'Data Master',
      'desc'     => 'Kelola data guru, siswa, kelas, dan master data lainnya dalam satu tempat.',
      'count'    => '6+ sub-fitur',
      'color'    => 'rgba(0, 207, 232, 0.10)',
      'colorHex' => '#00cfe8',
      'tags'     => ['data'],
    ],
    [
      'icon'     => 'tabler-checklist',
      'title'    => 'Absensi Siswa',
      'desc'     => 'QR Code, bulk scan, auto-alpha, dan pencatatan kehadiran siswa secara real-time.',
      'count'    => '7+ sub-fitur',
      'color'    => 'rgba(40, 199, 111, 0.10)',
      'colorHex' => '#28c76f',
      'tags'     => ['absensi'],
    ],
    [
      'icon'     => 'tabler-school',
      'title'    => 'Absensi Guru',
      'desc'     => 'Monitoring kehadiran guru dengan QR Code dan dashboard khusus untuk waka.',
      'count'    => '4+ sub-fitur',
      'color'    => 'rgba(255, 159, 67, 0.10)',
      'colorHex' => '#ff9f43',
      'tags'     => ['absensi'],
    ],
    [
      'icon'     => 'tabler-users',
      'title'    => 'Absensi Staff TU',
      'desc'     => 'Pencatatan kehadiran staff tata usaha dan tenaga kependidikan.',
      'count'    => '3+ sub-fitur',
      'color'    => 'rgba(96, 165, 250, 0.10)',
      'colorHex' => '#60a5fa',
      'tags'     => ['absensi'],
    ],
    [
      'icon'     => 'tabler-file-text',
      'title'    => 'Izin & Sakit',
      'desc'     => 'Approval workflow, kuota izin, notifikasi WhatsApp, dan riwayat lengkap.',
      'count'    => '5+ sub-fitur',
      'color'    => 'rgba(168, 159, 247, 0.10)',
      'colorHex' => '#a89ff7',
      'tags'     => ['absensi'],
    ],
    [
      'icon'     => 'tabler-target',
      'title'    => 'Kegiatan Khusus',
      'desc'     => 'Scan presensi untuk kegiatan non-rutin, live board, dan rekap otomatis.',
      'count'    => '4+ sub-fitur',
      'color'    => 'rgba(52, 211, 153, 0.10)',
      'colorHex' => '#34d399',
      'tags'     => ['kegiatan'],
    ],
    [
      'icon'     => 'tabler-trophy',
      'title'    => 'Ekstrakurikuler',
      'desc'     => 'Absensi ekskul, rekap kehadiran, dan laporan untuk pembina.',
      'count'    => '3+ sub-fitur',
      'color'    => 'rgba(251, 191, 36, 0.10)',
      'colorHex' => '#fbbf24',
      'tags'     => ['kegiatan'],
    ],
    [
      'icon'     => 'tabler-award',
      'title'    => 'Pelepasan Kelas XII',
      'desc'     => 'Modul khusus untuk acara pelepasan — scan, login, dan live board.',
      'count'    => '3+ sub-fitur',
      'color'    => 'rgba(226, 185, 111, 0.10)',
      'colorHex' => '#e2b96f',
      'tags'     => ['kegiatan'],
    ],
    [
      'icon'     => 'tabler-device-mobile',
      'title'    => 'Portal Khusus',
      'desc'     => '6 portal terpisah untuk Siswa, Guru, Wali Kelas, Wali Murid, Admin, dan Publik.',
      'count'    => '6 portal',
      'color'    => 'rgba(139, 92, 246, 0.10)',
      'colorHex' => '#8b5cf6',
      'tags'     => ['portal'],
    ],
    [
      'icon'     => 'tabler-eye',
      'title'    => 'Monitoring Real-Time',
      'desc'     => 'Pantau kehadiran seluruh sekolah secara live dengan Live Board Display.',
      'count'    => '4+ sub-fitur',
      'color'    => 'rgba(34, 211, 238, 0.10)',
      'colorHex' => '#22d3ee',
      'tags'     => ['monitoring'],
    ],
    [
      'icon'     => 'tabler-gamepad-2',
      'title'    => 'Gamifikasi',
      'desc'     => 'Poin, badge, leaderboard, dan streak untuk memotivasi kedisiplinan siswa.',
      'count'    => '5+ sub-fitur',
      'color'    => 'rgba(236, 72, 153, 0.10)',
      'colorHex' => '#ec4899',
      'tags'     => ['inovasi'],
    ],
    [
      'icon'     => 'tabler-qrcode',
      'title'    => 'QR Code & Scan',
      'desc'     => 'Generasi QR dinamis, multi-device scan, dan validasi anti-fraud.',
      'count'    => '4+ sub-fitur',
      'color'    => 'rgba(40, 199, 111, 0.10)',
      'colorHex' => '#28c76f',
      'tags'     => ['teknologi'],
    ],
    [
      'icon'     => 'tabler-id',
      'title'    => 'Kartu Identitas',
      'desc'     => 'Generate kartu identitas siswa/guru dengan QR Code presensi terintegrasi.',
      'count'    => '3+ sub-fitur',
      'color'    => 'rgba(96, 165, 250, 0.10)',
      'colorHex' => '#60a5fa',
      'tags'     => ['teknologi'],
    ],
    [
      'icon'     => 'tabler-alert-triangle',
      'title'    => 'Pelanggaran Siswa',
      'desc'     => 'Pencatatan pelanggaran, poin demerit, sanksi, dan laporan kedisiplinan.',
      'count'    => '5+ sub-fitur',
      'color'    => 'rgba(239, 68, 68, 0.10)',
      'colorHex' => '#ef4444',
      'tags'     => ['keamanan'],
    ],
    [
      'icon'     => 'tabler-message-report',
      'title'    => 'Pengaduan Data',
      'desc'     => 'Sistem pengaduan dan koreksi data presensi dengan workflow approval.',
      'count'    => '3+ sub-fitur',
      'color'    => 'rgba(251, 146, 60, 0.10)',
      'colorHex' => '#fb923c',
      'tags'     => ['data'],
    ],
    [
      'icon'     => 'tabler-plug-connected',
      'title'    => 'Integrasi Eksternal',
      'desc'     => 'Terhubung dengan sistem Dapodik, WhatsApp, Google Calendar, dan lainnya.',
      'count'    => '4+ sub-fitur',
      'color'    => 'rgba(99, 102, 241, 0.10)',
      'colorHex' => '#6366f1',
      'tags'     => ['integrasi'],
    ],
    [
      'icon'     => 'tabler-brain',
      'title'    => 'AI & Inovasi',
      'desc'     => 'Prediksi kehadiran, deteksi anomali, dan insight berbasis kecerdasan buatan.',
      'count'    => '3+ sub-fitur',
      'color'    => 'rgba(168, 85, 247, 0.10)',
      'colorHex' => '#a855f7',
      'tags'     => ['inovasi'],
    ],
    [
      'icon'     => 'tabler-settings',
      'title'    => 'Pengaturan Sistem',
      'desc'     => 'Konfigurasi fleksibel — jam sekolah, auto-alpha, notifikasi, dan parameter lainnya.',
      'count'    => '8+ sub-fitur',
      'color'    => 'rgba(148, 163, 184, 0.10)',
      'colorHex' => '#94a3b8',
      'tags'     => ['sistem'],
    ],
    [
      'icon'     => 'tabler-terminal-2',
      'title'    => 'Developer Tools',
      'desc'     => 'Shell, backup, cache manager, system info, dan tools pengembangan lanjutan.',
      'count'    => '5+ sub-fitur',
      'color'    => 'rgba(34, 211, 238, 0.10)',
      'colorHex' => '#22d3ee',
      'tags'     => ['sistem'],
    ],
    [
      'icon'     => 'tabler-book-2',
      'title'    => 'Panduan & Publik',
      'desc'     => 'Panduan pengguna, bantuan, kebijakan privasi, dan halaman publik lainnya.',
      'count'    => '4+ sub-fitur',
      'color'    => 'rgba(253, 224, 71, 0.10)',
      'colorHex' => '#fde047',
      'tags'     => ['publik'],
    ],
    [
      'icon'     => 'tabler-api',
      'title'    => 'API REST',
      'desc'     => 'RESTful API untuk integrasi mobile app, sistem eksternal, dan automasi.',
      'count'    => '6+ endpoint',
      'color'    => 'rgba(52, 211, 153, 0.10)',
      'colorHex' => '#34d399',
      'tags'     => ['integrasi'],
    ],
    [
      'icon'     => 'tabler-shield-checkered',
      'title'    => 'Keamanan Lanjutan',
      'desc'     => 'Geofencing, 2FA, audit log, rate limiting, dan enkripsi data end-to-end.',
      'count'    => '5+ sub-fitur',
      'color'    => 'rgba(248, 113, 113, 0.10)',
      'colorHex' => '#f87171',
      'tags'     => ['keamanan'],
    ],
  ];

  $categoriesAll = ['semua', 'keamanan', 'absensi', 'analitik', 'data', 'kegiatan', 'portal', 'monitoring', 'inovasi', 'teknologi', 'integrasi', 'sistem', 'publik'];
@endphp
```

---

## I. CATEGORY FILTER PILLS

### Tags Mapping
| Tag | Label | Icon |
|-----|-------|------|
| semua | Semua Fitur | `tabler-grid-dots` |
| keamanan | Keamanan | `tabler-shield` |
| absensi | Absensi | `tabler-checklist` |
| analitik | Analitik | `tabler-chart-bar` |
| data | Data | `tabler-database` |
| kegiatan | Kegiatan | `tabler-calendar-event` |
| portal | Portal | `tabler-device-mobile` |
| monitoring | Monitoring | `tabler-eye` |
| inovasi | Inovasi | `tabler-sparkles` |
| teknologi | Teknologi | `tabler-cpu` |
| integrasi | Integrasi | `tabler-plug` |
| sistem | Sistem | `tabler-settings` |
| publik | Publik | `tabler-world` |

### Filter Logic
- Klik pill → card yang tidak match akan `display: none` dengan animasi fade-out
- "Semua" → tampilkan semua card
- Multiple tags tidak didukung (single select)
- Active pill: `background: rgba(108, 99, 255, 0.25)`, `border: 0.5px solid rgba(108, 99, 255, 0.40)`

---

## J. ACCESSIBILITY

### Color Kontras
| Element | Foreground | Background | Ratio | WCAG |
|---------|-----------|------------|-------|------|
| Card Title | `#fff` | `#0d1120` | 15.4:1 | AAA ✅ |
| Card Desc | `rgba(255,255,255,0.5)` | `#0d1120` | 7.7:1 | AAA ✅ |
| Section Label | `#a89ff7` | `rgba(108,99,255,0.1)` | 5.2:1 | AA ✅ |
| Hero Title | `#fff` | `#07090f` | 17.9:1 | AAA ✅ |
| Gold Eyeline | `#e2b96f` | `#07090f` | 8.9:1 | AAA ✅ |

### Keyboard Navigation
- Category pills: `tabindex="0"`, `role="tab"`, `aria-selected`
- Feature cards: `role="article"`, `aria-label="[Nama Kategori]"`
- CTA buttons: `tabindex="0"`, focus ring visible

### Focus States
```css
.feature-card:focus-visible,
.category-pill:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}
```

---

## K. IMPLEMENTATION NOTES

### File yang Perlu Dibuat/Dimodifikasi
1. **`resources/views/content/pages/pages-fitur.blade.php`** — File baru, halaman fitur utama

### Struktur Blade
```
@extends('layouts/layoutMaster')
@section('title', 'Fitur Lengkap — ' . $namaSekolah)
@section('content')
  <style>/* inline CSS mengikuti pattern pages-home */</style>

  {{-- HERO --}}
  <section class="section-hero"> ... </section>

  {{-- STATS BAR --}}
  <section class="section-stats"> ... </section>

  {{-- CATEGORY FILTER --}}
  <section class="section-features-filter"> ... </section>

  {{-- FEATURE GRID --}}
  <section id="fitur" class="section-features">
    <div class="row g-3">
      @foreach($categories as $i => $cat)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3"
             data-category="{{ implode(' ', $cat['tags']) }}"
             data-aos="fade-up"
             data-aos-delay="{{ ($i % 4) * 60 }}">
          <div class="feature-card">
            <div class="feature-icon" style="background:{{ $cat['color'] }}; color:{{ $cat['colorHex'] }};">
              <i class="ti {{ $cat['icon'] }}"></i>
            </div>
            <h5>{{ $cat['title'] }}</h5>
            <p>{{ $cat['desc'] }}</p>
            <div class="feature-count">
              <i class="ti tabler-stack-2" style="font-size:10px;"></i>
              {{ $cat['count'] }}
            </div>
            <a href="#" class="feature-link">Explore <i class="ti tabler-arrow-right"></i></a>
            <div class="feature-card-line"></div>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  {{-- SPOTLIGHT (opsional, 3 feature unggulan) --}}
  <section class="section-spotlight"> ... </section>

  {{-- CTA --}}
  <section class="section-cta"> ... </section>

  {{-- AOS Library --}}
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      AOS.init({ duration: 700, once: true, easing: 'ease-out-quart', offset: 40 });
    });
  </script>
@endsection
```

### CSS Variables (reuse dari pages-home)
```css
:root {
  --bg: #07090f;
  --surface: #0d1120;
  --surface2: #121829;
  --border: rgba(255, 255, 255, 0.07);
  --primary: #6c63ff;
  --info: #22d3ee;
  --gold: #e2b96f;
  --text: #e8eaf0;
  --muted: #5a6478;
  --muted2: #8892a4;
  --radius: 14px;
}
```

### JavaScript — Category Filter Logic (Alpine.js compatible)
```javascript
document.addEventListener('DOMContentLoaded', function() {
  const pills = document.querySelectorAll('.category-pill');
  const cards = document.querySelectorAll('.feature-card-wrap');

  pills.forEach(pill => {
    pill.addEventListener('click', function() {
      // Update active state
      pills.forEach(p => p.classList.remove('active'));
      this.classList.add('active');

      const tag = this.dataset.tag;

      cards.forEach(card => {
        if (tag === 'semua' || card.dataset.category.includes(tag)) {
          card.style.display = '';
          card.style.opacity = '1';
          card.style.transform = 'scale(1)';
        } else {
          card.style.opacity = '0';
          card.style.transform = 'scale(0.95)';
          setTimeout(() => { card.style.display = 'none'; }, 250);
        }
      });
    });
  });
});
```

---

## 📦 Handoff ke Teh Ayu

### File yang Perlu Dibuat
- `resources/views/content/pages/pages-fitur.blade.php` — Halaman fitur baru (100% file baru)

### Komponen yang Di-reuse
- Layout: `layouts/layoutMaster`
- Hero pattern: copy dari `pages-home.blade.php` (section-hero, hero-badge, hero-title, btn-primary-live, btn-ghost-live)
- Stats bar pattern: copy dari `pages-home.blade.php` (section-stats, stat-val, stat-label)
- Feature card pattern: enhance dari `pages-home.blade.php` (feature-card, feature-icon, feature-card-line)
- CTA section: copy dari `pages-home.blade.php` (section-cta, cta-box, cta-glow)
- AOS init: copy dari `pages-home.blade.php`

### Komponen Baru yang Perlu Dibuat
1. **Category Filter Pills** — horizontal scroll container dengan pill buttons
2. **Feature Count Badge** — badge kecil yang menunjukkan jumlah sub-fitur
3. **Explore Link** — teks link "Explore →" di setiap card
4. **Spotlight Section** — 2-kolom alternating layout untuk 3 feature unggulan

### Catatan Implementasi
1. **CSS inline** — Ikuti pola `pages-home.blade.php` yang menggunakan `<style>` inline di dalam `@section('content')`
2. **Variabel CSS** — Reuse `:root` variables yang sama dengan home page
3. **Font** — Gunakan `Trajan Pro` untuk display headings, `Product Sans` untuk body text
4. **AOS** — Load AOS CSS & JS dari CDN (sama seperti home page)
5. **Filter animation** — Gunakan CSS transition `opacity .25s` + `transform .25s` untuk hide/show cards
6. **Icon** — Semua icon dari Tabler Icons library dengan class `.ti.tabler-*`
7. **Responsive** — Pastikan mockup visual hero hidden di mobile (`hero-img-col { display: none !important }`)
8. **24 card** — Grid 4 kolom desktop, 3 kolom tablet landscape, 2 kolom tablet portrait, 1 kolom small phone
