<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Board Monitoring Guru - WAKA Kurikulum</title>
    
    <!-- Tabler CSS (or Bootstrap 5) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.44.0/tabler-icons.min.css">
    
    <style>
        body {
            background-color: #f4f6fa;
            font-family: 'Inter', sans-serif;
            overflow: hidden; /* Prevent body scroll, handled by grid container */
        }
        /* Custom scrollbar for grid */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
    @livewireStyles
</head>
<body>

    @livewire('live-board-waka')

    @livewireScripts
</body>
</html>