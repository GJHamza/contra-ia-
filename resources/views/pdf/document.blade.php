<!-- filepath: resources/views/document.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
        h1 { text-align: center; margin-bottom: 24px; }
        h2 { text-align: left; font-size: 18px; font-weight: bold; margin-top: 24px; margin-bottom: 10px; text-decoration: underline; }
        p { margin-bottom: 14px; text-align: justify; }
        ul, ol { margin-left: 30px; }
        .signature-row { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-block { width: 40%; text-align: center; }
        .signature-label { font-weight: bold; }
        .signature-space { border-bottom: 1px solid #888; width: 80%; height: 40px; display: inline-block; margin-bottom: 10px; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 12px; color: #888; }
        .intro { margin-bottom: 20px; }
        .date-section { margin-top: 40px; text-align: center; }
        .signature-section { margin-top: 40px; }
    </style>
</head>
<body>
    <h1>{{ $document->title ?? 'CONTRAT' }}</h1>

    @php
        $content = json_decode($document->content, true);
        $html = $content['html'] ?? '';
        $lieu_signature = $content['fields']['lieu'] ?? null;
        $date_signature = $content['fields']['date'] ?? null;
        $signataires = $content['fields']['signataires'] ?? null;
        // Extraction de l'introduction (avant le premier <h2>)
        $introduction = null;
        $mainHtml = $html;
        if (preg_match('/^(.*?)<h2/i', $html, $matches)) {
            $introduction = $matches[1];
            $mainHtml = substr($html, strlen($matches[1]));
        }
        // Valeurs par défaut si pas de champ signataires
        if (!$signataires) {
            $signataires = ['Signature de la première partie', 'Signature de la seconde partie'];
        }
    @endphp

    @if(isset($introduction) && trim(strip_tags($introduction)))
        <div class="intro">
            {!! $introduction !!}
        </div>
    @endif

    <div style="margin: 0 10px;">
        {!! $mainHtml ?? '' !!}
    </div>

    @if(isset($date_signature) || isset($lieu_signature))
    <div class="date-section">
        Fait à {{ $lieu_signature ?? '...' }}, le {{ $date_signature ?? '...' }}
    </div>
    @endif

    <div class="signature-section">
        @foreach($signataires as $sig)
            <div class="signature-block">
                {{ $sig }}<br><br><br>
                _______________________
            </div>
        @endforeach
    </div>

    <div class="footer">
        Document généré automatiquement - {{ date('d/m/Y') }}
    </div>
</body>
</html>