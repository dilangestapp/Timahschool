<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace parent - TIMAH ACADEMY</title>
    <style>
        :root { --primary:#2563eb; --dark:#0f172a; --muted:#64748b; --bg:#f6f8fc; --border:#dbe6f3; --green:#059669; --red:#dc2626; --amber:#d97706; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Inter, Segoe UI, Arial, sans-serif; background:var(--bg); color:var(--dark); }
        a { color: inherit; text-decoration: none; }
        .parent-shell { max-width: 1180px; margin: 0 auto; padding: 18px; }
        .parent-top { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:18px; }
        .brand { display:flex; align-items:center; gap:12px; font-weight:900; }
        .brand-mark { width:46px; height:46px; border-radius:16px; display:grid; place-items:center; color:#fff; background:linear-gradient(135deg,#2563eb,#7c3aed); }
        .logout { min-height:42px; border:1px solid var(--border); border-radius:14px; background:#fff; padding:0 14px; font-weight:800; }
        .hero { overflow:hidden; border-radius:28px; padding:24px; color:#fff; background:linear-gradient(135deg,#0f172a,#1d4ed8,#7c3aed); box-shadow:0 20px 46px rgba(15,23,42,.16); margin-bottom:18px; }
        .hero h1 { margin:8px 0; font-size:clamp(1.8rem,5vw,3rem); line-height:1; }
        .hero p { margin:0; color:#dbeafe; line-height:1.5; max-width:760px; }
        .grid { display:grid; gap:14px; }
        .stats { grid-template-columns: repeat(5,1fr); margin-bottom:18px; }
        .card { background:#fff; border:1px solid var(--border); border-radius:22px; padding:16px; box-shadow:0 10px 24px rgba(15,23,42,.05); }
        .stat span { display:block; color:var(--muted); font-weight:800; font-size:.86rem; }
        .stat strong { display:block; font-size:2rem; margin-top:6px; }
        .main { grid-template-columns: minmax(0,1.1fr) minmax(320px,.9fr); }
        .section-title { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:12px; }
        .section-title h2 { margin:0; font-size:1.2rem; }
        .section-title p { margin:4px 0 0; color:var(--muted); }
        .child-card { margin-bottom:12px; }
        .child-head { display:flex; justify-content:space-between; gap:12px; align-items:center; margin-bottom:12px; }
        .child-head strong { font-size:1.1rem; }
        .muted { color:var(--muted); }
        .badge { display:inline-flex; align-items:center; min-height:30px; padding:0 10px; border-radius:999px; font-weight:900; font-size:.78rem; background:#eff6ff; color:#1d4ed8; }
        .badge.done { background:#dcfce7; color:#166534; }
        .badge.late { background:#fee2e2; color:#991b1b; }
        .progress-item { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; padding:12px; border:1px solid #e2e8f0; border-radius:16px; margin-top:8px; }
        .progress-item strong { display:block; }
        .progress-item p { margin:4px 0 0; color:var(--muted); font-size:.92rem; }
        .notif { display:block; padding:13px; border:1px solid #e2e8f0; border-radius:16px; margin-top:8px; }
        .notif strong { display:block; }
        .notif p { margin:5px 0 0; color:var(--muted); line-height:1.35; }
        .empty { padding:20px; text-align:center; color:var(--muted); border:1px dashed var(--border); border-radius:18px; background:#fff; }
        @media(max-width:900px){ .stats,.main{grid-template-columns:1fr 1fr;} .main{grid-template-columns:1fr;} }
        @media(max-width:600px){ .parent-shell{padding:12px;} .stats{grid-template-columns:1fr 1fr;} .parent-top{align-items:flex-start;} .progress-item{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<div class="parent-shell">
    <header class="parent-top">
        <div class="brand"><span class="brand-mark">TA</span><span>TIMAH ACADEMY<br><small class="muted">Espace parent</small></span></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Déconnexion</button></form>
    </header>

    <section class="hero">
        <span class="badge">Suivi familial</span>
        <h1>Bonjour {{ $parentUser->full_name ?? $parentUser->name ?? $parentUser->username }}</h1>
        <p>Suivez les cours publiés, les cours consultés et les notifications liées au travail scolaire de votre enfant.</p>
    </section>

    <section class="grid stats">
        <article class="card stat"><span>Enfants</span><strong>{{ $stats['children'] }}</strong></article>
        <article class="card stat"><span>Cours suivis</span><strong>{{ $stats['courses_total'] }}</strong></article>
        <article class="card stat"><span>Terminés</span><strong>{{ $stats['courses_completed'] }}</strong></article>
        <article class="card stat"><span>Non lus</span><strong>{{ $stats['courses_not_started'] }}</strong></article>
        <article class="card stat"><span>Notifications</span><strong>{{ $stats['notifications'] }}</strong></article>
    </section>

    <main class="grid main">
        <section class="card">
            <div class="section-title"><div><h2>Mes enfants</h2><p>Suivi des cours par enfant.</p></div></div>
            @forelse($children as $child)
                @php $items = $courseProgress->get($child->id, collect()); @endphp
                <article class="child-card">
                    <div class="child-head">
                        <div><strong>{{ $child->full_name ?? $child->name ?? $child->username }}</strong><div class="muted">{{ $child->studentProfile->schoolClass->name ?? 'Classe non renseignée' }}</div></div>
                        <span class="badge">{{ $items->count() }} cours</span>
                    </div>
                    @forelse($items->take(8) as $progress)
                        <div class="progress-item">
                            <div><strong>{{ $progress->course_title }}</strong><p>{{ $progress->subject_name ?? 'Matière' }} · publié {{ $progress->published_at ? \Carbon\Carbon::parse($progress->published_at)->format('d/m/Y') : '-' }}</p></div>
                            <span class="badge {{ $progress->status === 'completed' ? 'done' : ($progress->status === 'not_started' ? 'late' : '') }}">{{ $progress->status === 'completed' ? 'Terminé' : ($progress->status === 'not_started' ? 'Non lu' : 'Ouvert') }}</span>
                        </div>
                    @empty
                        <div class="empty">Aucun cours suivi pour le moment.</div>
                    @endforelse
                </article>
            @empty
                <div class="empty">Aucun enfant n’est encore rattaché à ce compte parent.</div>
            @endforelse
        </section>

        <section class="card">
            <div class="section-title"><div><h2>Notifications</h2><p>Alertes internes concernant vos enfants.</p></div></div>
            @forelse($notifications as $notification)
                <article class="notif">
                    <strong>{{ $notification->title }}</strong>
                    <p>{{ $notification->message }}</p>
                    <p><small>{{ $notification->published_at ? \Carbon\Carbon::parse($notification->published_at)->format('d/m/Y à H:i') : '' }}</small></p>
                </article>
            @empty
                <div class="empty">Aucune notification pour le moment.</div>
            @endforelse
        </section>
    </main>
</div>
</body>
</html>
