@extends('layouts.teacher')

@section('title', 'Messagerie enseignant')
@section('page_title', 'Messagerie')
@section('page_subtitle', 'Conversations rapides avec vos élèves affectés.')

@section('content')
<script>document.body.classList.add('teacher-messenger-view');</script>
@php
    $threads = collect($threads ?? []);
    $assignments = collect($assignments ?? []);
    $selectedThread = $selectedThread ?? null;
    $hasSelectedThread = (bool) $selectedThread;
@endphp

<style>
    .messenger-page {
        height: calc(100vh - 150px);
        min-height: 620px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .msg-broadcast {
        flex: 0 0 auto;
        background: #fff;
        border: 1px solid #dbeafe;
        border-radius: 18px;
        padding: 12px;
    }

    .msg-broadcast summary { cursor: pointer; font-weight: 950; }
    .msg-broadcast form { display: grid; grid-template-columns: 250px 1fr auto; gap: 8px; margin-top: 10px; }
    .msg-broadcast select,
    .msg-broadcast input { border: 1px solid #dbe3ee; border-radius: 14px; padding: 12px; }
    .msg-broadcast button { border: 0; border-radius: 14px; background: #128c7e; color: #fff; font-weight: 950; padding: 12px 18px; }

    .msg-shell {
        flex: 1 1 auto;
        min-height: 0;
        display: grid;
        grid-template-columns: minmax(280px, 380px) minmax(0, 1fr);
        background: #fff;
        border: 1px solid #dbeafe;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(15, 23, 42, .08);
    }

    .msg-sidebar { min-height: 0; display: flex; flex-direction: column; background: #f8fafc; border-right: 1px solid #e2e8f0; }
    .msg-sidebar-head { flex: 0 0 auto; background: #fff; border-bottom: 1px solid #e2e8f0; padding: 14px; }
    .msg-sidebar-head h3 { margin: 0 0 10px; font-weight: 950; }
    .msg-search { display: flex; gap: 8px; align-items: center; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px; padding: 9px 12px; }
    .msg-search input { border: 0; background: transparent; outline: 0; width: 100%; font-weight: 800; }
    .msg-list { flex: 1 1 auto; min-height: 0; overflow: auto; padding: 10px; }

    .msg-thread {
        display: grid;
        grid-template-columns: 48px minmax(0, 1fr) auto;
        gap: 10px;
        align-items: center;
        padding: 10px;
        border-radius: 18px;
        margin-bottom: 7px;
        text-decoration: none;
        color: #0f172a;
        border: 1px solid transparent;
    }

    .msg-thread:hover { background: #eef7f2; }
    .msg-thread.active { background: #e7f8ef; border-color: #9be7b3; }
    .msg-avatar { width: 48px; height: 48px; border-radius: 50%; display: grid; place-items: center; background: linear-gradient(135deg, #128c7e, #25d366); color: #fff; font-weight: 950; flex-shrink: 0; }
    .msg-name { font-weight: 950; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msg-sub { font-size: .86rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msg-time { font-size: .76rem; color: #64748b; text-align: right; font-weight: 800; }
    .msg-badge { display: inline-grid; place-items: center; min-width: 22px; height: 22px; border-radius: 999px; background: #25d366; color: #052e2b; font-size: .72rem; font-weight: 950; margin-top: 5px; }

    .msg-chat { height: 100%; min-height: 0; display: flex; flex-direction: column; background-color: #efeae2; background-image: radial-gradient(rgba(18, 140, 126, .08) 1px, transparent 1px); background-size: 18px 18px; }
    .msg-chat-head { flex: 0 0 auto; display: flex; align-items: center; gap: 12px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 10px 14px; }
    .msg-chat-head h3 { margin: 0; font-size: 1.05rem; font-weight: 950; }
    .msg-chat-head small { display: block; color: #64748b; font-weight: 750; }
    .msg-back-list { border: 0; border-radius: 12px; background: #e2e8f0; color: #0f172a; font-weight: 950; padding: 9px 11px; cursor: pointer; }
    .msg-close { margin-left: auto; border: 0; border-radius: 12px; background: #e2e8f0; color: #0f172a; font-weight: 950; padding: 9px 11px; cursor: pointer; text-decoration: none; }

    .msg-messages { flex: 1 1 auto; min-height: 0; overflow: auto; padding: 14px 18px; display: flex; flex-direction: column; gap: 8px; overscroll-behavior: contain; }
    .msg-day { align-self: center; background: rgba(255, 255, 255, .84); border: 1px solid rgba(148, 163, 184, .35); border-radius: 999px; padding: 6px 12px; font-size: .78rem; color: #64748b; font-weight: 900; }
    .msg-wrap { display: flex; flex-direction: column; max-width: min(78%, 700px); align-self: flex-start; }
    .msg-wrap.me { align-self: flex-end; }
    .msg-bubble { background: #fff; border-radius: 18px 18px 18px 4px; padding: 10px 12px; box-shadow: 0 3px 10px rgba(15, 23, 42, .08); border: 1px solid rgba(226, 232, 240, .8); }
    .msg-wrap.me .msg-bubble { background: #d9fdd3; border-color: #bbf7d0; border-radius: 18px 18px 4px 18px; }
    .msg-quote { border-left: 4px solid #128c7e; background: rgba(18, 140, 126, .08); border-radius: 10px; padding: 8px; margin-bottom: 8px; color: #475569; font-size: .86rem; }
    .msg-text { white-space: pre-wrap; line-height: 1.45; color: #0f172a; }
    .msg-meta { display: flex; justify-content: flex-end; gap: 6px; color: #64748b; font-size: .72rem; font-weight: 850; margin-top: 6px; }
    .msg-ticks { color: #128c7e; }
    .msg-attach { display: block; text-decoration: none; color: #0f172a; border: 1px solid rgba(148, 163, 184, .35); background: rgba(255, 255, 255, .72); border-radius: 14px; padding: 8px; margin-bottom: 8px; }
    .msg-attach img { display: block; max-width: 260px; max-height: 220px; border-radius: 12px; object-fit: cover; }
    .msg-file { display: flex; align-items: center; gap: 10px; }
    .msg-file-icon { width: 42px; height: 42px; border-radius: 12px; background: #fee2e2; color: #dc2626; display: grid; place-items: center; font-weight: 950; }
    .msg-audio { width: 260px; max-width: 100%; }
    .msg-tools { display: flex; gap: 6px; margin-top: 6px; opacity: .76; }
    .msg-tool { border: 0; background: rgba(15, 23, 42, .06); border-radius: 999px; padding: 5px 9px; font-size: .72rem; font-weight: 900; cursor: pointer; color: #334155; }
    .msg-tool.danger { color: #dc2626; }

    .msg-composer { flex: 0 0 auto; background: #f0f2f5; border-top: 1px solid #e2e8f0; padding: 8px; z-index: 5; }
    .msg-reply { display: none; background: #e7f8ef; border-left: 4px solid #128c7e; border-radius: 14px; padding: 9px; margin-bottom: 8px; }
    .msg-reply.show { display: flex; justify-content: space-between; gap: 10px; }
    .msg-form { display: grid; grid-template-columns: 42px 42px minmax(0, 1fr) 46px; gap: 7px; align-items: end; }
    .msg-form textarea { min-height: 42px; max-height: 90px; resize: none; border: 1px solid #dbe3ee; border-radius: 21px; padding: 10px 14px; outline: none; background: #fff; font-weight: 750; }
    .msg-btn, .msg-send { width: 42px; min-height: 42px; height: 42px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size: 18px; cursor: pointer; }
    .msg-btn { border: 1px solid #dbe3ee; background: #fff; color: #128c7e; }
    .msg-btn.recording { background: #fee2e2; color: #dc2626; border-color: #fecaca; animation: pulse 1s infinite; }
    .msg-send { border: 0; background: #128c7e; color: #fff; font-weight: 950; }
    .msg-file-input { display: none; }
    .msg-label { display: block; margin-top: 5px; color: #64748b; font-weight: 850; font-size: 11px; }
    .msg-empty { background: rgba(255, 255, 255, .78); border: 1px dashed #cbd5e1; border-radius: 18px; color: #64748b; padding: 18px; font-weight: 850; }
    .msg-toast { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%); background: #0f172a; color: #fff; border-radius: 999px; padding: 10px 16px; font-weight: 900; z-index: 9999; opacity: 0; pointer-events: none; transition: .2s; }
    .msg-toast.show { opacity: 1; }

    @keyframes pulse { 50% { transform: scale(1.04); } }

    @media (max-width: 980px) {
        .messenger-page { height: calc(100vh - 115px); min-height: 620px; }
        .msg-shell { grid-template-columns: 1fr; }
        .msg-sidebar { border-right: 0; border-bottom: 1px solid #e2e8f0; max-height: 250px; }
        .msg-broadcast form { grid-template-columns: 1fr; }
        .msg-wrap { max-width: 90%; }
    }

    @media (max-width: 640px) {
        body.teacher-messenger-view .resp-navbar,
        body.teacher-messenger-view .teacher-subpage-header { display: none !important; }
        body.teacher-messenger-view .resp-page { padding: 0 !important; width: 100% !important; margin: 0 !important; }
        body.teacher-messenger-view .teacher-subpage-content { display: block !important; background: #f2f5ff !important; }
        .messenger-page { height: var(--timah-msg-vh, 100dvh); min-height: 0; gap: 0; overflow: hidden; background: #f2f5ff; }
        .msg-broadcast { display: none; }
        .msg-shell { display: block; height: 100%; border-radius: 0; border: 0; background: transparent; box-shadow: none; overflow: hidden; }
        .msg-shell--open .msg-sidebar { display: none !important; }
        .msg-shell--list .msg-chat { display: none !important; }
        .msg-sidebar { height: 100%; max-height: none; border: 0; background: #fff; border-radius: 20px 20px 0 0; overflow: hidden; }
        .msg-sidebar-head { padding: 14px 14px 10px; }
        .msg-sidebar-head h3 { font-size: 1.35rem; margin-bottom: 10px; }
        .msg-search { border-radius: 20px; padding: 8px 12px; }
        .msg-search input { min-height: 42px; font-size: 15px; }
        .msg-list { padding: 0; }
        .msg-thread { grid-template-columns: 46px minmax(0, 1fr) auto; gap: 10px; min-height: 68px; padding: 10px 14px; border-radius: 0; margin: 0; border: 0; border-top: 1px solid #e2e8f0; }
        .msg-thread.active { background: #f8fafc; border-color: #e2e8f0; }
        .msg-avatar { width: 46px; height: 46px; font-size: 16px; }
        .msg-name { font-size: 16px; }
        .msg-sub { font-size: 13px; line-height: 1.25; }
        .msg-time { font-size: 12px; }
        .msg-chat { height: 100%; min-height: 0; border-radius: 0; overflow: hidden; background-color: #efe7dc; }
        .msg-chat-head { min-height: 58px; padding: 8px 10px; background: #f8fafc; }
        .msg-chat-head .msg-avatar { width: 40px; height: 40px; }
        .msg-chat-head h3 { font-size: 17px; line-height: 1.1; }
        .msg-chat-head small { font-size: 12px; }
        .msg-back-list { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; padding: 0; font-size: 23px; border-radius: 14px; background: transparent; }
        .msg-close { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size: 22px; border-radius: 14px; }
        .msg-messages { padding: 10px; gap: 7px; scroll-padding-bottom: 10px; }
        .msg-wrap { max-width: 88%; }
        .msg-bubble { padding: 8px 10px; border-radius: 14px 14px 14px 4px; }
        .msg-wrap.me .msg-bubble { border-radius: 14px 14px 4px 14px; }
        .msg-tools { display: none; }
        .msg-audio { width: 220px; }
        .msg-composer { padding: 7px 8px max(7px, env(safe-area-inset-bottom)); background: #f0f2f5; }
        .msg-form { grid-template-columns: 40px 40px minmax(0, 1fr) 42px; gap: 6px; align-items: end; }
        .msg-btn, .msg-send { width: 40px; min-height: 40px; height: 40px; font-size: 18px; }
        .msg-send { width: 42px; }
        .msg-form textarea { min-height: 40px; max-height: 82px; border-radius: 20px; padding: 10px 13px; font-size: 14px; resize: none; }
        .msg-label { margin-top: 4px; font-size: 11px; }
        body.timah-keyboard-open .msg-chat-head { min-height: 52px; padding-top: 6px; padding-bottom: 6px; }
        body.timah-keyboard-open .msg-chat-head .msg-avatar { width: 36px; height: 36px; }
        body.timah-keyboard-open .msg-messages { padding-top: 8px; padding-bottom: 8px; }
    }
</style>

<div class="messenger-page">
    @if($assignments->isNotEmpty())
        <details class="msg-broadcast">
            <summary>📣 Envoyer une annonce à une classe</summary>
            <form method="POST" action="{{ route('teacher.messages.broadcast') }}">
                @csrf
                <select name="school_class_id" required>
                    @foreach($assignments->unique('school_class_id') as $assignment)
                        <option value="{{ $assignment->school_class_id }}">{{ $assignment->schoolClass->name ?? 'Classe' }}</option>
                    @endforeach
                </select>
                <input type="text" name="message" placeholder="Message à envoyer à toute la classe" required>
                <button type="submit">Envoyer</button>
            </form>
        </details>
    @endif

    <section class="msg-shell {{ $hasSelectedThread ? 'msg-shell--open' : 'msg-shell--list' }}" id="msgShell">
        <aside class="msg-sidebar" id="msgSidebar">
            <div class="msg-sidebar-head">
                <h3>Conversations</h3>
                <label class="msg-search">🔎 <input type="search" id="msgSearch" placeholder="Rechercher un élève"></label>
            </div>
            <div class="msg-list" id="msgList">
                @forelse($threads as $thread)
                    @php
                        $student = $thread->student;
                        $name = $student->full_name ?? $student->name ?? $student->username ?? 'Élève';
                        $initials = collect(explode(' ', trim($name)))->filter()->take(2)->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1)))->join('') ?: 'E';
                        $latest = $thread->latest_message ?? null;
                        $latestText = $latest ? ($latest->isFromTeacher() ? 'Vous : ' : '') . ($latest->message ?: 'Pièce jointe') : 'Démarrer la conversation';
                        $time = $latest?->created_at?->format('H:i') ?? '';
                        $unread = (int) ($thread->unread_count ?? 0);
                    @endphp
                    <a class="msg-thread {{ (int)($selectedStudentId ?? 0) === (int)$student->id ? 'active' : '' }}" data-name="{{ strtolower($name) }}" href="{{ route('teacher.messages.index', ['student' => $student->id]) }}">
                        <div class="msg-avatar">{{ $initials }}</div>
                        <div style="min-width:0">
                            <div class="msg-name">{{ $name }}</div>
                            <div class="msg-sub">{{ $student->studentProfile->schoolClass->name ?? 'Classe inconnue' }}</div>
                            <div class="msg-sub">{{ $latestText }}</div>
                        </div>
                        <div class="msg-time"><div>{{ $time }}</div>@if($unread > 0)<span class="msg-badge">{{ $unread }}</span>@endif</div>
                    </a>
                @empty
                    <div class="msg-empty">Aucun élève affecté à ce compte enseignant.</div>
                @endforelse
            </div>
        </aside>

        <main class="msg-chat" id="msgChat">
            @if($selectedThread ?? null)
                @php
                    $student = $selectedThread->student;
                    $studentName = $student->full_name ?? $student->name ?? $student->username ?? 'Élève';
                    $studentInitials = collect(explode(' ', trim($studentName)))->filter()->take(2)->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1)))->join('') ?: 'E';
                    $messages = collect($selectedThread->messages ?? []);
                    $lastDay = null;
                    $listUrl = route('teacher.messages.index');
                @endphp
                <header class="msg-chat-head">
                    <button class="msg-back-list" type="button" data-list-url="{{ $listUrl }}" aria-label="Retour aux conversations">‹</button>
                    <div class="msg-avatar">{{ $studentInitials }}</div>
                    <div style="min-width:0"><h3>{{ $studentName }}</h3><small>{{ $student->studentProfile->schoolClass->name ?? 'Classe inconnue' }} · {{ $messages->count() }} message(s)</small></div>
                    <button class="msg-close" type="button" data-list-url="{{ $listUrl }}" aria-label="Afficher la liste">✕</button>
                </header>

                <div class="msg-messages" id="msgMessages">
                    @forelse($messages as $message)
                        @php
                            $isMe = $message->isFromTeacher();
                            $day = $message->created_at?->format('d/m/Y');
                            $parent = $message->parentMessage ?? null;
                            $attachmentUrl = ($message->attachment_path && \Illuminate\Support\Facades\Route::has('teacher.messages.attachment')) ? route('teacher.messages.attachment', $message) : null;
                            $messageText = trim((string) $message->message);
                            $showText = $messageText !== '' && !($message->isAudioAttachment() && $messageText === (string) $message->attachment_name);
                        @endphp
                        @if($day && $day !== $lastDay)<div class="msg-day">{{ $message->created_at?->isToday() ? 'Aujourd’hui' : ($message->created_at?->isYesterday() ? 'Hier' : $day) }}</div>@php $lastDay = $day; @endphp@endif
                        <div class="msg-wrap {{ $isMe ? 'me' : '' }}" id="msg-{{ $message->id }}">
                            <div class="msg-bubble">
                                @if($parent)<div class="msg-quote"><strong>{{ $parent->isFromTeacher() ? 'Vous' : $studentName }}</strong><br>{{ \Illuminate\Support\Str::limit($parent->message, 120) }}</div>@endif
                                @if($message->attachment_path && $attachmentUrl)
                                    <a class="msg-attach" href="{{ $attachmentUrl }}" target="_blank" rel="noopener">
                                        @if($message->isImageAttachment())
                                            <img src="{{ $attachmentUrl }}" alt="{{ $message->attachment_name ?? 'Image' }}">
                                        @elseif($message->isAudioAttachment())
                                            <audio class="msg-audio" src="{{ $attachmentUrl }}" controls preload="metadata"></audio>
                                        @else
                                            <div class="msg-file"><span class="msg-file-icon">📄</span><div><strong>{{ $message->attachment_name ?? 'Document' }}</strong><br><small>{{ $message->humanAttachmentSize() }}</small></div></div>
                                        @endif
                                    </a>
                                @endif
                                @if($showText)<div class="msg-text">{{ $messageText }}</div>@endif
                                <div class="msg-meta"><span>{{ $message->created_at?->format('H:i') }}</span>@if($isMe)<span class="msg-ticks">{{ $message->read_at ? '✓✓' : '✓' }}</span>@endif</div>
                            </div>
                            <div class="msg-tools"><button class="msg-tool" type="button" data-reply-id="{{ $message->id }}" data-reply-text="{{ e(\Illuminate\Support\Str::limit($message->message ?: ($message->attachment_name ?: 'Pièce jointe'), 120)) }}">Répondre</button><button class="msg-tool" type="button" data-copy-text="{{ e($message->message) }}">Copier</button><form method="POST" action="{{ route('teacher.messages.delete', $message) }}" onsubmit="return confirm('Supprimer ce message de votre affichage ?')">@csrf<button class="msg-tool danger" type="submit">Supprimer</button></form></div>
                        </div>
                    @empty
                        <div class="msg-empty">Aucun message avec cet élève. Écrivez le premier message.</div>
                    @endforelse
                </div>

                <footer class="msg-composer" id="msgComposer">
                    <div class="msg-reply" id="replyPreview"><div><strong>Réponse à</strong><br><span id="replyText"></span></div><button class="msg-tool danger" type="button" id="cancelReply">Annuler</button></div>
                    <form class="msg-form" method="POST" action="{{ route('teacher.messages.send') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        <input type="hidden" name="teacher_assignment_id" value="{{ $selectedThread->assignment->id ?? '' }}">
                        <input type="hidden" name="parent_message_id" id="parentMessageId" value="">
                        <label class="msg-btn" title="Joindre image, PDF, Word ou audio">📎<input class="msg-file-input" type="file" name="attachment" id="msgAttachment"></label>
                        <button class="msg-btn" type="button" id="voiceBtn" title="Enregistrer un vocal">🎙️</button>
                        <input class="msg-file-input" type="file" name="voice_note" id="voiceInput" accept="audio/*">
                        <textarea name="message" id="messageText" placeholder="Message" rows="1"></textarea>
                        <button class="msg-send" type="submit">➤</button>
                    </form>
                    <small class="msg-label" id="fileLabel"></small>
                </footer>
            @else
                <header class="msg-chat-head"><div class="msg-avatar">💬</div><div><h3>Sélectionnez une conversation</h3><small>Choisissez un élève dans la liste.</small></div></header><div class="msg-messages"><div class="msg-empty">Aucune conversation sélectionnée.</div></div>
            @endif
        </main>
    </section>
</div>
<div class="msg-toast" id="msgToast">Copié</div>

<script>
(() => {
const shell=document.getElementById('msgShell');
const messages=document.getElementById('msgMessages');
const text=document.getElementById('messageText');
const toast=document.getElementById('msgToast');
const listUrl=document.querySelector('[data-list-url]')?.dataset.listUrl || '{{ route('teacher.messages.index') }}';

function showToast(t){ if(!toast)return; toast.textContent=t; toast.classList.add('show'); setTimeout(()=>toast.classList.remove('show'),1500); }
function scrollBottom(){ if(messages) messages.scrollTop = messages.scrollHeight; }
function applyViewport(){
    const vv = window.visualViewport;
    const h = vv ? vv.height : window.innerHeight;
    document.documentElement.style.setProperty('--timah-msg-vh', h + 'px');
    const keyboardOpen = vv ? (window.innerHeight - vv.height > 120) : false;
    document.body.classList.toggle('timah-keyboard-open', keyboardOpen);
    setTimeout(scrollBottom, 80);
}
window.TimahMessagesBackToList = function(){
    if(shell){ shell.classList.remove('msg-shell--open'); shell.classList.add('msg-shell--list'); }
    document.querySelectorAll('.msg-thread.active').forEach(i=>i.classList.remove('active'));
    if(window.history && listUrl) window.history.pushState({}, '', listUrl);
    setTimeout(()=>document.getElementById('msgSearch')?.focus(), 120);
};

document.querySelectorAll('.msg-close,.msg-back-list').forEach(btn=>btn.addEventListener('click', (e)=>{ e.preventDefault(); window.TimahMessagesBackToList(); }));
const search=document.getElementById('msgSearch'), list=document.getElementById('msgList');
if(search&&list){ search.addEventListener('input',()=>{const q=search.value.trim().toLowerCase();list.querySelectorAll('.msg-thread').forEach(i=>i.style.display=(i.dataset.name||'').includes(q)?'grid':'none')}); }

document.querySelectorAll('[data-copy-text]').forEach(b=>b.addEventListener('click',async()=>{const t=b.dataset.copyText||'';if(!t)return showToast('Rien à copier');try{await navigator.clipboard.writeText(t);showToast('Message copié')}catch(e){showToast('Copie impossible')}}));
const reply=document.getElementById('replyPreview'),replyText=document.getElementById('replyText'),parent=document.getElementById('parentMessageId'),cancel=document.getElementById('cancelReply');
document.querySelectorAll('[data-reply-id]').forEach(b=>b.addEventListener('click',()=>{if(!reply||!replyText||!parent)return;parent.value=b.dataset.replyId||'';replyText.textContent=b.dataset.replyText||'Message';reply.classList.add('show');text?.focus()}));
cancel?.addEventListener('click',()=>{if(parent)parent.value='';reply?.classList.remove('show')});
const attach=document.getElementById('msgAttachment'),label=document.getElementById('fileLabel');
attach?.addEventListener('change',()=>{label.textContent=attach.files?.[0]?.name?'Pièce jointe : '+attach.files[0].name:''});
text?.addEventListener('focus',()=>{applyViewport(); setTimeout(scrollBottom, 240);});
text?.addEventListener('input',()=>{text.style.height='auto'; text.style.height=Math.min(text.scrollHeight, 90)+'px'; setTimeout(scrollBottom, 40);});
text?.addEventListener('blur',()=>setTimeout(applyViewport, 120));
window.visualViewport?.addEventListener('resize', applyViewport);
window.addEventListener('resize', applyViewport);
applyViewport();
setTimeout(scrollBottom, 120);

const voiceBtn=document.getElementById('voiceBtn'),voiceInput=document.getElementById('voiceInput');let recorder=null,chunks=[];
voiceBtn?.addEventListener('click',async()=>{if(!navigator.mediaDevices||!window.MediaRecorder)return showToast('Vocal non supporté sur ce navigateur');if(recorder&&recorder.state==='recording'){recorder.stop();return}try{const stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true,autoGainControl:true}});chunks=[];recorder=new MediaRecorder(stream);recorder.ondataavailable=e=>{if(e.data.size>0)chunks.push(e.data)};recorder.onstop=()=>{stream.getTracks().forEach(track=>track.stop());const blob=new Blob(chunks,{type:'audio/webm'});const f=new File([blob],'voice-note-'+Date.now()+'.webm',{type:'audio/webm'});const dt=new DataTransfer();dt.items.add(f);voiceInput.files=dt.files;voiceBtn.classList.remove('recording');voiceBtn.textContent='🎙️';label.textContent='Note vocale prête';showToast('Vocal prêt à envoyer')};recorder.start();voiceBtn.classList.add('recording');voiceBtn.textContent='⏹️';label.textContent='Enregistrement vocal en cours...'}catch(e){showToast('Micro refusé ou indisponible')}});
})();
</script>
@endsection
