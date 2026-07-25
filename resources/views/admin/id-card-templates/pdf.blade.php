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
        .id-card-wrapper {
            width: 100%;
            height: {{ $config['canvas']['height'] }}pt;
            page-break-after: always;
            clear: both;
        }
        .id-card {
            position: relative;
            float: left;
            width: {{ $config['canvas']['width'] }}pt;
            height: {{ $config['canvas']['height'] }}pt;
            overflow: hidden;
            border-radius: {{ $config['canvas']['border_radius'] ?? 5 }}pt;
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
        }
        .photo {
            border: 1pt solid #ccc;
            object-fit: cover;
        }
        .qr {
            background: #fff;
        }
        .element-divider {
            position: absolute;
            z-index: 9;
        }
    </style>
</head>
<body class="pdf-body">
    @php
        $lembagaData = $lembaga ?? [];
        $hasBack = $hasSideBack ?? (isset($config['back']['elements']) && collect($config['back']['elements'])->contains('show', true));

        // Front background base64
        $bgFrontBase64 = '';
        if ($template->background_path) {
            if (str_starts_with($template->background_path, 'http://') || str_starts_with($template->background_path, 'https://')) {
                $bgFrontBase64 = $template->background_path;
            } elseif (strlen($template->background_path) > 30 && !str_contains($template->background_path, '/')) {
                try {
                    $bgFrontBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($template->background_path);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('IdCardTemplate PDF Front: Gagal load background Drive: ' . $e->getMessage());
                }
            } else if (file_exists(storage_path('app/public/' . $template->background_path))) {
                $bgData = file_get_contents(storage_path('app/public/' . $template->background_path));
                $bgFrontBase64 = 'data:image/' . pathinfo($template->background_path, PATHINFO_EXTENSION) . ';base64,' . base64_encode($bgData);
            }
        }

        // Back background base64
        $bgBackBase64 = '';
        if ($hasBack && !empty($template->background_path_back)) {
            if (str_starts_with($template->background_path_back, 'http://') || str_starts_with($template->background_path_back, 'https://')) {
                $bgBackBase64 = $template->background_path_back;
            } elseif (strlen($template->background_path_back) > 30 && !str_contains($template->background_path_back, '/')) {
                try {
                    $bgBackBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($template->background_path_back);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('IdCardTemplate PDF Back: Gagal load background Drive: ' . $e->getMessage());
                }
            } else if (file_exists(storage_path('app/public/' . $template->background_path_back))) {
                $bgDataBack = file_get_contents(storage_path('app/public/' . $template->background_path_back));
                $bgBackBase64 = 'data:image/' . pathinfo($template->background_path_back, PATHINFO_EXTENSION) . ';base64,' . base64_encode($bgDataBack);
            }
        }

        $elementsFront = $configFront['elements'] ?? ($config['front']['elements'] ?? ($config['elements'] ?? []));
        $elementsBack  = $configBack['elements'] ?? ($config['back']['elements'] ?? null);
    @endphp

    @foreach($entities as $entity)
    <div class="id-card-wrapper">
        
        {{-- SISI FRONT (DEPAN) --}}
        <div class="id-card id-card-front">
            @if($bgFrontBase64)
            <img class="background" src="{{ $bgFrontBase64 }}">
            @endif

            @php $elements = $elementsFront; @endphp
            @include('admin.id-card-templates._elements_render', ['elements' => $elements, 'entity' => $entity, 'lembagaData' => $lembagaData, 'template' => $template])
        </div>

        {{-- SISI BACK (BELAKANG) --}}
        @if($hasBack && $elementsBack)
        <div class="id-card id-card-back">
            @if($bgBackBase64)
            <img class="background" src="{{ $bgBackBase64 }}">
            @endif

            @php $elements = $elementsBack; @endphp
            @include('admin.id-card-templates._elements_render', ['elements' => $elements, 'entity' => $entity, 'lembagaData' => $lembagaData, 'template' => $template])
        </div>
        @endif

    </div>
    @endforeach
</body>
</html>
