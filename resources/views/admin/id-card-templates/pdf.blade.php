<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body.pdf-body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', sans-serif;
            background: #fff;
        }
        .id-card {
            position: relative;
            width: {{ $config['canvas']['width'] }}pt;
            height: {{ $config['canvas']['height'] }}pt;
            overflow: hidden;
            border-radius: {{ $config['canvas']['border_radius'] ?? 5 }}pt;
            page-break-after: always;
        }
        .background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        .element {
            position: absolute;
            z-index: 10;
        }
        .photo {
            border: 1pt solid #ccc;
            object-fit: cover;
        }
        .qr {
            background: #fff;
        }
        .text {
            /* font-weight is managed via inline styles */
        }
        .element-divider {
            position: absolute;
            z-index: 9;
        }
    </style>
</head>
<body class="pdf-body">
    @foreach($entities as $entity)
    <div class="id-card">
        @php
            $bgBase64 = '';
            if ($template->background_path) {
                if (strlen($template->background_path) > 30 && !str_contains($template->background_path, '/')) {
                    try {
                        $bgBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($template->background_path);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('IdCardTemplate PDF: Gagal load background dari Google Drive: ' . $e->getMessage());
                    }
                } else if (file_exists(storage_path('app/public/' . $template->background_path))) {
                    $bgData = file_get_contents(storage_path('app/public/' . $template->background_path));
                    $bgBase64 = 'data:image/' . pathinfo($template->background_path, PATHINFO_EXTENSION) . ';base64,' . base64_encode($bgData);
                }
            }
        @endphp

        @if($bgBase64)
        <img class="background" src="{{ $bgBase64 }}">
        @endif

        @php
            $elements = $config['elements'];
            // Variabel lembaga: gunakan $lembaga jika ada, fallback array kosong
            $lembagaData = $lembaga ?? [];
        @endphp

        <!-- PHOTO -->
        @if($elements['photo']['show'])
        @php
            $fotoPath = '';
            if(is_array($entity)) {
                $fotoPath = $entity['photo'] ? $entity['photo'] : public_path('assets/img/avatars/1.png');
            } else {
                // Gunakan _foto_base64 jika sudah disiapkan oleh IdCardPdfService
                if (isset($entity->_foto_base64) && $entity->_foto_base64) {
                    $fotoBase64 = $entity->_foto_base64;
                } else {
                    $fotoPath = $entity->foto ? storage_path('app/public/' . $entity->foto) : public_path('assets/img/avatars/1.png');
                    $fotoBase64 = '';
                }
            }
            
            if (!isset($fotoBase64) || empty($fotoBase64)) {
                $fotoBase64 = '';
                if($fotoPath && file_exists($fotoPath)) {
                    $fotoData = @file_get_contents($fotoPath);
                    if ($fotoData !== false) {
                        $fotoBase64 = 'data:image/' . pathinfo($fotoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($fotoData);
                    }
                }
            }
        @endphp
        <div class="element" style="left: {{ $elements['photo']['x'] }}pt; top: {{ $elements['photo']['y'] }}pt;">
            @if($fotoBase64)
            <img class="photo" src="{{ $fotoBase64 }}" 
                 style="width: {{ $elements['photo']['w'] }}pt; height: {{ $elements['photo']['h'] }}pt;">
            @endif
        </div>
        @endif

        <!-- Name -->
        @if($elements['name']['show'])
        <div class="element text" style="
            left: {{ $elements['name']['align'] == 'center' ? 0 : $elements['name']['x'] . 'pt' }}; 
            top: {{ $elements['name']['y'] }}pt;
            width: {{ $elements['name']['align'] == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['name']['align'] }};
            font-size: {{ $elements['name']['size'] }}pt;
            color: {{ $elements['name']['color'] }};
            font-weight: {{ ($elements['name']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['name']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['name']['transform'] ?? 'uppercase' }};
        ">
            {{ is_array($entity) ? ($entity['name'] ?? '') : $entity->nama_lengkap }}
        </div>
        @endif

        <!-- ID Card (NIS/NIP) -->
        @if(isset($elements['id_number']) && $elements['id_number']['show'])
        <div class="element text" style="
            left: {{ ($elements['id_number']['align'] ?? 'center') == 'center' ? 0 : ($elements['id_number']['x'] ?? 0) . 'pt' }}; 
            top: {{ $elements['id_number']['y'] ?? 0 }}pt;
            width: {{ ($elements['id_number']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['id_number']['align'] ?? 'center' }};
            font-size: {{ $elements['id_number']['size'] ?? 8 }}pt;
            color: {{ $elements['id_number']['color'] ?? '#000000' }};
            font-weight: {{ ($elements['id_number']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['id_number']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['id_number']['transform'] ?? 'none' }};
        ">
            {{ is_array($entity) ? ($entity['id_number'] ?? '') : ($entity->nis ?? $entity->nip  ?? '') }}
        </div>
        @endif

        <!-- NIS (Siswa) -->
        @if(isset($elements['nis']) && $elements['nis']['show'])
        <div class="element text" style="
            left: {{ ($elements['nis']['align'] ?? 'center') == 'center' ? 0 : $elements['nis']['x'] . 'pt' }}; 
            top: {{ $elements['nis']['y'] }}pt;
            width: {{ ($elements['nis']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['nis']['align'] ?? 'center' }};
            font-size: {{ $elements['nis']['size'] ?? 12 }}pt;
            color: {{ $elements['nis']['color'] ?? '#555555' }};
            font-weight: {{ ($elements['nis']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['nis']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['nis']['transform'] ?? 'none' }};
        ">
            {{ is_array($entity) ? ($entity['nis'] ?? '') : ($entity->_nis ?? $entity->nis ?? '') }}
        </div>
        @endif

        <!-- NISN (Siswa) -->
        @if(isset($elements['nisn']) && $elements['nisn']['show'])
        <div class="element text" style="
            left: {{ ($elements['nisn']['align'] ?? 'center') == 'center' ? 0 : $elements['nisn']['x'] . 'pt' }}; 
            top: {{ $elements['nisn']['y'] }}pt;
            width: {{ ($elements['nisn']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['nisn']['align'] ?? 'center' }};
            font-size: {{ $elements['nisn']['size'] ?? 12 }}pt;
            color: {{ $elements['nisn']['color'] ?? '#555555' }};
            font-weight: {{ ($elements['nisn']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['nisn']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['nisn']['transform'] ?? 'none' }};
        ">
            {{ is_array($entity) ? ($entity['nisn'] ?? '') : ($entity->_nisn ?? $entity->nisn ?? '') }}
        </div>
        @endif

        <!-- NIP (Guru/Staff) -->
        @if(isset($elements['nip']) && $elements['nip']['show'])
        <div class="element text" style="
            left: {{ ($elements['nip']['align'] ?? 'center') == 'center' ? 0 : $elements['nip']['x'] . 'pt' }}; 
            top: {{ $elements['nip']['y'] }}pt;
            width: {{ ($elements['nip']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['nip']['align'] ?? 'center' }};
            font-size: {{ $elements['nip']['size'] ?? 12 }}pt;
            color: {{ $elements['nip']['color'] ?? '#555555' }};
            font-weight: {{ ($elements['nip']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['nip']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['nip']['transform'] ?? 'none' }};
        ">
            {{ is_array($entity) ? ($entity['nip'] ?? '') : ($entity->_nip ?? $entity->nip ?? '') }}
        </div>
        @endif

        <!-- Class/Position -->
        @if($elements['class']['show'])
        <div class="element text" style="
            left: {{ $elements['class']['align'] == 'center' ? 0 : $elements['class']['x'] . 'pt' }}; 
            top: {{ $elements['class']['y'] }}pt;
            width: {{ $elements['class']['align'] == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['class']['align'] }};
            font-size: {{ $elements['class']['size'] }}pt;
            color: {{ $elements['class']['color'] }};
            font-weight: {{ ($elements['class']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['class']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['class']['transform'] ?? 'none' }};
        ">
            {{ is_array($entity) ? ($entity['class'] ?? '') : ($entity->kelas->nama ?? $entity->jabatan ?? '') }}
        </div>
        @endif

        {{-- EXTRA FIELDS FOR PELEPASAN --}}
        @if(isset($elements['id_number']) && $template->type === 'pelepasan')
            <div class="element text" style="left: {{ $elements['id_number']['x'] ?? 0 }}pt; top: {{ ($elements['id_number']['y'] ?? 0) + 15 }}pt; font-size: {{ ($elements['id_number']['size'] ?? 8) - 1 }}pt; color: {{ $elements['id_number']['color'] ?? '#000000' }};">
                {{ is_array($entity) ? ($entity['nis'] ?? '') : '' }}
            </div>
            <div class="element text" style="left: {{ $elements['id_number']['x'] ?? 0 }}pt; top: {{ ($elements['id_number']['y'] ?? 0) + 28 }}pt; font-size: {{ ($elements['id_number']['size'] ?? 8) - 1 }}pt; color: {{ $elements['id_number']['color'] ?? '#000000' }};">
                {{ is_array($entity) ? ($entity['gender'] ?? '') : '' }}
            </div>
        @endif

        <!-- QR Code -->
        @if($elements['qr']['show'])
        @php
            if (is_array($entity)) {
                $qrData = $entity['qr_code'];
            } elseif (isset($entity->_qr_base64) && $entity->_qr_base64) {
                $qrData = $entity->_qr_base64;
            } else {
                $qrData = \App\Support\QrCodeGenerator::renderDataUri($entity->qr_code ?? $entity->nisn, 200);
            }
        @endphp
        <div class="element" style="left: {{ $elements['qr']['x'] }}pt; top: {{ $elements['qr']['y'] }}pt;">
            <img class="qr" src="{{ $qrData }}" style="width: {{ $elements['qr']['w'] }}pt; height: {{ $elements['qr']['h'] }}pt;">
        </div>
        @endif

        {{-- ===== ELEMEN BARU LEMBAGA ===== --}}

        <!-- Logo Lembaga -->
        @if(isset($elements['logo_lembaga']) && $elements['logo_lembaga']['show'])
        @php
            $logoBase64 = $lembagaData['logo_base64'] ?? '';
            // Jika logo_base64 kosong tapi ada logo_url, gunakan URL langsung
            if (empty($logoBase64) && !empty($lembagaData['logo_url'])) {
                $logoBase64 = $lembagaData['logo_url'];
            }
        @endphp
        @if($logoBase64)
        <div class="element" style="left: {{ $elements['logo_lembaga']['x'] }}pt; top: {{ $elements['logo_lembaga']['y'] }}pt;">
            <img src="{{ $logoBase64 }}" style="width: {{ $elements['logo_lembaga']['w'] ?? 40 }}pt; height: {{ $elements['logo_lembaga']['h'] ?? 40 }}pt; object-fit: contain;">
        </div>
        @endif
        @endif

        <!-- Logo Dinas -->
        @if(isset($elements['logo_dinas']) && $elements['logo_dinas']['show'])
        @php
            $logoDinasBase64 = $lembagaData['logo_dinas_base64'] ?? '';
            if (empty($logoDinasBase64) && !empty($lembagaData['logo_dinas_url'])) {
                $logoDinasBase64 = $lembagaData['logo_dinas_url'];
            }
        @endphp
        @if($logoDinasBase64)
        <div class="element" style="left: {{ $elements['logo_dinas']['x'] }}pt; top: {{ $elements['logo_dinas']['y'] }}pt;">
            <img src="{{ $logoDinasBase64 }}" style="width: {{ $elements['logo_dinas']['w'] ?? 40 }}pt; height: {{ $elements['logo_dinas']['h'] ?? 40 }}pt; object-fit: contain;">
        </div>
        @endif
        @endif

        <!-- Nama Lembaga -->
        @if(isset($elements['nama_lembaga']) && $elements['nama_lembaga']['show'])
        <div class="element text" style="
            left: {{ ($elements['nama_lembaga']['align'] ?? 'left') == 'center' ? 0 : $elements['nama_lembaga']['x'] . 'pt' }};
            top: {{ $elements['nama_lembaga']['y'] }}pt;
            width: {{ ($elements['nama_lembaga']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['nama_lembaga']['align'] ?? 'left' }};
            font-size: {{ $elements['nama_lembaga']['size'] ?? 8 }}pt;
            color: {{ $elements['nama_lembaga']['color'] ?? '#000000' }};
            font-weight: {{ ($elements['nama_lembaga']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['nama_lembaga']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['nama_lembaga']['transform'] ?? 'none' }};
        ">
            {{ $lembagaData['nama_sekolah'] ?? '' }}
        </div>
        @endif

        <!-- Alamat Lembaga -->
        @if(isset($elements['alamat_lembaga']) && $elements['alamat_lembaga']['show'])
        <div class="element" style="
            left: {{ ($elements['alamat_lembaga']['align'] ?? 'left') == 'center' ? 0 : $elements['alamat_lembaga']['x'] . 'pt' }};
            top: {{ $elements['alamat_lembaga']['y'] }}pt;
            width: {{ ($elements['alamat_lembaga']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['alamat_lembaga']['align'] ?? 'left' }};
            font-size: {{ $elements['alamat_lembaga']['size'] ?? 7 }}pt;
            color: {{ $elements['alamat_lembaga']['color'] ?? '#333333' }};
            font-weight: {{ ($elements['alamat_lembaga']['bold'] ?? false) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['alamat_lembaga']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['alamat_lembaga']['transform'] ?? 'none' }};
        ">
            {{ $lembagaData['alamat_lembaga'] ?? '' }}
        </div>
        @endif

        <!-- Jenis Kelamin -->
        @if(isset($elements['gender']) && $elements['gender']['show'])
        @php
            $genderText = '';
            if (!is_array($entity)) {
                $genderText = ($entity->jenis_kelamin ?? '') === 'L' ? 'Laki-laki' : (($entity->jenis_kelamin ?? '') === 'P' ? 'Perempuan' : '');
            } else {
                $genderText = ($entity['gender'] ?? '');
            }
        @endphp
        <div class="element text" style="
            left: {{ ($elements['gender']['align'] ?? 'left') == 'center' ? 0 : $elements['gender']['x'] . 'pt' }};
            top: {{ $elements['gender']['y'] }}pt;
            width: {{ ($elements['gender']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['gender']['align'] ?? 'left' }};
            font-size: {{ $elements['gender']['size'] ?? 8 }}pt;
            color: {{ $elements['gender']['color'] ?? '#000000' }};
            font-weight: {{ ($elements['gender']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['gender']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['gender']['transform'] ?? 'none' }};
        ">
            {{ $genderText }}
        </div>
        @endif

        <!-- TTL (Tempat, Tanggal Lahir) — khusus Siswa -->
        @if(isset($elements['ttl']) && $elements['ttl']['show'])
        @php
            $ttlText = '';
            if (!is_array($entity) && isset($entity->tempat_lahir) && isset($entity->tanggal_lahir)) {
                $ttlText = $entity->tempat_lahir . ', ' . \Carbon\Carbon::parse($entity->tanggal_lahir)->isoFormat('D MMMM Y');
            } elseif (is_array($entity)) {
                $ttlText = $entity['ttl'] ?? '';
            }
        @endphp
        <div class="element" style="
            left: {{ ($elements['ttl']['align'] ?? 'left') == 'center' ? 0 : $elements['ttl']['x'] . 'pt' }};
            top: {{ $elements['ttl']['y'] }}pt;
            width: {{ ($elements['ttl']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['ttl']['align'] ?? 'left' }};
            font-size: {{ $elements['ttl']['size'] ?? 7 }}pt;
            color: {{ $elements['ttl']['color'] ?? '#333333' }};
            font-weight: {{ ($elements['ttl']['bold'] ?? false) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['ttl']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['ttl']['transform'] ?? 'none' }};
        ">
            {{ $ttlText }}
        </div>
        @endif

        <!-- Masa Berlaku Kartu -->
        @if(isset($elements['masa_berlaku']) && $elements['masa_berlaku']['show'])
        @php
            if (!is_array($entity) && isset($entity->_masa_berlaku)) {
                $masaBerlakuText = $entity->_masa_berlaku;
            } elseif (is_array($entity)) {
                $masaBerlakuText = $entity['masa_berlaku'] ?? '';
            } else {
                $masaBerlakuText = 'Selama menjadi anggota aktif';
            }
        @endphp
        <div class="element" style="
            left: {{ ($elements['masa_berlaku']['align'] ?? 'left') == 'center' ? 0 : $elements['masa_berlaku']['x'] . 'pt' }};
            top: {{ $elements['masa_berlaku']['y'] }}pt;
            width: {{ ($elements['masa_berlaku']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['masa_berlaku']['align'] ?? 'left' }};
            font-size: {{ $elements['masa_berlaku']['size'] ?? 7 }}pt;
            color: {{ $elements['masa_berlaku']['color'] ?? '#333333' }};
            font-weight: {{ ($elements['masa_berlaku']['bold'] ?? false) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['masa_berlaku']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['masa_berlaku']['transform'] ?? 'none' }};
        ">
            {{ $masaBerlakuText }}
        </div>
        @endif

        <!-- Tempat & Tanggal Terbit -->
        @if(isset($elements['tempat_tanggal_terbit']) && $elements['tempat_tanggal_terbit']['show'])
        <div class="element" style="
            left: {{ ($elements['tempat_tanggal_terbit']['align'] ?? 'left') == 'center' ? 0 : $elements['tempat_tanggal_terbit']['x'] . 'pt' }};
            top: {{ $elements['tempat_tanggal_terbit']['y'] }}pt;
            width: {{ ($elements['tempat_tanggal_terbit']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['tempat_tanggal_terbit']['align'] ?? 'left' }};
            font-size: {{ $elements['tempat_tanggal_terbit']['size'] ?? 7 }}pt;
            color: {{ $elements['tempat_tanggal_terbit']['color'] ?? '#333333' }};
            font-weight: {{ ($elements['tempat_tanggal_terbit']['bold'] ?? false) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['tempat_tanggal_terbit']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['tempat_tanggal_terbit']['transform'] ?? 'none' }};
        ">
            {{ ($lembagaData['kota_penerbitan'] ?? '') . (($lembagaData['kota_penerbitan'] ?? '') ? ', ' : '') . now()->locale('id')->isoFormat('D MMMM Y') }}
        </div>
        @endif

        <!-- TTD Kepala Sekolah -->
        @if(isset($elements['ttd_kepala_sekolah']) && $elements['ttd_kepala_sekolah']['show'])
        @php
            $ttdBase64 = $lembagaData['ttd_base64'] ?? '';
            $ttdUrl    = $lembagaData['ttd_url'] ?? '';
            $ttdSrc    = !empty($ttdBase64) ? $ttdBase64 : (!empty($ttdUrl) ? $ttdUrl : '');
        @endphp
        @if($ttdSrc)
        <div class="element" style="left: {{ $elements['ttd_kepala_sekolah']['x'] }}pt; top: {{ $elements['ttd_kepala_sekolah']['y'] }}pt;">
            <img src="{{ $ttdSrc }}" style="width: {{ $elements['ttd_kepala_sekolah']['w'] ?? 60 }}pt; height: {{ $elements['ttd_kepala_sekolah']['h'] ?? 30 }}pt; object-fit: contain;">
        </div>
        @endif
        @endif

        <!-- Cap Lembaga -->
        @if(isset($elements['cap_lembaga']) && $elements['cap_lembaga']['show'])
        @php
            $capBase64 = $lembagaData['cap_base64'] ?? '';
            $capUrl    = $lembagaData['cap_url'] ?? '';
            $capSrc    = !empty($capBase64) ? $capBase64 : (!empty($capUrl) ? $capUrl : '');
        @endphp
        @if($capSrc)
        <div class="element" style="left: {{ $elements['cap_lembaga']['x'] }}pt; top: {{ $elements['cap_lembaga']['y'] }}pt;">
            <img src="{{ $capSrc }}" style="width: {{ $elements['cap_lembaga']['w'] ?? 50 }}pt; height: {{ $elements['cap_lembaga']['h'] ?? 50 }}pt; object-fit: contain;">
        </div>
        @endif
        @endif

        <!-- Nama Kepala Sekolah -->
        @if(isset($elements['nama_kepala_sekolah']) && $elements['nama_kepala_sekolah']['show'])
        <div class="element text" style="
            left: {{ ($elements['nama_kepala_sekolah']['align'] ?? 'center') == 'center' ? 0 : $elements['nama_kepala_sekolah']['x'] . 'pt' }};
            top: {{ $elements['nama_kepala_sekolah']['y'] }}pt;
            width: {{ ($elements['nama_kepala_sekolah']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['nama_kepala_sekolah']['align'] ?? 'center' }};
            font-size: {{ $elements['nama_kepala_sekolah']['size'] ?? 8 }}pt;
            color: {{ $elements['nama_kepala_sekolah']['color'] ?? '#000000' }};
            font-weight: {{ ($elements['nama_kepala_sekolah']['bold'] ?? true) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['nama_kepala_sekolah']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['nama_kepala_sekolah']['transform'] ?? 'none' }};
        ">
            {{ $lembagaData['nama_kepala_lembaga'] ?? '' }}
        </div>
        @endif

        <!-- NIP Kepala Sekolah -->
        @if(isset($elements['nip_kepala_sekolah']) && $elements['nip_kepala_sekolah']['show'])
        <div class="element" style="
            left: {{ ($elements['nip_kepala_sekolah']['align'] ?? 'center') == 'center' ? 0 : $elements['nip_kepala_sekolah']['x'] . 'pt' }};
            top: {{ $elements['nip_kepala_sekolah']['y'] }}pt;
            width: {{ ($elements['nip_kepala_sekolah']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['nip_kepala_sekolah']['align'] ?? 'center' }};
            font-size: {{ $elements['nip_kepala_sekolah']['size'] ?? 7 }}pt;
            color: {{ $elements['nip_kepala_sekolah']['color'] ?? '#333333' }};
            font-weight: {{ ($elements['nip_kepala_sekolah']['bold'] ?? false) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['nip_kepala_sekolah']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['nip_kepala_sekolah']['transform'] ?? 'none' }};
        ">
            NIP. {{ $lembagaData['nip_kepala_lembaga'] ?? '' }}
        </div>
        @endif

        {{-- ===== ELEMEN BARU KUSTOMISASI ===== --}}
        
        <!-- Teks Kustom 1 -->
        @if(isset($elements['custom_text_1']) && $elements['custom_text_1']['show'])
        <div class="element text" style="
            left: {{ ($elements['custom_text_1']['align'] ?? 'center') == 'center' ? 0 : $elements['custom_text_1']['x'] . 'pt' }};
            top: {{ $elements['custom_text_1']['y'] }}pt;
            width: {{ ($elements['custom_text_1']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['custom_text_1']['align'] ?? 'center' }};
            font-size: {{ $elements['custom_text_1']['size'] ?? 8 }}pt;
            color: {{ $elements['custom_text_1']['color'] ?? '#000000' }};
            font-weight: {{ ($elements['custom_text_1']['bold'] ?? false) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['custom_text_1']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['custom_text_1']['transform'] ?? 'none' }};
        ">
            {{ $elements['custom_text_1']['content'] ?? 'Teks Kustom 1' }}
        </div>
        @endif

        <!-- Teks Kustom 2 -->
        @if(isset($elements['custom_text_2']) && $elements['custom_text_2']['show'])
        <div class="element text" style="
            left: {{ ($elements['custom_text_2']['align'] ?? 'center') == 'center' ? 0 : $elements['custom_text_2']['x'] . 'pt' }};
            top: {{ $elements['custom_text_2']['y'] }}pt;
            width: {{ ($elements['custom_text_2']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['custom_text_2']['align'] ?? 'center' }};
            font-size: {{ $elements['custom_text_2']['size'] ?? 8 }}pt;
            color: {{ $elements['custom_text_2']['color'] ?? '#000000' }};
            font-weight: {{ ($elements['custom_text_2']['bold'] ?? false) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['custom_text_2']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['custom_text_2']['transform'] ?? 'none' }};
        ">
            {{ $elements['custom_text_2']['content'] ?? 'Teks Kustom 2' }}
        </div>
        @endif

        <!-- Teks Kustom 3 -->
        @if(isset($elements['custom_text_3']) && $elements['custom_text_3']['show'])
        <div class="element text" style="
            left: {{ ($elements['custom_text_3']['align'] ?? 'center') == 'center' ? 0 : $elements['custom_text_3']['x'] . 'pt' }};
            top: {{ $elements['custom_text_3']['y'] }}pt;
            width: {{ ($elements['custom_text_3']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['custom_text_3']['align'] ?? 'center' }};
            font-size: {{ $elements['custom_text_3']['size'] ?? 8 }}pt;
            color: {{ $elements['custom_text_3']['color'] ?? '#000000' }};
            font-weight: {{ ($elements['custom_text_3']['bold'] ?? false) ? 'bold' : 'normal' }};
            font-style: {{ ($elements['custom_text_3']['italic'] ?? false) ? 'italic' : 'normal' }};
            text-transform: {{ $elements['custom_text_3']['transform'] ?? 'none' }};
        ">
            {{ $elements['custom_text_3']['content'] ?? 'Teks Kustom 3' }}
        </div>
        @endif

        <!-- Divider 1 -->
        @if(isset($elements['divider_1']) && $elements['divider_1']['show'])
        <div class="element-divider" style="
            left: {{ $elements['divider_1']['x'] }}pt;
            top: {{ $elements['divider_1']['y'] }}pt;
            width: {{ $elements['divider_1']['w'] }}pt;
            height: {{ $elements['divider_1']['h'] }}pt;
            background-color: {{ $elements['divider_1']['color'] }};
        "></div>
        @endif

        <!-- Divider 2 -->
        @if(isset($elements['divider_2']) && $elements['divider_2']['show'])
        <div class="element-divider" style="
            left: {{ $elements['divider_2']['x'] }}pt;
            top: {{ $elements['divider_2']['y'] }}pt;
            width: {{ $elements['divider_2']['w'] }}pt;
            height: {{ $elements['divider_2']['h'] }}pt;
            background-color: {{ $elements['divider_2']['color'] }};
        "></div>
        @endif

    </div>
    @endforeach
</body>
</html>
