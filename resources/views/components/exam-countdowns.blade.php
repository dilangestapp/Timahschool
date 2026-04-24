@php
    $countdowns = collect($examCountdowns ?? [])->filter()->values();
    $compact = (bool) ($compact ?? false);
@endphp

@if($countdowns->isNotEmpty())
    <style>
        .exam-countdown-zone{padding:34px 0;position:relative;z-index:2}.exam-countdown-zone--compact{padding:0}.exam-countdown-container{display:grid;gap:18px;width:min(var(--container,1240px),calc(100% - 32px));margin:0 auto}.exam-countdown-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-end;flex-wrap:wrap}.exam-countdown-head h2{margin:8px 0 6px;font-size:clamp(1.6rem,3vw,2.4rem);line-height:1.05;letter-spacing:-.05em;color:var(--text,#0f172a)}.exam-countdown-head p{margin:0;color:var(--muted,#64748b);max-width:760px;line-height:1.65}.exam-eyebrow{display:inline-flex;align-items:center;min-height:32px;padding:0 12px;border-radius:999px;background:rgba(15,118,110,.10);color:#115e59;font-size:.78rem;font-weight:950;text-transform:uppercase;letter-spacing:.06em}.exam-countdown-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.exam-countdown-grid--compact{grid-template-columns:1fr}.exam-card{position:relative;overflow:hidden;border-radius:26px;border:1px solid rgba(148,163,184,.24);background:linear-gradient(180deg,var(--panel,#fff),var(--panel-soft,#f8fafc));box-shadow:0 18px 42px rgba(15,23,42,.08);padding:18px;display:grid;gap:12px}.exam-card:before{content:"";position:absolute;right:-52px;top:-52px;width:135px;height:135px;border-radius:999px;background:rgba(37,99,235,.13)}.exam-card--green:before{background:rgba(15,118,110,.15)}.exam-card--orange:before{background:rgba(245,158,11,.18)}.exam-card--purple:before{background:rgba(124,58,237,.14)}.exam-card>*{position:relative;z-index:1}.exam-card__top{display:flex;justify-content:space-between;gap:10px;align-items:center}.exam-badge,.exam-status{display:inline-flex;align-items:center;min-height:28px;padding:0 10px;border-radius:999px;font-size:.72rem;font-weight:950;text-transform:uppercase;letter-spacing:.04em}.exam-badge{background:rgba(15,118,110,.10);color:#115e59}.exam-status{background:rgba(37,99,235,.10);color:#1d4ed8}.exam-card h3{margin:0;font-size:1.12rem;line-height:1.18;letter-spacing:-.035em;color:var(--text,#0f172a)}.exam-card p{margin:0;color:var(--muted,#64748b);font-size:.9rem;line-height:1.45}.exam-date-line{font-size:.86rem;color:var(--muted,#64748b)}.exam-date-line strong{color:var(--text,#0f172a)}.exam-timer{border-radius:20px;background:rgba(15,118,110,.07);border:1px solid rgba(15,118,110,.12);padding:13px}.exam-timer-title{font-weight:900;color:#115e59;font-size:.82rem;margin-bottom:10px}.exam-timer-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.exam-timer-grid div{border-radius:15px;background:rgba(255,255,255,.82);padding:10px;text-align:center}.exam-timer-grid strong{display:block;font-size:1.35rem;line-height:1;color:var(--text,#0f172a);letter-spacing:-.05em}.exam-timer-grid span{font-size:.72rem;color:var(--muted,#64748b);font-weight:850}.exam-progress{height:9px;border-radius:999px;background:rgba(15,118,110,.10);overflow:hidden}.exam-progress span{display:block;height:100%;border-radius:999px;background:linear-gradient(90deg,#0f766e,#22c55e)}.exam-card small{color:var(--muted,#64748b);line-height:1.45}.exam-countdown-zone--compact .exam-countdown-container{width:100%}.exam-countdown-zone--compact .exam-card{padding:20px}.exam-countdown-zone--compact .exam-countdown-head{display:none}html[data-theme='dark'] .exam-card{background:linear-gradient(180deg,rgba(15,23,42,.88),rgba(15,23,42,.70))}html[data-theme='dark'] .exam-eyebrow{background:rgba(45,212,191,.14);color:#99f6e4}html[data-theme='dark'] .exam-timer-grid div{background:rgba(2,6,23,.42)}html[data-theme='dark'] .exam-badge{background:rgba(45,212,191,.14);color:#99f6e4}html[data-theme='dark'] .exam-status{background:rgba(96,165,250,.14);color:#bfdbfe}@media(max-width:1100px){.exam-countdown-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.exam-countdown-grid--compact{grid-template-columns:1fr}}@media(max-width:720px){.exam-countdown-grid{grid-template-columns:1fr}.exam-countdown-zone{padding:22px 0}.exam-card{border-radius:22px}.exam-timer-grid strong{font-size:1.2rem}}
    </style>

    <section class="exam-countdown-zone {{ $compact ? 'exam-countdown-zone--compact' : '' }}" id="exam-countdowns">
        <div class="exam-countdown-container">
            <div class="exam-countdown-head">
                <div>
                    <span class="exam-eyebrow">Calendrier officiel 2026</span>
                    <h2>{{ $compact ? 'Compte à rebours examen' : 'Comptes à rebours des examens officiels' }}</h2>
                    <p>{{ $compact ? 'Votre échéance principale selon votre classe.' : 'Suivez les dates clés des examens officiels de l’enseignement général et préparez vos révisions sans attendre.' }}</p>
                </div>
            </div>

            <div class="exam-countdown-grid {{ $compact ? 'exam-countdown-grid--compact' : '' }}">
                @foreach($countdowns as $exam)
                    <article class="exam-card exam-card--{{ $exam['color'] ?? 'blue' }}" data-exam-card data-target="{{ $exam['target_iso'] }}">
                        <div class="exam-card__top">
                            <span class="exam-badge">{{ $exam['badge'] ?? 'Examen' }}</span>
                            <span class="exam-status">{{ $exam['status'] === 'running' ? 'En cours' : ($exam['status'] === 'finished' ? 'Terminé' : 'À venir') }}</span>
                        </div>
                        <h3>{{ $exam['label'] }}</h3>
                        <p>{{ $exam['audience'] }}</p>
                        <div class="exam-date-line"><strong>Début :</strong> {{ $exam['start_label'] }}</div>
                        <div class="exam-date-line"><strong>Fin :</strong> {{ $exam['end_label'] }}</div>
                        <div class="exam-timer">
                            <div class="exam-timer-title" data-headline>{{ $exam['headline'] }}</div>
                            <div class="exam-timer-grid">
                                <div><strong data-days>{{ $exam['days'] }}</strong><span>jours</span></div>
                                <div><strong data-hours>{{ $exam['hours'] }}</strong><span>heures</span></div>
                                <div><strong data-minutes>{{ $exam['minutes'] }}</strong><span>min</span></div>
                            </div>
                        </div>
                        <div class="exam-progress"><span style="width: {{ $exam['progress'] }}%"></span></div>
                        <small>{{ $exam['notes'] }}</small>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function updateExamCards() {
                document.querySelectorAll('[data-exam-card]').forEach(function (card) {
                    var target = new Date(card.dataset.target);
                    if (isNaN(target.getTime())) return;
                    var diff = Math.max(0, Math.floor((target.getTime() - Date.now()) / 1000));
                    var days = Math.floor(diff / 86400);
                    var hours = Math.floor((diff % 86400) / 3600);
                    var minutes = Math.floor((diff % 3600) / 60);
                    var d = card.querySelector('[data-days]');
                    var h = card.querySelector('[data-hours]');
                    var m = card.querySelector('[data-minutes]');
                    if (d) d.textContent = days;
                    if (h) h.textContent = hours;
                    if (m) m.textContent = minutes;
                });
            }
            updateExamCards();
            setInterval(updateExamCards, 60000);
        });
    </script>
@endif
