<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        h1, h2, h3 { text-align: center; margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 16px; margin-top: 24px; margin-bottom: 8px; }
        ul, ol { margin-left: 30px; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <h1>{{ $document->title }}</h1>
    <hr>
    <div style="margin: 5px 20px;">
        {!! $generated_text ?? '' !!}
    </div>
    <div class="footer">
        <!-- Pagination ou autre info pied de page -->
        Document généré automatiquement - {{ date('d/m/Y') }}
    </div>
</body>
</html>
