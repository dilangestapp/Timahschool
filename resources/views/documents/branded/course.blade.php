<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brand['brand_name'] }} - {{ $brand['title'] }}</title>
    <style>
        :root { --blue:#1a237e; --blue2:#2563eb; --ink:#101828; --muted:#60758d; --line:#dbe5f4; --soft:#f0f4ff; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Inter, Segoe UI, Arial, sans-serif; background:var(--soft); color:var(--ink); }
        .page { width:min(1120px, calc(100vw - 28px)); margin:22px auto; background:#fff; border:1px solid var(--line); border-radius:22px; overflow:hidden; box-shadow:0 20px 50px rgba(26,35,126,.10); position:relative; }
        .watermark { position:absolute; inset:35% 0 auto 0; text-align:center; font-size:76px; font-weight:900; color:rgba(26,35,126,.035); transform:rotate(-18deg); letter-spacing:.08em; pointer-events:none; }
        .top { background:linear-gradient(135deg,var(--blue),#173b74 58%,#0f766e); color:#fff; padding:24px 28px; display:flex; justify-content:space-between; gap:20px; align-items:center; position:relative; }
        .brand { display:flex; gap:14px; align-items:center; }
        .logo { width:62px; height:62px; border-radius:18px; background:#fff; color:var(--blue); display:grid; place-items:center; font-weight:900; font-size:24px; box-shadow:0 10px 24px rgba(0,0,0,.14); }
        .brand h1 { margin:0; font-size:24px; letter-spacing:-.03em; }
        .brand p { margin:4px 0 0; opacity:.9; }
        .qr { background:#fff; color:var(--ink); border-radius:18px; padding:10px; display:flex; gap:10px; align-items:center; min-width:244px; }
        .qr img { width:82px; height:82px; border-radius:10px; }
        .qr strong { color:var(--blue); display:block; font-size:13px; }
        .qr span { display:block; color:var(--muted); font-size:11px; line-height:1.35; word-break:break-all; }
        .doc-head { padding:26px 28px 18px; border-bottom:1px solid var(--line); position:relative; }
        .doc-head h2 { margin:0 0 8px; font-size:34px; line-height:1.1; color:var(--blue); letter-spacing:-.04em; }
        .doc-head .meta { color:var(--muted); line-height:1.55; font-weight:700; }
        .content { padding:24px 28px 30px; position:relative; }
        .block { margin:0 0 22px; }
        .block h3 { color:var(--blue); margin:0 0 8px; font-size:18px; }
        .rich { line-height:1.72; font-size:16px; }
        .rich img { max-width:100%; height:auto; }
        .document-box { border:1px solid var(--line); background:#f8fbff; border-radius:18px; padding:16px; margin-top:16px; }
        .document-box strong { color:var(--blue); }
        .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:12px; }
        .btn { border:0; border-radius:999px; padding:10px 14px; font-weight:900; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .btn-primary { background:var(--blue); color:#fff; }
        .btn-ghost { background:#fff; color:var(--blue); border:1px solid var(--line); }
        iframe { width:100%; min-height:620px; border:1px solid var(--line); border-radius:16px; background:#fff; }
        .foot { border-top:1px solid var(--line); padding:14px 28px; color:var(--muted); font-weight:700; font-size:13px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; position:relative; }
        @media print { body{background:#fff}.page{box-shadow:none;width:100%;margin:0;border:0;border-radius:0}.actions{display:none}.top{print-color-adjust:exact;-webkit-print-color-adjust:exact} iframe{min-height:500px}.watermark{display:block} }
        @media(max-width:760px){.top{display:block}.qr{margin-top:14px}.doc-head h2{font-size:26px}.content,.doc-head,.top,.foot{padding-left:18px;padding-right:18px}}
    </style>
</head>
<body>
<div class="page">
    <div class="watermark">{{ $brand['brand_name'] }}</div>
    <header class="top">
        <div class="brand">
            <div class="logo">{{ $brand['brand_initials'] }}</div>
            <div><h1>{{ $brand['brand_name'] }}</h1><p>{{ $brand['slogan'] }} · {{ $brand['signature'] }}</p></div>
        </div>
        <div class="qr"><img src="{{ $brand['qr_url'] }}" alt="QR Code TIMAH ACADEMY"><div><strong>Scanner pour accéder à TIMAH ACADEMY</strong><span>{{ $brand['site'] }}</span><span>{{ $brand['public_url'] }}</span></div></div>
    </header>

    <section class="doc-head">
        <h2>{{ $brand['title'] }}</h2>
        <div class="meta">{{ $brand['subtitle'] }} @if($brand['author']) · Enseignant : {{ $brand['author'] }} @endif @if($brand['published_at']) · Publié le {{ $brand['published_at']->format('d/m/Y') }} @endif</div>
    </section>

    <main class="content">
        @if($brand['description'])<section class="block"><h3>Résumé</h3><div class="rich">{{ $brand['description'] }}</div></section>@endif
        @if($brand['objectives'])<section class="block"><h3>Objectifs pédagogiques</h3><div class="rich">{!! nl2br(e($brand['objectives'])) !!}</div></section>@endif
        @if($brand['content_html'])<section class="block"><h3>Contenu</h3><div class="rich">{!! $brand['content_html'] !!}</div></section>@endif
        @if($brand['document_name'])
            <section class="document-box">
                <strong>Document joint : {{ $brand['document_name'] }}</strong>
                <p>{{ $brand['document_size'] ?: 'Fichier attaché' }}. La version publique est présentée avec l’identité TIMAH ACADEMY.</p>
                <div class="actions"><a href="{{ $embedUrl }}" target="_blank" class="btn btn-primary">Ouvrir le fichier joint</a><a href="{{ route('documents.course.download', $course) }}" class="btn btn-ghost">Télécharger la version officielle</a><button onclick="window.print()" class="btn btn-ghost">Imprimer / PDF</button></div>
                @if($embedUrl && str_contains((string) $brand['document_mime'], 'pdf'))<iframe src="{{ $embedUrl }}"></iframe>@endif
            </section>
        @else
            <div class="actions"><a href="{{ route('documents.course.download', $course) }}" class="btn btn-primary">Télécharger la version officielle</a><button onclick="window.print()" class="btn btn-ghost">Imprimer / PDF</button></div>
        @endif
    </main>
    <footer class="foot"><span>{{ $brand['brand_name'] }} — {{ $brand['slogan'] }}</span><span>{{ $brand['signature'] }} · {{ $brand['site'] }}</span></footer>
</div>
</body>
</html>
