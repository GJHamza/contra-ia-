<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 14px; }
    </style>
</head>
<body>
    <h1>{{ $document->title }}</h1>
    <p><strong>Type :</strong> {{ $document->type }}</p>
    <p><strong>Contenu :</strong></p>
    <div>{!! nl2br(e($document->content)) !!}</div>
</body>
</html>
