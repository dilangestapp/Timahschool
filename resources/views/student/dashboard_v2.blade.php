@extends('layouts.student')

@section('title', 'Tableau de bord')

@php
    $studentName = $user->full_name ?? $user->name ?? $user->username ?? 'Élève';
    $className = $studentProfile->schoolClass->name ?? 'Classe non définie';
    $isSubscriptionActive = $subscription && $subscription->isActive();
    $subscriptionState = $isSubscriptionActive ? 'Actif' : 'Inactif';
    $subscriptionName = $subscription->plan_name ?? 'Aucun abonnement';
    $subscriptionEndsAt = $subscription?->ends_at;
    $maxWeekly = max(1, collect($weeklyActivity ?? [])->max('value') ?? 1);
    $maxSubject = max(1, collect($subjectStats ?? [])->max('count') ?? 1);
    $maxType = max(1, collect($typeStats ?? [])->max('total') ?? 1);
@endphp

@push('styles')
<style>
    .student-pro-dashboard{display:grid;gap:22px;color:var(--text)}
    .student-pro-dashboard .dash-hero{position:relative;overflow:hidden;border-radius:30px;padding:26px;background:linear-gradient(135deg,#0f172a 0%,#12336d 52%,#0f766e 100%);color:#fff;box-shadow:var(--shadow-lg)}
    .student-pro-dashboard .dash-hero:before{content:"";position:absolute;right:-90px;top:-90px;width:260px;height:260px;border-radius:999px;background:rgba(255,255,255,.09)}
    .student-pro-dashboard .dash-hero:after{content:"";position:absolute;left:-70px;bottom:-100px;width:240px;height:240px;border-radius:999px;background:rgba(45,212,191,.16)}
    .student-pro-dashboard .hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:1.2fr .8fr;gap:20px;align-items:stretch}
    .student-pro-dashboard .eyebrow{display:inline-flex;align-items:center;width:max-content;gap:8px;padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.16);font-weight:900;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em}
    .student-pro-dashboard h1{margin:14px 0 10px;font-size:clamp(2rem,4vw,3.8rem);line-height:1;letter-spacing:-.06em;color:#fff}
    .student-pro-dashboard .hero-text{margin:0;color:rgba(255,255,255,.82);line-height:1.7;max-width:68ch}
    .student-pro-dashboard .hero-pills{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}
    .student-pro-dashboard .hero-pill{display:inline-flex;align-items:center;gap:8px;min-height:38px;padding:0 13px;border-radius:999px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.14);font-weight:800;color:#f8fafc}
    .student-pro-dashboard .hero-side{display:grid;gap:14px}
    .student-pro-dashboard .hero-card{border-radius:22px;padding:18px;background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.16);backdrop-filter:blur(10px)}
    .student-pro-dashboard .hero-card small{display:block;color:rgba(255,255,255,.70);font-weight:800;text-transform:uppercase;letter-spacing:.04em;margin-bottom:8px}
    .student-pro-dashboard .hero-card strong{font-size:1.3rem;letter-spacing:-.03em}
    .student-pro-dashboard .hero-card p{margin:8px 0 0;color:rgba(255,255,255,.76);line-height:1.55}
    .student-pro-dashboard .kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .student-pro-dashboard .kpi{padding:18px;border-radius:24px;background:linear-gradient(180deg,var(--panel),var(--panel-soft));border:1px solid var(--line);box-shadow:var(--shadow)}
    .student-pro-dashboard .kpi span{display:block;color:var(--muted);font-size:.78rem;text-transform:uppercase;letter-spacing:.05em;font-weight:900}
    .student-pro-dashboard .kpi strong{display:block;margin-top:10px;font-size:2rem;line-height:1;letter-spacing:-.05em;color:var(--text)}
    .student-pro-dashboard .kpi small{display:block;margin-top:8px;color:var(--muted);line-height:1.4}
    .student-pro-dashboard .grid-2{display:grid;grid-template-columns:1.15fr .85fr;gap:18px;align-items:stretch}
    .student-pro-dashboard .panel{overflow:hidden;border-radius:28px;background:linear-gradient(180deg,var(--panel),var(--panel-soft));border:1px solid var(--line);box-shadow:var(--shadow)}
    .student-pro-dashboard .panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;padding:22px;border-bottom:1px solid var(--line);background:rgba(15,118,110,.035)}
    .student-pro-dashboard .panel-head h2{margin:0;color:var(--text);font-size:1.35rem;letter-spacing:-.04em}
    .student-pro-dashboard .panel-head p{margin:6px 0 0;color:var(--muted);line-height:1.55}
    .student-pro-dashboard .panel-body{padding:22px}
    .student-pro-dashboard .ring-layout{display:grid;grid-template-columns:170px 1fr;gap:18px;align-items:center}
    .student-pro-dashboard .ring{width:154px;height:154px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(#0f766e {{ $progressPercent }}%,rgba(15,118,110,.12) 0);position:relative;margin:auto}
    .student-pro-dashboard .ring:before{content:"";position:absolute;inset:16px;border-radius:50%;background:var(--panel)}
    .student-pro-dashboard .ring-inner{position:relative;text-align:center;display:grid;gap:2px}
    .student-pro-dashboard .ring-inner strong{font-size:2rem;letter-spacing:-.05em;color:var(--text)}
    .student-pro-dashboard .ring-inner span{font-size:.85rem;color:var(--muted);font-weight:800}
    .student-pro-dashboard .bar-list{display:grid;gap:14px}
    .student-pro-dashboard .bar-row{display:grid;gap:8px}
    .student-pro-dashboard .bar-top{display:flex;justify-content:space-between;gap:10px;font-weight:900;color:var(--text)}
    .student-pro-dashboard .bar-top span{color:var(--muted);font-size:.88rem}
    .student-pro-dashboard .track{height:11px;border-radius:999px;background:rgba(15,118,110,.10);overflow:hidden}
    .student-pro-dashboard .fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#0f766e,#22c55e)}
    .student-pro-dashboard .fill-blue{background:linear-gradient(90deg,#2563eb,#06b6d4)}
    .student-pro-dashboard .fill-warn{background:linear-gradient(90deg,#f59e0b,#f97316)}
    .student-pro-dashboard .week-chart{display:flex;align-items:end;gap:12px;min-height:230px;padding-top:8px}
    .student-pro-dashboard .week-col{flex:1;display:grid;gap:9px;justify-items:center;align-items:end}
    .student-pro-dashboard .week-value{font-weight:900;color:var(--text);font-size:.84rem}
    .student-pro-dashboard .week-stick{height:150px;width:100%;max-width:36px;border-radius:999px;background:rgba(15,118,110,.09);display:flex;align-items:end;overflow:hidden}
    .student-pro-dashboard .week-fill{width:100%;border-radius:999px;background:linear-gradient(180deg,#06b6d4,#2563eb);min-height:8px}
    .student-pro-dashboard .week-label{font-size:.78rem;font-weight:900;color:var(--muted);text-transform:capitalize}
    .student-pro-dashboard .list{display:grid;gap:12px}
    .student-pro-dashboard .event-card,.student-pro-dashboard .reminder-card{border-radius:20px;border:1px solid var(--line);background:rgba(255,255,255,.55);padding:16px;display:grid;gap:10px}
    html[data-theme='dark'] .student-pro-dashboard .event-card,html[data-theme='dark'] .student-pro-dashboard .reminder-card{background:rgba(15,23,42,.26)}
    .student-pro-dashboard .item-top{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
    .student-pro-dashboard .item-top strong{color:var(--text);font-size:1rem;letter-spacing:-.02em}
    .student-pro-dashboard .item-meta{display:flex;flex-wrap:wrap;gap:8px;color:var(--muted);font-size:.84rem}
    .student-pro-dashboard .badge{display:inline-flex;align-items:center;min-height:30px;padding:0 11px;border-radius:999px;font-size:.76rem;font-weight:900;border:1px solid transparent;white-space:nowrap}
    .student-pro-dashboard .badge-ok{background:#dcfce7;color:#166534;border-color:#bbf7d0}
    .student-pro-dashboard .badge-info{background:#dbeafe;color:#1d4ed8;border-color:#bfdbfe}
    .student-pro-dashboard .badge-warn{background:#fef3c7;color:#92400e;border-color:#fde68a}
    .student-pro-dashboard .quick-list{display:grid;gap:10px}
    .student-pro-dashboard .quick-row{display:flex;justify-content:space-between;gap:12px;padding:14px 15px;border-radius:18px;border:1px solid var(--line);background:rgba(255,255,255,.52)}
    html[data-theme='dark'] .student-pro-dashboard .quick-row{background:rgba(15,23,42,.22)}
    .student-pro-dashboard .empty{padding:20px;border-radius:20px;border:1px dashed var(--line);color:var(--muted);text-align:center;line-height:1.6;background:rgba(255,255,255,.38)}
    .student-pro-dashboard .action-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    .student-pro-dashboard .action-card{display:flex;justify-content:space-between;gap:14px;align-items:center;border-radius:24px;padding:20px;border:1px solid var(--line);background:linear-gradient(180deg,var(--panel),var(--panel-soft));box-shadow:var(--shadow)}
    .student-pro-dashboard .action-card h3{margin:0 0 6px;color:var(--text);letter-spacing:-.03em}
    .student-pro-dashboard .action-card p{margin:0;color:var(--muted);line-height:1.5}
    .student-pro-dashboard .action-card a{font-weight:900;color:#0f766e;margin-top:10px;display:inline-flex}
    .student-pro-dashboard .action-icon{width:52px;height:52px;border-radius:18px;display:grid;place-items:center;background:rgba(15,118,110,.10);font-size:1.3rem;flex:0 0 52px}
    @media(max-width:1100px){.student-pro-dashboard .hero-grid,.student-pro-dashboard .grid-2,.student-pro-dashboard .kpi-grid,.student-pro-dashboard .action-grid{grid-template-columns:1fr}.student-pro-dashboard .ring-layout{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="student-pro-dashboard">
    <section class="dash-hero">
        <div class="hero-grid">
            <div>
                <span class="eyebrow">Tableau de bord intelligent</span>
                <h1>Bonjour {{ $studentName }}</h1>
                <p class="hero-text">Votre tableau de bord affiche maintenant des statistiques réelles : contenus disponibles, TD ouverts, progression, rappels et dernières publications de votre classe.</p>
                <div class="hero-pills">
                    <span class="hero-pill">🎓 {{ $className }}</span>
                    <span class="hero-pill">📚 {{ $totalResources }} ressource(s)</span>
                    <span class="hero-pill">✅ {{ $progressPercent }}% progression</span>
                </div>
            </div>
            <div class="hero-side">
                <div class="hero-card">
                    <small>Abonnement</small>
                    <strong>{{ $subscriptionName }}</strong>
                    <p>{{ $subscriptionState }} @if($subscriptionEndsAt) · expire le {{ $subscriptionEndsAt->format('d/m/Y à H:i') }} @endif</p>
                </div>
                <div class="hero-card">
                    <small>Priorité actuelle</small>
                    <strong>{{ $pendingCount }} élément(s) à consulter</strong>
                    <p>Les rappels ci-dessous signalent les publications non encore ouvertes.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="kpi-grid">
        <div class="kpi"><span>TD disponibles</span><strong>{{ $allTdCount }}</strong><small>{{ $tdOpenedCount }} ouvert(s), {{ $tdCompletedCount }} terminé(s)</small></div>
        <div class="kpi"><span>Cours disponibles</span><strong>{{ $allCoursesCount }}</strong><small>Publications de votre classe</small></div>
        <div class="kpi"><span>Quiz à faire</span><strong>{{ $pendingQuizzes->count() }}</strong><small>Évaluations non encore traitées</small></div>
        <div class="kpi"><span>Rappels</span><strong>{{ $pendingCount }}</strong><small>Événements non consultés</small></div>
    </section>

    <section class="grid-2">
        <article class="panel">
            <div class="panel-head"><div><h2>Progression réelle</h2><p>Calculée à partir des ressources disponibles et des TD déjà ouverts.</p></div><span class="badge badge-info">{{ $consultedResources }}/{{ $totalResources }}</span></div>
            <div class="panel-body ring-layout">
                <div class="ring"><div class="ring-inner"><strong>{{ $progressPercent }}%</strong><span>progression</span></div></div>
                <div class="bar-list">
                    @foreach($typeStats as $type)
                        @php $width = $maxType > 0 ? max(4, round(($type['total'] / $maxType) * 100)) : 0; @endphp
                        <div class="bar-row">
                            <div class="bar-top"><strong>{{ $type['label'] }}</strong><span>{{ $type['total'] }} total · {{ $type['pending'] }} à voir</span></div>
                            <div class="track"><div class="fill {{ $type['pending'] > 0 ? 'fill-warn' : '' }}" style="width: {{ $width }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <article class="panel">
            <div class="panel-head"><div><h2>Activité sur 7 jours</h2><p>Ouvertures de TD enregistrées cette semaine.</p></div></div>
            <div class="panel-body">
                <div class="week-chart">
                    @foreach($weeklyActivity as $day)
                        @php $height = max(6, round(($day['value'] / $maxWeekly) * 100)); @endphp
                        <div class="week-col">
                            <div class="week-value">{{ $day['value'] }}</div>
                            <div class="week-stick"><div class="week-fill" style="height: {{ $height }}%"></div></div>
                            <div class="week-label">{{ $day['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>
    </section>

    <section class="grid-2">
        <article class="panel">
            <div class="panel-head"><div><h2>Rappels importants</h2><p>Derniers TD publiés que vous n’avez pas encore consultés.</p></div><span class="badge badge-warn">À rattraper</span></div>
            <div class="panel-body">
                @if($pendingReminders->isEmpty())
                    <div class="empty">Aucun rappel urgent. Vous êtes à jour sur les TD détectés.</div>
                @else
                    <div class="list">
                        @foreach($pendingReminders as $item)
                            <div class="reminder-card">
                                <div class="item-top"><strong><a href="{{ $item['route'] }}">{{ $item['title'] }}</a></strong><span class="badge badge-warn">{{ $item['priority'] }}</span></div>
                                <div class="item-meta"><span>{{ $item['type'] }}</span><span>{{ $item['subject'] }}</span><span>{{ optional($item['date'])->diffForHumans() }}</span></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>

        <article class="panel">
            <div class="panel-head"><div><h2>Répartition par matière</h2><p>Volume réel des TD et cours disponibles.</p></div></div>
            <div class="panel-body">
                @if($subjectStats->isEmpty())
                    <div class="empty">Aucune statistique matière disponible pour votre classe.</div>
                @else
                    <div class="bar-list">
                        @foreach($subjectStats as $stat)
                            @php $width = max(5, round(($stat['count'] / $maxSubject) * 100)); @endphp
                            <div class="bar-row">
                                <div class="bar-top"><strong>{{ $stat['name'] }}</strong><span>{{ $stat['count'] }} contenu(s)</span></div>
                                <div class="track"><div class="fill fill-blue" style="width: {{ $width }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section class="grid-2">
        <article class="panel">
            <div class="panel-head"><div><h2>Derniers événements de la classe</h2><p>Publications récentes classées par date.</p></div><span class="badge badge-info">Actualités</span></div>
            <div class="panel-body">
                @if($latestEvents->isEmpty())
                    <div class="empty">Aucun événement récent pour le moment.</div>
                @else
                    <div class="list">
                        @foreach($latestEvents as $event)
                            <div class="event-card">
                                <div class="item-top"><strong><a href="{{ $event['route'] }}">{{ $event['title'] }}</a></strong><span class="badge badge-info">{{ $event['type'] }}</span></div>
                                <div class="item-meta"><span>{{ $event['subject'] }}</span><span>{{ $event['access'] }}</span><span>{{ optional($event['date'])->diffForHumans() }}</span></div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </article>

        <article class="panel">
            <div class="panel-head"><div><h2>Repères rapides</h2><p>Résumé utile de votre situation actuelle.</p></div></div>
            <div class="panel-body">
                <div class="quick-list">
                    <div class="quick-row"><strong>Classe</strong><span>{{ $className }}</span></div>
                    <div class="quick-row"><strong>Abonnement</strong><span>{{ $subscriptionState }}</span></div>
                    <div class="quick-row"><strong>Progression</strong><span>{{ $progressPercent }}%</span></div>
                    <div class="quick-row"><strong>TD ouverts</strong><span>{{ $tdOpenedCount }}/{{ $allTdCount }}</span></div>
                    <div class="quick-row"><strong>Rappels</strong><span>{{ $pendingCount }}</span></div>
                </div>
            </div>
        </article>
    </section>

    <section class="action-grid">
        <div class="action-card">
            <div><h3>Accéder à mes TD</h3><p>Continuez votre travail sans perdre le fil.</p><a href="{{ route('student.td.index') }}">Ouvrir cet espace</a></div>
            <div class="action-icon">📁</div>
        </div>
        <div class="action-card">
            <div><h3>Ouvrir la messagerie</h3><p>Écrivez à votre enseignant ou demandez de l’aide.</p><a href="{{ route('student.messages.create') }}">Écrire maintenant</a></div>
            <div class="action-icon">✉️</div>
        </div>
    </section>
</div>
@endsection
