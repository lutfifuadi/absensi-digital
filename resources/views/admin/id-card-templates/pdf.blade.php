<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
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
            font-weight: bold;
        }
    </style>
</head>
<body>
    @foreach($entities as $entity)
    <div class="id-card">
        @php
            $bgBase64 = '';
            if($template->background_path && file_exists(storage_path('app/public/' . $template->background_path))) {
                $bgData = file_get_contents(storage_path('app/public/' . $template->background_path));
                $bgBase64 = 'data:image/' . pathinfo($template->background_path, PATHINFO_EXTENSION) . ';base64,' . base64_encode($bgData);
            }
        @endphp

        @if($bgBase64)
        <img class="background" src="{{ $bgBase64 }}">
        @endif

        @php
            $elements = $config['elements'];
        @endphp

        <!-- PHOTO -->
        @if($elements['photo']['show'])
        @php
            $fotoPath = '';
            if(is_array($entity)) {
                $fotoPath = $entity['photo'] ? $entity['photo'] : public_path('assets/img/avatars/1.png');
            } else {
                $fotoPath = $entity->foto ? storage_path('app/public/' . $entity->foto) : public_path('assets/img/avatars/1.png');
            }
            
            $fotoBase64 = '';
            if($fotoPath && file_exists($fotoPath)) {
                $fotoData = file_get_contents($fotoPath);
                $fotoBase64 = 'data:image/' . pathinfo($fotoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($fotoData);
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
        ">
            {{ is_array($entity) ? ($entity['name'] ?? '') : strtoupper($entity->nama_lengkap) }}
        </div>
        @endif

        <!-- ID Card (NIS/NIP) -->
        @if($elements['id_number']['show'])
        <div class="element text" style="
            left: {{ $elements['id_number']['align'] == 'center' ? 0 : $elements['id_number']['x'] . 'pt' }}; 
            top: {{ $elements['id_number']['y'] }}pt;
            width: {{ $elements['id_number']['align'] == 'center' ? '100%' : 'auto' }};
            text-align: {{ $elements['id_number']['align'] }};
            font-size: {{ $elements['id_number']['size'] }}pt;
            color: {{ $elements['id_number']['color'] }};
        ">
            {{ is_array($entity) ? ($entity['id_number'] ?? '') : ($entity->nis ?? $entity->nip  ?? '') }}
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
        ">
            {{ is_array($entity) ? ($entity['class'] ?? '') : ($entity->kelas->nama ?? $entity->jabatan ?? '') }}
        </div>
        @endif

        {{-- EXTRA FIELDS FOR PELEPASAN --}}
        @if($template->type === 'pelepasan')
            <div class="element text" style="left: {{ $elements['id_number']['x'] }}pt; top: {{ ($elements['id_number']['y'] + 15) }}pt; font-size: {{ ($elements['id_number']['size'] - 1) }}pt; color: {{ $elements['id_number']['color'] }};">
                {{ is_array($entity) ? ($entity['nis'] ?? '') : '' }}
            </div>
            <div class="element text" style="left: {{ $elements['id_number']['x'] }}pt; top: {{ ($elements['id_number']['y'] + 28) }}pt; font-size: {{ ($elements['id_number']['size'] - 1) }}pt; color: {{ $elements['id_number']['color'] }};">
                {{ is_array($entity) ? ($entity['gender'] ?? '') : '' }}
            </div>
        @endif

        <!-- QR Code -->
        @if($elements['qr']['show'])
        @php
            $qrData = is_array($entity) ? $entity['qr_code'] : \App\Support\QrCodeGenerator::renderDataUri($entity->qr_code ?? $entity->nisn, 200);
        @endphp
        <div class="element" style="left: {{ $elements['qr']['x'] }}pt; top: {{ $elements['qr']['y'] }}pt;">
            <img class="qr" src="{{ $qrData }}" style="width: {{ $elements['qr']['w'] }}pt; height: {{ $elements['qr']['h'] }}pt;">
        </div>
        @endif
    </div>
    @endforeach
</body>
</html>
