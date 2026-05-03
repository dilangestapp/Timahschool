@extends('layouts.admin')

@section('title', 'Babillard numérique')
@section('page_title', 'Babillard numérique')
@section('page_subtitle', 'Publiez les annonces, rappels, évaluations et rapports TIMAH ACADEMY sans frais externes.')

@section('content')
<div class="admin-compact-page">
    @if($tableMissing)
        <div class="admin-empty-box">La table <strong>digital_board_posts</strong> est introuvable. Lancez les migrations Railway pour activer ce module.</div>
    @else
        <div class="admin-summary-strip">
            <div class="admin-summary-card"><strong>{{ $items->count() }}</strong><span>publications</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('status', 'published')->count() }}</strong><span>publiées</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('type', 'report')->count() }}</strong><span>rapports</span></div>
            <div class="admin-summary-card"><strong>{{ $items->where('audience', 'parent')->count() }}</strong><span>parents</span></div>
        </div>

        <details class="admin-collapse-box" open>
            <summary>Nouvelle publication</summary>
            <div class="admin-collapse-box__body">
                <form method="POST" action="{{ route('admin.digital-board.store') }}" class="admin-form">
                    @csrf
                    <div class="admin-form-grid">
                        <div class="form-group">
                            <label>Titre</label>
                            <input type="text" name="title" required placeholder="Ex: Rapport bimensuel disponible">
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" required>
                                <option value="announcement">Annonce</option>
                                <option value="report">Rapport</option>
                                <option value="evaluation">Évaluation</option>
                                <option value="subscription">Abonnement</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Public</label>
                            <select name="audience" required>
                                <option value="all">Tout le monde</option>
                                <option value="student">Apprenants</option>
                                <option value="parent">Parents</option>
                                <option value="teacher">Enseignants</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Classe concernée</label>
                            <select name="school_class_id">
                                <option value="">Toutes les classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Statut</label>
                            <select name="status" required>
                                <option value="published">Publier maintenant</option>
                                <option value="draft">Brouillon</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Expiration facultative</label>
                            <input type="datetime-local" name="expires_at">
                        </div>
                        <div class="form-group admin-form-grid__full">
                            <label>Message</label>
                            <textarea name="content" rows="5" required placeholder="Rédigez le message visible dans l'application..."></textarea>
                        </div>
                    </div>
                    <div class="admin-actions">
                        <button type="submit" class="btn btn--primary">Publier sur le babillard</button>
                    </div>
                </form>
            </div>
        </details>

        <section class="admin-list-panel">
            <div class="admin-list-panel__head">
                <div>
                    <h2>Publications récentes</h2>
                    <p>Le babillard devient le canal officiel gratuit. WhatsApp manuel sert uniquement d’alerte.</p>
                </div>
            </div>

            <div class="admin-clean-list">
                @forelse($items as $post)
                    @php
                        $statusClass = match($post->status) {
                            'published' => 'admin-badge--success',
                            'archived' => 'admin-badge--warning',
                            default => 'admin-badge--trial',
                        };
                    @endphp
                    <article class="admin-clean-row">
                        <div class="admin-clean-title">
                            <strong>{{ $post->title }}</strong>
                            <span>{{ $post->schoolClass?->name ?? 'Toutes les classes' }} · {{ $post->audience }} · {{ $post->type }}</span>
                            <p style="margin:.65rem 0 0;color:var(--muted);line-height:1.55;">{{ \Illuminate\Support\Str::limit($post->content, 220) }}</p>
                        </div>
                        <div class="admin-clean-meta">
                            <span class="admin-badge {{ $statusClass }}">{{ $post->status }}</span><br>
                            <small>{{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : 'Non publié' }}</small>
                        </div>
                        <div class="admin-row-actions">
                            @if($post->status !== 'published')
                                <form method="POST" action="{{ route('admin.digital-board.publish', $post) }}">
                                    @csrf
                                    <button type="submit" class="btn btn--primary">Publier</button>
                                </form>
                            @endif
                            @if($post->status !== 'archived')
                                <form method="POST" action="{{ route('admin.digital-board.archive', $post) }}">
                                    @csrf
                                    <button type="submit" class="btn btn--ghost">Archiver</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.digital-board.delete', $post) }}" onsubmit="return confirm('Supprimer cette publication ?');">
                                @csrf
                                <button type="submit" class="btn btn--ghost admin-btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="admin-empty-box">Aucune publication pour le moment.</div>
                @endforelse
            </div>
        </section>
    @endif
</div>
@endsection
