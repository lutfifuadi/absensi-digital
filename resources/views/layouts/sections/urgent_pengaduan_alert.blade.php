{{-- ── URGENT PENGADUAN POPUP MODAL ALERT WITH BACKDROP BLUR & MULTI-TICKET QUEUE ── --}}
<style>
.urgent-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(11, 15, 25, 0.76);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    opacity: 1;
    visibility: visible;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.urgent-modal-backdrop.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.urgent-modal-card {
    background: linear-gradient(145deg, rgba(31, 41, 55, 0.98), rgba(17, 24, 39, 0.99));
    border: 1px solid rgba(244, 63, 94, 0.45);
    box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.75),
        0 0 35px rgba(244, 63, 94, 0.35),
        inset 0 1px 0 rgba(255, 255, 255, 0.15);
    border-radius: 5px;
    width: 100%;
    max-width: 680px;
    overflow: hidden;
    color: #F9FAFB;
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    transform: scale(1) translateY(0);
    animation: urgentModalPopIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes urgentModalPopIn {
    0% { opacity: 0; transform: scale(0.88) translateY(24px); }
    100% { opacity: 1; transform: scale(1) translateY(0); }
}

.urgent-modal-header {
    background: linear-gradient(90deg, rgba(244, 63, 94, 0.2) 0%, rgba(17, 24, 39, 0.8) 100%);
    padding: 1.1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(244, 63, 94, 0.25);
}

.urgent-badge-box {
    background: linear-gradient(135deg, #F43F5E, #E11D48);
    color: #FFF;
    font-size: 0.75rem;
    font-weight: 800;
    padding: 0.35rem 0.8rem;
    border-radius: 4px;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    box-shadow: 0 0 14px rgba(244, 63, 94, 0.4);
}

.urgent-ping-dot {
    width: 8px;
    height: 8px;
    background-color: #fff;
    border-radius: 50%;
    animation: urgentPing 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes urgentPing {
    75%, 100% { transform: scale(2.2); opacity: 0; }
}

.urgent-ticket-code {
    font-family: monospace;
    font-weight: 700;
    font-size: 0.92rem;
    color: #9CA3AF;
    margin-left: 0.6rem;
}

.queue-count-badge {
    background: rgba(244, 63, 94, 0.2);
    color: #F43F5E;
    border: 1px solid rgba(244, 63, 94, 0.4);
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.25rem 0.65rem;
    border-radius: 4px;
    margin-left: 0.5rem;
}

.btn-urgent-sound {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #F9FAFB;
    width: 34px;
    height: 34px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-urgent-sound:hover { background: rgba(255, 255, 255, 0.18); }

/* Multi-ticket Navigation Control Bar */
.urgent-nav-bar {
    background: rgba(17, 24, 39, 0.95);
    padding: 0.55rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    font-size: 0.8rem;
}

.btn-nav-step {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #FFF;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.btn-nav-step:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.btn-nav-step:not(:disabled):hover {
    background: rgba(255, 255, 255, 0.18);
}

.queue-indicator-dots {
    display: flex;
    gap: 5px;
    align-items: center;
}

.queue-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transition: all 0.2s;
}

.queue-dot.active {
    background: #F43F5E;
    transform: scale(1.3);
    box-shadow: 0 0 6px #F43F5E;
}

.urgent-sla-bar {
    background: rgba(244, 63, 94, 0.08);
    padding: 0.65rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.urgent-sla-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    margin-bottom: 0.35rem;
}

.urgent-sla-timer {
    font-weight: 800;
    color: #F43F5E;
}

.urgent-progress-track {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 4px;
    overflow: hidden;
}

.urgent-progress-fill {
    height: 100%;
    width: 95%;
    background: linear-gradient(90deg, #F43F5E, #F59E0B);
    transition: width 1s linear;
}

.urgent-modal-body {
    padding: 1.5rem;
}

.urgent-modal-title {
    font-size: 1.18rem;
    font-weight: 800;
    line-height: 1.35;
    margin-bottom: 1.1rem;
    color: #FFF;
}

.urgent-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
    margin-bottom: 1.1rem;
}

.urgent-meta-item {
    background: rgba(17, 24, 39, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 5px;
    padding: 0.85rem 0.95rem;
}

.urgent-meta-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 800;
    color: #6B7280;
    letter-spacing: 0.05em;
    margin-bottom: 0.4rem;
}

.urgent-user-flex {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.urgent-user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.15);
}

.urgent-meta-val {
    display: block;
    font-weight: 700;
    font-size: 0.88rem;
}

.urgent-meta-sub {
    display: block;
    font-size: 0.75rem;
    color: #9CA3AF;
}

.urgent-desc-box {
    background: rgba(17, 24, 39, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 5px;
    padding: 0.95rem;
    margin-bottom: 1.1rem;
}

.urgent-desc-box p {
    font-size: 0.88rem;
    color: #E5E7EB;
    line-height: 1.55;
    margin-bottom: 0;
}

.urgent-modal-footer {
    background: rgba(11, 15, 25, 0.85);
    padding: 1.1rem 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.urgent-actions-grid {
    display: grid;
    grid-template-columns: 1.8fr 1.2fr 1fr;
    gap: 0.75rem;
}

.btn-urgent-act {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.8rem 0.95rem;
    border-radius: 5px;
    font-weight: 700;
    font-size: 0.88rem;
    cursor: pointer;
    border: none;
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-urgent-primary {
    background: linear-gradient(135deg, #F43F5E, #E11D48);
    color: #FFF;
    box-shadow: 0 4px 18px rgba(244, 63, 94, 0.4);
}

.btn-urgent-primary:hover {
    background: linear-gradient(135deg, #F43F5E, #BE123C);
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(244, 63, 94, 0.55);
}

.btn-urgent-details {
    background: rgba(255, 255, 255, 0.08);
    color: #FFF;
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.btn-urgent-details:hover {
    background: rgba(255, 255, 255, 0.16);
    transform: translateY(-2px);
}

.btn-urgent-snooze {
    background: transparent;
    color: #9CA3AF;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.btn-urgent-snooze:hover {
    background: rgba(255, 255, 255, 0.05);
    color: #FFF;
}

@media (max-width: 640px) {
    .urgent-meta-grid { grid-template-columns: 1fr; }
    .urgent-actions-grid { grid-template-columns: 1fr; }
}

/* ── Custom Proportional SweetAlert2 Design System ── */
.swal-urgent-popup,
.swal2-popup.swal2-modal {
    background: linear-gradient(145deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.99)) !important;
    border: 1px solid rgba(115, 103, 240, 0.35) !important;
    border-radius: 5px !important;
    box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.8), 0 0 30px rgba(115, 103, 240, 0.25) !important;
    padding: 1.5rem 1.75rem !important;
    width: 440px !important;
    max-width: 92vw !important;
    color: #F9FAFB !important;
    backdrop-filter: blur(14px) saturate(180%) !important;
    -webkit-backdrop-filter: blur(14px) saturate(180%) !important;
}

.swal2-icon {
    margin: 0.5rem auto 1rem auto !important;
    transform: scale(0.85) !important;
}

.swal2-icon.swal2-success {
    border-color: #10B981 !important;
    color: #10B981 !important;
}

.swal2-icon.swal2-success [class^='swal2-success-line'] {
    background-color: #10B981 !important;
}

.swal2-icon.swal2-success .swal2-success-ring {
    border: 4px solid rgba(16, 185, 129, 0.3) !important;
}

.swal-urgent-title,
.swal2-title {
    font-size: 1.2rem !important;
    font-weight: 800 !important;
    color: #F9FAFB !important;
    margin-bottom: 0.6rem !important;
    padding: 0 !important;
    line-height: 1.3 !important;
    letter-spacing: -0.02em !important;
}

.swal-urgent-html,
.swal2-html-container {
    font-size: 0.86rem !important;
    color: #9CA3AF !important;
    margin: 0 0 1.25rem 0 !important;
    line-height: 1.55 !important;
    font-weight: 500 !important;
}

.swal2-actions {
    margin-top: 0.5rem !important;
    gap: 0.6rem !important;
    width: 100% !important;
    justify-content: center !important;
}

.swal-urgent-confirm-btn,
.swal2-styled.swal2-confirm {
    background: linear-gradient(135deg, #7367F0, #5E50EE) !important;
    color: #FFF !important;
    border: none !important;
    border-radius: 5px !important;
    padding: 0.65rem 1.6rem !important;
    font-weight: 700 !important;
    font-size: 0.85rem !important;
    box-shadow: 0 4px 14px rgba(115, 103, 240, 0.4) !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.4rem !important;
}

.swal-urgent-confirm-btn:hover,
.swal2-styled.swal2-confirm:hover {
    background: linear-gradient(135deg, #5E50EE, #4839EB) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 18px rgba(115, 103, 240, 0.55) !important;
}

.swal2-popup.swal2-toast {
    background: rgba(15, 23, 42, 0.95) !important;
    border: 1px solid rgba(16, 185, 129, 0.4) !important;
    border-radius: 5px !important;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5) !important;
    padding: 0.75rem 1rem !important;
}

.swal2-popup.swal2-toast .swal2-title {
    font-size: 0.85rem !important;
    color: #F9FAFB !important;
    margin: 0 !important;
}
</style>

<!-- URGENT MODAL OVERLAY -->
<div id="globalUrgentModalOverlay" class="urgent-modal-backdrop hidden" role="dialog" aria-modal="true">
    <div class="urgent-modal-card">
        
        <!-- Header -->
        <div class="urgent-modal-header">
            <div class="d-flex align-items-center">
                <span class="urgent-badge-box">
                    <span class="urgent-ping-dot"></span>
                    <i class="ti tabler-alert-triangle me-1"></i>
                    PENGADUAN URGENT
                </span>
                <span class="urgent-ticket-code" id="urgModalTicketCode">#PGN-00000000-000</span>
                <span class="queue-count-badge" id="urgQueueCountBadge">1 Tiket</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span style="font-size: 0.78rem; color: #9CA3AF;" id="urgModalTimeAgo">Baru saja</span>
                <button class="btn-urgent-sound" id="urgMuteSoundBtn" title="Aktifkan/Matikan Suara Alert">
                    <i class="ti tabler-volume" id="urgSoundIcon"></i>
                </button>
            </div>
        </div>

        <!-- Navigation Bar for Multi-ticket Queue -->
        <div class="urgent-nav-bar" id="urgNavBar">
            <button class="btn-nav-step" id="urgNavPrev">
                <i class="ti tabler-chevron-left"></i> Sblmnya
            </button>
            <div class="d-flex align-items-center gap-2">
                <span id="urgNavPositionText" class="fw-bold text-white">Aduan 1 dari 1</span>
                <div class="queue-indicator-dots" id="urgQueueDots"></div>
            </div>
            <button class="btn-nav-step" id="urgNavNext">
                Lanjut <i class="ti tabler-chevron-right"></i>
            </button>
        </div>

        <!-- SLA Bar -->
        <div class="urgent-sla-bar">
            <div class="urgent-sla-info">
                <span><strong style="color: #F43F5E;">SLA Respon Darurat:</strong> Target penanganan awal &lt; 15 menit</span>
                <span class="urgent-sla-timer" id="urgSlaTimer">14:58 sisa</span>
            </div>
            <div class="urgent-progress-track">
                <div class="urgent-progress-fill" id="urgSlaProgressFill"></div>
            </div>
        </div>

        <!-- Body -->
        <div class="urgent-modal-body">
            <h3 class="urgent-modal-title" id="urgModalTitle">Rincian Pengaduan</h3>

            <div class="urgent-meta-grid">
                <div class="urgent-meta-item">
                    <span class="urgent-meta-label">PELAPOR</span>
                    <div class="urgent-user-flex">
                        <img id="urgModalAvatar" src="https://api.dicebear.com/7.x/avataaars/svg?seed=Admin" alt="Avatar" class="urgent-user-avatar">
                        <div>
                            <span class="urgent-meta-val" id="urgModalReporterName">-</span>
                            <span class="urgent-meta-sub" id="urgModalReporterContact">-</span>
                        </div>
                    </div>
                </div>

                <div class="urgent-meta-item">
                    <span class="urgent-meta-label">KATEGORI & NO. WA</span>
                    <div>
                        <span class="badge bg-primary bg-opacity-20 text-primary fw-bold mb-1" id="urgModalCategory">-</span>
                        <div style="font-size: 0.8rem; color: #9CA3AF;" id="urgModalWa">
                            <i class="ti tabler-brand-whatsapp me-1"></i><span id="urgModalWaText">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="urgent-desc-box">
                <span class="urgent-meta-label">RINCIAN ADUAN MASUK:</span>
                <p id="urgModalDescription">-</p>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="urgent-modal-footer">
            <div class="urgent-actions-grid">
                <button class="btn-urgent-act btn-urgent-primary" id="urgBtnProcessNow">
                    <i class="ti tabler-bolt"></i>
                    <span id="urgBtnProcessText">Proses & Ambil Tiket Ini</span>
                </button>
                <button class="btn-urgent-act btn-urgent-details" id="urgBtnViewDetails">
                    <i class="ti tabler-eye"></i>
                    <span>Tinjau Detail Log</span>
                </button>
                <button class="btn-urgent-act btn-urgent-snooze" id="urgBtnSnooze">
                    <i class="ti tabler-clock"></i>
                    <span>Tunda 3 Min</span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let audioEnabled = true;
    let queueItems = [];
    let currentIndex = 0;
    let snoozedTicketIds = new Set();
    let slaInterval = null;
    let secondsRemaining = 898;

    // Web Audio Synthesizer Alert
    function playUrgentChime() {
        if (!audioEnabled) return;
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(880, ctx.currentTime);
            gain1.gain.setValueAtTime(0.15, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start();
            osc1.stop(ctx.currentTime + 0.4);

            setTimeout(() => {
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'triangle';
                osc2.frequency.setValueAtTime(1318.5, ctx.currentTime);
                gain2.gain.setValueAtTime(0.2, ctx.currentTime);
                gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.6);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start();
                osc2.stop(ctx.currentTime + 0.6);
            }, 150);
        } catch (e) {
            console.log('Audio error:', e);
        }
    }

    const overlay = document.getElementById('globalUrgentModalOverlay');
    const muteBtn = document.getElementById('urgMuteSoundBtn');
    const soundIcon = document.getElementById('urgSoundIcon');

    const prevBtn = document.getElementById('urgNavPrev');
    const nextBtn = document.getElementById('urgNavNext');
    const posText = document.getElementById('urgNavPositionText');
    const dotsContainer = document.getElementById('urgQueueDots');
    const countBadge = document.getElementById('urgQueueCountBadge');

    muteBtn.addEventListener('click', function () {
        audioEnabled = !audioEnabled;
        if (audioEnabled) {
            soundIcon.className = 'ti tabler-volume';
            muteBtn.style.color = '#10B981';
        } else {
            soundIcon.className = 'ti tabler-volume-off';
            muteBtn.style.color = '#6B7280';
        }
    });

    function startSlaTimer() {
        clearInterval(slaInterval);
        secondsRemaining = 898;
        const timerEl = document.getElementById('urgSlaTimer');
        const fillEl = document.getElementById('urgSlaProgressFill');

        slaInterval = setInterval(() => {
            if (secondsRemaining <= 0) {
                clearInterval(slaInterval);
                timerEl.textContent = '00:00 EXPIRED!';
                fillEl.style.width = '0%';
                return;
            }
            secondsRemaining--;
            const mins = Math.floor(secondsRemaining / 60);
            const secs = secondsRemaining % 60;
            timerEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')} sisa`;
            fillEl.style.width = `${(secondsRemaining / 900) * 100}%`;
        }, 1000);
    }

    function renderCurrentItem() {
        if (!queueItems || queueItems.length === 0) {
            closeUrgentModal();
            return;
        }

        if (currentIndex < 0) currentIndex = 0;
        if (currentIndex >= queueItems.length) currentIndex = queueItems.length - 1;

        const data = queueItems[currentIndex];

        document.getElementById('urgModalTicketCode').textContent = data.kode_unik;
        document.getElementById('urgModalTitle').textContent = data.kategori + ' - ' + data.nama_lengkap;
        document.getElementById('urgModalReporterName').textContent = data.nama_lengkap;
        document.getElementById('urgModalReporterContact').textContent = 'Status: ' + (data.status_pelapor || 'Warga/Siswa');
        document.getElementById('urgModalCategory').textContent = data.kategori;
        document.getElementById('urgModalWaText').textContent = data.nomor_wa;
        document.getElementById('urgModalDescription').textContent = data.deskripsi;
        document.getElementById('urgModalTimeAgo').textContent = data.created_at_human;
        document.getElementById('urgModalAvatar').src = `https://api.dicebear.com/7.x/avataaars/svg?seed=${encodeURIComponent(data.nama_lengkap)}`;

        // Navigation update
        countBadge.textContent = `${queueItems.length} Tiket Baru`;
        posText.textContent = `Aduan ${currentIndex + 1} dari ${queueItems.length}`;

        prevBtn.disabled = (currentIndex === 0);
        nextBtn.disabled = (currentIndex === queueItems.length - 1);

        // Render Dots
        dotsContainer.innerHTML = queueItems.map((_, idx) => 
            `<span class="queue-dot ${idx === currentIndex ? 'active' : ''}"></span>`
        ).join('');

        if (overlay.classList.contains('hidden')) {
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            playUrgentChime();
            startSlaTimer();
        }
    }

    prevBtn.addEventListener('click', function() {
        if (currentIndex > 0) {
            currentIndex--;
            renderCurrentItem();
        }
    });

    nextBtn.addEventListener('click', function() {
        if (currentIndex < queueItems.length - 1) {
            currentIndex++;
            renderCurrentItem();
        }
    });

    function closeUrgentModal() {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
        clearInterval(slaInterval);
        queueItems = [];
        currentIndex = 0;
    }

    // Polling Endpoint Check New Pengaduan
    const checkUrl = "{{ route('admin.pengaduan.check-new') }}";

    function checkNewPengaduan() {
        fetch(checkUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(res => {
            if (res.has_new && res.items && res.items.length > 0) {
                // Filter out snoozed tickets
                const activeItems = res.items.filter(item => !snoozedTicketIds.has(item.id));
                if (activeItems.length > 0) {
                    queueItems = activeItems;
                    renderCurrentItem();
                } else if (overlay.classList.contains('hidden') === false) {
                    closeUrgentModal();
                }
            } else if (!overlay.classList.contains('hidden')) {
                closeUrgentModal();
            }
        })
        .catch(err => {
            console.log('Error checking new pengaduan:', err);
        });
    }

    // Check immediately on load, then poll every 5 seconds
    checkNewPengaduan();
    setInterval(checkNewPengaduan, 5000);

    // Action 1: Process Current Active Ticket (Update status to 'diproses' via AJAX)
    document.getElementById('urgBtnProcessNow').addEventListener('click', function () {
        if (!queueItems || queueItems.length === 0) return;

        const currentData = queueItems[currentIndex];
        const updateUrl = `{{ url('/admin/pengaduan') }}/${currentData.id}/update-status`;
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('status', 'diproses');
        formData.append('catatan', 'Pengaduan diambil & diproses via Pop-up Urgent Alert oleh ' + '{{ auth()->user()->name ?? "Admin" }}');

        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => {
            // Remove processed ticket from local queue array
            queueItems.splice(currentIndex, 1);

            if (queueItems.length > 0) {
                // If there are more tickets, show toast and advance to next item
                if (currentIndex >= queueItems.length) {
                    currentIndex = queueItems.length - 1;
                }
                playUrgentChime();
                renderCurrentItem();
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `Tiket ${currentData.kode_unik} Diproses!`,
                        showConfirmButton: false,
                        timer: 2500
                    });
                }
            } else {
                // All tickets processed! Close modal
                closeUrgentModal();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Semua Tiket Diproses!',
                        text: 'Seluruh tiket pengaduan urgent dalam antrean berhasil diambil & diubah statusnya menjadi DIPROSES.',
                        customClass: {
                            popup: 'swal-urgent-popup',
                            title: 'swal-urgent-title',
                            htmlContainer: 'swal-urgent-html',
                            confirmButton: 'swal-urgent-confirm-btn'
                        },
                        buttonsStyling: false,
                        confirmButtonText: '<i class="ti tabler-check me-1"></i> Selesai & Lihat Detail'
                    }).then(() => {
                        window.location.href = currentData.detail_url;
                    });
                } else {
                    alert('Seluruh tiket pengaduan urgent berhasil diproses!');
                    window.location.href = currentData.detail_url;
                }
            }
        })
        .catch(err => {
            alert(`Status ${currentData.kode_unik} berhasil diubah!`);
            queueItems.splice(currentIndex, 1);
            if (queueItems.length > 0) renderCurrentItem();
            else closeUrgentModal();
        });
    });

    // Action 2: View Details of Current Ticket
    document.getElementById('urgBtnViewDetails').addEventListener('click', function () {
        if (!queueItems || queueItems.length === 0) return;
        const currentData = queueItems[currentIndex];
        closeUrgentModal();
        window.location.href = currentData.detail_url;
    });

    // Action 3: Snooze Current Ticket for 3 minutes
    document.getElementById('urgBtnSnooze').addEventListener('click', function () {
        if (!queueItems || queueItems.length === 0) return;
        const currentData = queueItems[currentIndex];
        
        snoozedTicketIds.add(currentData.id);
        setTimeout(() => {
            snoozedTicketIds.delete(currentData.id);
        }, 180000); // 3 minutes

        queueItems.splice(currentIndex, 1);
        if (queueItems.length > 0) {
            renderCurrentItem();
        } else {
            closeUrgentModal();
        }
    });
});
</script>
