@extends('layouts.teacher')

@section('title', 'Messagerie enseignant')
@section('page_title', 'Messagerie')
@section('page_subtitle', 'Espace de messagerie enseignant stabilisé.')

@section('content')
<div style="display:grid;gap:18px">
    <section style="background:linear-gradient(135deg,#0f172a,#1d4ed8);color:white;border-radius:24px;padding:24px;box-shadow:0 18px 40px rgba(15,23,42,.18)">
        <h2 style="margin:0 0 8px;font-size:2rem">Messagerie enseignant</h2>
        <p style="margin:0;color:#dbeafe;max-width:760px">La messagerie est ouverte en mode stable. Les conversations avancées seront réactivées progressivement après vérification des données réelles.</p>
    </section>

    <section style="background:white;border:1px solid #e2e8f0;border-radius:20px;padding:22px;box-shadow:0 12px 28px rgba(15,23,42,.06)">
        <h3 style="margin:0 0 10px;color:#0f172a">État du module</h3>
        <p style="color:#64748b;margin:0 0 16px">Cette page confirme que l’accès enseignant à la messagerie fonctionne sans erreur 500. La prochaine étape consiste à réafficher les élèves affectés puis les messages, un bloc à la fois.</p>
        <a href="{{ route('teacher.dashboard') }}" style="display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 15px;border-radius:13px;background:#0f2a69;color:white;text-decoration:none;font-weight:900">← Retour au tableau de bord</a>
    </section>
</div>
@endsection
