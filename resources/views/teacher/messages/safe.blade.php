@extends('layouts.teacher')

@section('title', 'Messagerie enseignant')
@section('page_title', 'Messagerie')
@section('page_subtitle', 'Conversations rapides avec vos élèves affectés.')

@section('content')
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

    .msg-hero {
        flex: 0 0 auto;
        background: linear-gradient(135deg, #052e2b, #128c7e 58%, #25d366);
        color: #fff;
        border-radius: 22px;
        padding: 14px 18px;
        box-shadow: 0 16px 38px rgba(18, 140, 126, .2);
    }

    .msg-hero h2 { margin: 0; font-weight: 950; }
    .msg-hero p { margin: 4px 0 0; color: #dcfce7; font-weight: 700; }

    .msg-broadcast {
        flex: 0 0 auto;
        background: #fff;
        border: 1px solid #dbeafe;
        border-radius: 18px;
        padding: 12px;
    }

    .msg-broadcast summary {
        cursor: pointer;
        font-weight: 950;
    }

    .msg-broadcast form {
        display: grid;
        grid-template-columns: 250px 1fr auto;
        gap: 8px;
        margin-top: 10px;
    }

    .msg-broadcast select,
    .msg-broadcast input {
        border: 1px solid #dbe3ee;
        border-radius: 14px;
        padding: 12px;
    }

    .msg-broadcast button {
        border: 0;
        border-radius: 14px;
        background: #128c7e;
        color: #fff;
        font-weight: 950;
        padding: 12px 18px;
    }

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

    .msg-sidebar {
        min-height: 0;
        display: flex;
        flex-direction: column;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
    }

    .msg-sidebar-head {
        flex: 0 0 auto;
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px;
    }

    .msg-sidebar-head h3 {
        margin: 0 0 10px;
        font-weight: 950;
    }

    .msg-search {
        display: flex;
        gap: 8px;
        align-items: center;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        padding: 9px 12px;
    }

    .msg-search input {
        border: 0;
        background: transparent;
        outline: 0;
        width: 100%;
        font-weight: 800;
    }

    .msg-list {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
        padding: 10px;
    }

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

    .msg-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, #128c7e, #25d366);
        color: #fff;
        font-weight: 950;
    }

    .msg-name { font-weight: 950; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msg-sub { font-size: .86rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .msg-time { font-size: .76rem; color: #64748b; text-align: right; font-weight: 800; }
    .msg-badge { display: inline-grid; place-items: center; min-width: 22px; height: 22px; border-radius: 999px; background: #25d366; color: #052e2b; font-size: .72rem; font-weight: 950; margin-top: 5px; }

    .msg-chat {
        height: 100%;
        min-height: 0;
        display: flex;
        flex-direction: column;
        background-color: #efeae2;
        background-image: radial-gradient(rgba(18, 140, 126, .08) 1px, transparent 1px);
        background-size: 18px 18px;
    }

    .msg-chat-head {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 10px 14px;
    }

    .msg-chat-head h3 { margin: 0; font-size: 1.05rem; font-weight: 950; }
    .msg-chat-head small { display: block; color: #64748b; font-weight: 750; }

    .msg-close {
        margin-left: auto;
        border: 0;
        border-radius: 12px;
        background: #e2e8f0;
        color: #0f172a;
        font-weight: 950;
        padding: 9px 11px;
        text-decoration: none;
    }

    .msg-messages {
        flex: 1 1 auto;
        min-height: 0;
        overflow: auto;
        padding: 14px 18px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

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
    .msg-audio { width: 280px; max-width: 100%; }

    .msg-tools { display: flex; gap: 6px; margin-top: 6px; opacity: .76; }
    .msg-tool { border: 0; background: rgba(15, 23, 42, .06); border-radius: 999px; padding: 5px 9px; font-size: .72rem; font-weight: 900; cursor: pointer; color: #334155; }
    .msg-tool.danger { color: #dc2626; }

    .msg-composer { flex: 0 0 auto; background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 10px 12px; z-index: 5; }
    .msg-reply { display: none; background: #e7f8ef; border-left: 4px solid #128c7e; border-radius: 14px; padding: 9px; margin-bottom: 8px; }
    .msg-reply.show { display: flex; justify-content: space-between; gap: 10px; }
    .msg-form { display: grid; grid-template-columns: auto auto minmax(120px, 1fr) auto; gap: 8px; align-items: end; }
    .msg-form textarea { min-height: 46px; max-height: 110px; resize: vertical; border: 1px solid #dbe3ee; border-radius: 18px; padding: 12px 14px; outline: none; background: #fff; font-weight: 750; }
    .msg-btn { border: 1px solid #dbe3ee; background: #fff; border-radius: 18px; padding: 13px 15px; font-weight: 950; cursor: pointer; color: #128c7e; }
    .msg-btn.recording { background: #fee2e2; color: #dc2626; border-color: #fecaca; animation: pulse 1s infinite; }
    .msg-send { border: 0; border-radius: 18px; background: #25d366; color: #052e2b; font-weight: 950; padding: 13px 17px; cursor: pointer; }
    .msg-file-input { display: none; }
    .msg-label { display: block; margin-top: 6px; color: #64748b; font-weight: 850; }
    .msg-empty { background: rgba(255, 255, 255, .78); border: 1px dashed #cbd5e1; border-radius: 18px; color: #64748b; padding: 18px; font-weight: 850; }
    .msg-toast { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%); background: #0f172a; color: #fff; border-radius: 999px; padding: 10px 16px; font-weight: 900; z-index: 9999; opacity: 0; pointer-events: none; transition: .2s; }
    .msg-toast.show { opacity: 1; }

    @keyframes pulse { 50% { transform: scale(1.04); } }

    @media (max-width: 980px) {
        .messenger-page { height: calc(100vh - 115px); min-height: 620px; }
        .msg-hero { display: none; }
        .msg-shell { grid-template-columns: 1fr; }
        .msg-sidebar { border-right: 0; border-bottom: 1px solid #e2e8f0; max-height: 250px; }
        .msg-broadcast form { grid-template-columns: 1fr; }
        .msg-wrap { max-width: 90%; }
    }

    @media (max-width: 640px) {
        body[data-ui-role="teacher"] .teacher-subpage-header { display: none !important; }
        .messenger-page { height: calc(100dvh - 150px); min-height: 0; gap: 0; overflow: hidden; }
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
        .msg-chat { height: 100%; min-height: 0; border-radius: 20px 20px 0 0; overflow: hidden; background-color: #efe7dc; }
        .msg-chat-head { min-height: 58px; padding: 8px 10px; background: #f8fafc; }
        .msg-chat-head .msg-avatar { width: 40px; height: 40px; }
        .msg-chat-head h3 { font-size: 17px; line-height: 1.1; }
        .msg-chat-head small { font-size: 12px; }
        .msg-close { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; padding: 0; font-size: 22px; border-radius: 14px; }
        .msg-messages { padding: 10px; gap: 7px; }
        .msg-wrap { max-width: 88%; }
        .msg-bubble { padding: 8px 10px; border-radius: 14px 14px 14px 4px; }
        .msg-wrap.me .msg-bubble { border-radius: 14px 14px 4px 14px; }
        .msg-tools { display: none; }
        .msg-composer { padding: 7px 8px; background: #f0f2f5; }
        .msg-form { display: grid; grid-template-columns: 40px 40px minmax(0, 1fr) 42px; gap: 6px; align-items: end; }
        .msg-btn, .msg-send { width: 40px; min-height: 40px; height: 40px; padding: 0; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
        .msg-send { width: 42px; background: #128c7e; color: #fff; }
        .msg-form textarea { min-height: 40px; max-height: 90px; border-radius: 20px; padding: 10px 13px; font-size: 14px; resize: none; }
        .msg-label { margin-top: 4px; font-size: 11px; }
    }
</style>

<div class="messenger-page">
    <section class="msg-hero"><h2>💬 Messagerie TIMAH</h2><p>Conversations en bulles, pièces jointes, réponses, accusés de lecture et notes vocales.</p></section>

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

    <section class="msg-shell {{ $hasSelectedThread ? 'msg-shell--open' : 'msg-shell--list' }}">
        <aside class="msg-sidebar">
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

        <main class="msg-chat">
            @if($selectedThread ?? null)
                @php
                    $student = $selectedThread->student;
                    $studentName = $student->full_name ?? $student->name ?? $student->username ?? 'Élève';
                    $studentInitials = collect(explode(' ', trim($studentName)))->filter()->take(2)->map(fn($p)=>mb_strtoupper(mb_substr($p,0,1)))->join('') ?: 'E';
                    $messages = collect($selectedThread->messages ?? []);
                    $lastDay = null;
                @endphp
                <header class="msg-chat-head">
                    <div class="msg-avatar">{{ $studentInitials }}</div>
                    <div><h3>{{ $studentName }}</h3><small>{{ $student->studentProfile->schoolClass->name ?? 'Classe inconnue' }} · {{ $messages->count() }} message(s)</small></div>
                    <a class="msg-close" href="{{ route('teacher.messages.index') }}">✕</a>
                </header>

                <div class="msg-messages" id="msgMessages">
                    @forelse($messages as $message)
                        @php
                            $isMe = $message->isFromTeacher();
                            $day = $message->created_at?->format('d/m/Y');
                            $parent = $message->parentMessage ?? null;
                            $attachmentUrl = ($message->attachment_path && \Illuminate\Support\Facades\Route::has('teacher.messages.attachment')) ? route('teacher.messages.attachment', $message) : null;
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
                                            <div class="msg-file" style="margin-bottom:8px"><span class="msg-file-icon">🎙️</span><strong>{{ $message->attachment_name ?? 'Audio' }}</strong></div><audio class="msg-audio" src="{{ $attachmentUrl }}" controls preload="metadata"></audio>
                                        @else
                                            <div class="msg-file"><span class="msg-file-icon">📄</span><div><strong>{{ $message->attachment_name ?? 'Document' }}</strong><br><small>{{ $message->humanAttachmentSize() }}</small></div></div>
                                        @endif
                                    </a>
                                @endif
                                @if(trim((string)$message->message) !== '')<div class="msg-text">{{ $message->message }}</div>@endif
                                <div class="msg-meta"><span>{{ $message->created_at?->format('H:i') }}</span>@if($isMe)<span class="msg-ticks">{{ $message->read_at ? '✓✓' : '✓' }}</span>@endif</div>
                            </div>
                            <div class="msg-tools"><button class="msg-tool" type="button" data-reply-id="{{ $message->id }}" data-reply-text="{{ e(\Illuminate\Support\Str::limit($message->message ?: ($message->attachment_name ?: 'Pièce jointe'), 120)) }}">Répondre</button><button class="msg-tool" type="button" data-copy-text="{{ e($message->message) }}">Copier</button><form method="POST" action="{{ route('teacher.messages.delete', $message) }}" onsubmit="return confirm('Supprimer ce message de votre affichage ?')">@csrf<button class="msg-tool danger" type="submit">Supprimer</button></form></div>
                        </div>
                    @empty
                        <div class="msg-empty">Aucun message avec cet élève. Écrivez le premier message.</div>
                    @endforelse
                </div>

                <footer class="msg-composer">
                    <div class="msg-reply" id="replyPreview"><div><strong>Réponse à</strong><br><span id="replyText"></span></div><button class="msg-tool danger" type="button" id="cancelReply">Annuler</button></div>
                    <form class="msg-form" method="POST" action="{{ route('teacher.messages.send') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                        <input type="hidden" name="teacher_assignment_id" value="{{ $selectedThread->assignment->id ?? '' }}">
                        <input type="hidden" name="parent_message_id" id="parentMessageId" value="">
                        <label class="msg-btn" title="Joindre image, PDF, Word ou audio">📎<input class="msg-file-input" type="file" name="attachment" id="msgAttachment"></label>
                        <button class="msg-btn" type="button" id="voiceBtn" title="Enregistrer un vocal">🎙️</button>
                        <input class="msg-file-input" type="file" name="voice_note" id="voiceInput" accept="audio/*">
                        <textarea name="message" id="messageText" placeholder="Message"></textarea>
                        <button class="msg-send" type="submit">➤</button>
                    </form>
                    <small class="msg-label" id="fileLabel"></small>
                </footer>
            @else
                <header class="msg-chat-head"><div class="msg-avatar">💬</div><div><h3>Sélectionnez une conversation</h3><small>Choisissez un élève à gauche pour discuter.</small></div></header><div class="msg-messages"><div class="msg-empty">Aucune conversation sélectionnée.</div></div>
            @endif
        </main>
    </section>
</div>
<div class="msg-toast" id="msgToast">Copié</div>

<script>
(() => {
const list=document.getElementById('msgList'),search=document.getElementById('msgSearch');if(search&&list){search.addEventListener('input',()=>{const q=search.value.trim().toLowerCase();list.querySelectorAll('.msg-thread').forEach(i=>i.style.display=(i.dataset.name||'').includes(q)?'grid':'none')})}const box=document.getElementById('msgMessages');if(box)box.scrollTop=box.scrollHeight;const toast=document.getElementById('msgToast');const show=t=>{if(!toast)return;toast.textContent=t;toast.classList.add('show');setTimeout(()=>toast.classList.remove('show'),1500)};document.querySelectorAll('[data-copy-text]').forEach(b=>b.addEventListener('click',async()=>{const t=b.dataset.copyText||'';if(!t)return show('Rien à copier');try{await navigator.clipboard.writeText(t);show('Message copié')}catch(e){show('Copie impossible')}}));const reply=document.getElementById('replyPreview'),replyText=document.getElementById('replyText'),parent=document.getElementById('parentMessageId'),cancel=document.getElementById('cancelReply'),text=document.getElementById('messageText');document.querySelectorAll('[data-reply-id]').forEach(b=>b.addEventListener('click',()=>{if(!reply||!replyText||!parent)return;parent.value=b.dataset.replyId||'';replyText.textContent=b.dataset.replyText||'Message';reply.classList.add('show');text?.focus()}));cancel?.addEventListener('click',()=>{if(parent)parent.value='';reply?.classList.remove('show')});const attach=document.getElementById('msgAttachment'),label=document.getElementById('fileLabel');attach?.addEventListener('change',()=>{label.textContent=attach.files?.[0]?.name?'Pièce jointe : '+attach.files[0].name:''});const voiceBtn=document.getElementById('voiceBtn'),voiceInput=document.getElementById('voiceInput');let recorder=null,chunks=[];voiceBtn?.addEventListener('click',async()=>{if(!navigator.mediaDevices||!window.MediaRecorder)return show('Vocal non supporté sur ce navigateur');if(recorder&&recorder.state==='recording'){recorder.stop();return}try{const stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true,autoGainControl:true}});chunks=[];recorder=new MediaRecorder(stream);recorder.ondataavailable=e=>{if(e.data.size>0)chunks.push(e.data)};recorder.onstop=()=>{stream.getTracks().forEach(track=>track.stop());const blob=new Blob(chunks,{type:'audio/webm'});const f=new File([blob],'voice-note-'+Date.now()+'.webm',{type:'audio/webm'});const dt=new DataTransfer();dt.items.add(f);voiceInput.files=dt.files;voiceBtn.classList.remove('recording');voiceBtn.textContent='🎙️';label.textContent='Note vocale prête : '+f.name;show('Vocal prêt à envoyer')};recorder.start();voiceBtn.classList.add('recording');voiceBtn.textContent='⏹️';label.textContent='Enregistrement vocal en cours... clique encore pour arrêter'}catch(e){show('Micro refusé ou indisponible')}});setTimeout(()=>{if(document.visibilityState==='visible'&&!document.querySelector('textarea:focus')&&!document.querySelector('.recording'))window.location.reload()},45000);
})();
</script>
@endsection
