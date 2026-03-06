<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; position: relative; }
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 72px; color: rgba(0,0,0,0.06); white-space: nowrap; z-index: 0; }
        .content { position: relative; z-index: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        h1 { font-size: 18px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="watermark">{{ $watermarkText ?? 'BIOTIME' }}</div>
    <div class="content">
        @yield('content')
    </div>
</body>
</html>
