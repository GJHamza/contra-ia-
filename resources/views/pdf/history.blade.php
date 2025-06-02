<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historique des textes générés</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .item { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Historique des textes générés</h2>
    @foreach ($texts as $text)
        <div class="item">
            <strong>Prompt :</strong>
            <p>{{ $text->prompt }}</p>
            <strong>Réponse :</strong>
            <p>{{ $text->response }}</p>
            <em>Créé le : {{ $text->created_at }}</em>
        </div>
    @endforeach
</body>
</html>
