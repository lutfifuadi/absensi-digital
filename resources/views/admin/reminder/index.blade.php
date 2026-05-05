@extends('layouts/layoutMaster')

@section('title', 'Pengaturan Reminder WhatsApp/SMS')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Pengaturan /</span> Reminder WhatsApp/SMS
</h4>

<div class="row mb-4">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center">
        <div class="avatar avatar-xl mx-auto mb-3 bg-label-success">
          <i class="ti tabler-brand-whatsapp"></i>
        </div>
        <h3 class="fw-bold mb-0" id="waEnabled">-</h3>
        <small class="text-muted">WhatsApp Gateway</small>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center">
        <div class="avatar avatar-xl mx-auto mb-3 bg-label-info">
          <i class="ti tabler-message-dots"></i>
        </div>
        <h3 class="fw-bold mb-0" id="totalReminders">-</h3>
        <small class="text-muted">Konfigurasi Aktif</small>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center py-3">
    <h5 class="mb-0"><i class="ti tabler-bell me-2"></i>Konfigurasi Reminder</h5>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Jenis Reminder</th>
          <th class="text-center">Status</th>
          <th class="text-center">Kanal</th>
          <th class="text-center">Kirim Sebelum</th>
          <th class="text-center">Notifikasi Ortu</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody id="reminderBody">
        <tr>
          <td colspan="6" class="text-center py-4 text-muted">
            <i class="ti tabler-loader-2 me-2"></i>Memuat data...
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="editReminderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Reminder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editReminderId">
        
        <div class="mb-3">
          <label class="form-label">Aktifkan Reminder</label>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="editIsEnabled">
            <label class="form-check-label" for="editIsEnabled">Aktif</label>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Kanal Pengiriman</label>
          <select id="editChannel" class="form-select">
            <option value="whatsapp">WhatsApp</option>
            <option value="sms">SMS</option>
            <option value="both">Keduanya</option>
          </select>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Kirim Sebelum (menit)</label>
          <input type="number" id="editSendBefore" class="form-control" min="0">
        </div>
        
        <div class="mb-3">
          <label class="form-label">Pesan Kustom</label>
          <textarea id="editCustomMessage" class="form-control" rows="3"></textarea>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Kirim ke Orang Tua</label>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="editNotifyParent">
            <label class="form-check-label" for="editNotifyParent">Aktif</label>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" onclick="saveReminder()">Simpan</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  loadReminderSettings();
  checkWhatsAppStatus();
});

async function checkWhatsAppStatus() {
  try {
    const waStatus = {{ \App\Services\WhatsAppService::isEnabled() ? 'true' : 'false' }};
    document.getElementById('waEnabled').textContent = waStatus ? 'Aktif' : 'Nonaktif';
    document.getElementById('waEnabled').className = 'fw-bold mb-0 ' + (waStatus ? 'text-success' : 'text-muted');
  } catch (e) {
    document.getElementById('waEnabled').textContent = 'Nonaktif';
  }
}

async function loadReminderSettings() {
  try {
    const response = await fetch('/api/v1/innovation/reminder-settings');
    const result = await response.json();
    
    const tbody = document.getElementById('reminderBody');
    const data = result.data || [];
    
    document.getElementById('totalReminders').textContent = data.filter(d => d.is_enabled).length;
    
    if (data.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center py-4 text-muted">
            <i class="ti tabler-info-circle me-2"></i>Belum ada konfigurasi reminder.
          </td>
        </tr>
      `;
      return;
    }
    
    const reminderTypes = {
      'absen_masuk': 'Absen Masuk',
      'absen_pulang': 'Absen Pulang',
      'izin_approve': 'Izin Disetujui',
      'izin_reject': 'Izin Ditolak',
      'sekolah_start': ' sekolah Dimulai'
    };
    
    tbody.innerHTML = data.map(reminder => `
      <tr>
        <td class="fw-medium">${reminderTypes[reminder.reminder_type] || reminder.reminder_type}</td>
        <td class="text-center">
          <span class="badge ${reminder.is_enabled ? 'bg-success' : 'bg-secondary'}">${reminder.is_enabled ? 'Aktif' : 'Nonaktif'}</span>
        </td>
        <td class="text-center">
          <span class="badge bg-info">${reminder.channel}</span>
        </td>
        <td class="text-center">${reminder.send_before_minutes} menit</td>
        <td class="text-center">
          <span class="badge ${reminder.notify_parent ? 'bg-primary' : 'bg-secondary'}">${reminder.notify_parent ? 'Ya' : 'Tidak'}</span>
        </td>
        <td class="text-center">
          <button class="btn btn-icon btn-sm btn-primary" onclick="editReminder(${reminder.id})">
            <i class="ti tabler-edit"></i>
          </button>
        </td>
      </tr>
    `).join('');
    
  } catch (e) {
    console.error('Error loading reminder settings:', e);
  }
}

function editReminder(id) {
  const row = document.querySelector(`tr[data-id="${id}"]`);
  document.getElementById('editReminderId').value = id;
  
  const modal = new bootstrap.Modal(document.getElementById('editReminderModal'));
  modal.show();
}

async function saveReminder() {
  const id = document.getElementById('editReminderId').value;
  
  const data = {
    is_enabled: document.getElementById('editIsEnabled').checked,
    channel: document.getElementById('editChannel').value,
    send_before_minutes: document.getElementById('editSendBefore').value,
    custom_message: document.getElementById('editCustomMessage').value,
    notify_parent: document.getElementById('editNotifyParent').checked
  };
  
  try {
    const response = await fetch(`/api/v1/innovation/reminder-settings/${id}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert('Pengaturan berhasil disimpan!');
      loadReminderSettings();
      bootstrap.Modal.getInstance(document.getElementById('editReminderModal')).hide();
    }
  } catch (e) {
    console.error('Error saving reminder:', e);
  }
}
</script>
@endsection