@extends('layouts.student')

@section('title', 'Messagerie')

@section('content')
@php
    $threads = collect($threads ?? []);
    $selectedThreadId = (int) ($selectedThreadId ?? 0);
    $selectedThread = $threads->first(fn ($thread) => (int) $thread->assignment->id === $selectedThreadId);
    $totalUnread = $threads->sum(fn ($thread) => (int) ($thread->unread_count ?? 0));
@endphp

<style>
    .student-messenger-page{height:calc(100vh - 150px);min-height:560px;overflow:hidden;display:flex;flex-direction:column;gap:10px}.student-messenger-hero{flex:0 0 auto;background:linear-gradient(135deg,#052e2b,#128c7e 58%,#25d366);color:#fff;border-radius:22px;padding:14px 18px;box-shadow:0 16px 38px rgba(18,140,126,.2)}.student-messenger-hero h1{margin:0;font-size:1.35rem;font-weight:950}.student-messenger-hero p{margin:4px 0 0;color:#dcfce7;font-weight:750}.wa-notice{flex:0 0 auto;display:flex;align-items:center;justify-content:space-between;gap:10px;background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;border-radius:16px;padding:10px 12px;font-weight:900}.wa-shell{flex:1 1 auto;min-height:0;display:grid;grid-template-columns:minmax(280px,380px) minmax(0,1fr);background:#fff;border:1px solid #dbeafe;border-radius:24px;overflow:hidden;box-shadow:0 20px 60px rgba(15,23,42,.08)}.wa-sidebar{min-height:0;display:flex;flex-direction:column;background:#f8fafc;border-right:1px solid #e2e8f0}.wa-sidebar-head{flex:0 0 auto;padding:14px;background:#fff;border-bottom:1px solid #e2e8f0}.wa-sidebar-head h2{margin:0 0 10px;font-size:1.1rem;font-weight:950}.wa-search{display:flex;align-items:center;gap:8px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:9px 12px}.wa-search input{border:0;background:transparent;outline:0;width:100%;font-weight:800;color:#0f172a}.wa-thread-list{flex:1 1 auto;min-height:0;overflow:auto;padding:10px}.wa-thread{display:grid;grid-template-columns:48px minmax(0,1fr) auto;gap:10px;align-items:center;text-decoration:none;color:#0f172a;padding:10px;border-radius:18px;margin-bottom:7px;border:1px solid transparent}.wa-thread:hover{background:#eef7f2}.wa-thread.active{background:#e7f8ef;border-color:#9be7b3}.wa-avatar{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,#128c7e,#25d366);color:#fff;font-weight:950;font-size:1rem;box-shadow:0 10px 24px rgba(18,140,126,.24)}.wa-thread-title{font-weight:950;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.wa-thread-sub{font-size:.86rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.wa-thread-time{font-size:.76rem;color:#64748b;text-align:right;font-weight:800}.wa-badge{display:inline-grid;place-items:center;min-width:23px;height:23px;border-radius:999px;background:#25d366;color:#052e2b;font-size:.74rem;font-weight:950;margin-top:6px}.wa-chat{height:100%;min-height:0;display:flex;flex-direction:column;background:#efeae2;background-image:radial-gradient(rgba(18,140,126,.08) 1px,transparent 1px);background-size:18px 18px}.wa-chat-head{flex:0 0 auto;display:flex;align-items:center;gap:12px;background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:10px 14px}.wa-chat-head h3{margin:0;font-size:1.05rem;font-weight:950}.wa-chat-head small{display:block;color:#64748b;font-weight:750}.wa-close{margin-left:auto;border:0;border-radius:12px;background:#e2e8f0;color:#0f172a;font-weight:950;padding:9px 11px;text-decoration:none}.wa-messages{flex:1 1 auto;min-height:0;overflow:auto;padding:14px 18px;display:flex;flex-direction:column;gap:8px}.wa-day{align-self:center;background:rgba(255,255,255,.84);border:1px solid rgba(148,163,184,.35);border-radius:999px;padding:6px 12px;font-size:.78rem;color:#64748b;font-weight:900;margin:6px 0}.wa-bubble-wrap{display:flex;flex-direction:column;max-width:min(78%,700px);align-self:flex-start}.wa-bubble-wrap.me{align-self:flex-end}.wa-bubble{background:#fff;border-radius:18px 18px 18px 4px;padding:10px 12px;box-shadow:0 3px 10px rgba(15,23,42,.08);border:1px solid rgba(226,232,240,.8)}.wa-bubble-wrap.me .wa-bubble{background:#d9fdd3;border-color:#bbf7d0;border-radius:18px 18px 4px 18px}.wa-reply-quote{border-left:4px solid #128c7e;background:rgba(18,140,126,.08);border-radius:10px;padding:8px;margin-bottom:8px;color:#475569;font-size:.86rem}.wa-text{white-space:pre-wrap;line-height:1.45;color:#0f172a}.wa-meta{display:flex;justify-content:flex-end;gap:6px;align-items:center;color:#64748b;font-size:.72rem;font-weight:850;margin-top:6px}.wa-ticks{color:#128c7e;font-size:.9rem}.wa-attachment{display:block;text-decoration:none;color:#0f172a;border:1px solid rgba(148,163,184,.35);background:rgba(255,255,255,.72);border-radius:14px;padding:8px;margin-bottom:8px;overflow:hidden}.wa-attachment img{display:block;max-width:260px;max-height:220px;border-radius:12px;object-fit:cover}.wa-file{display:flex;align-items:center;gap:10px}.wa-file-icon{width:42px;height:42px;border-radius:12px;background:#fee2e2;color:#dc2626;display:grid;place-items:center;font-weight:950}.wa-audio{width:280px;max-width:100%}.wa-msg-tools{display:flex;gap:6px;margin-top:6px;opacity:.75}.wa-tool{border:0;background:rgba(15,23,42,.06);border-radius:999px;padding:5px 9px;font-size:.72rem;font-weight:900;cursor:pointer;color:#334155}.wa-composer{flex:0 0 auto;background:#f8fafc;border-top:1px solid #e2e8f0;padding:10px 12px;z-index:4}.wa-reply-preview{display:none;background:#e7f8ef;border-left:4px solid #128c7e;border-radius:14px;padding:9px;margin-bottom:8px;color:#0f172a}.wa-reply-preview.is-visible{display:flex;justify-content:space-between;gap:10px}.wa-compose-form{display:grid;grid-template-columns:auto auto minmax(120px,1fr) auto;gap:8px;align-items:end}.wa-compose-form textarea{min-height:46px;max-height:110px;resize:vertical;border:1px solid #dbe3ee;border-radius:18px;padding:12px 14px;outline:none;background:#fff;font-weight:750}.wa-send{border:0;border-radius:18px;background:#25d366;color:#052e2b;font-weight:950;padding:13px 17px;cursor:pointer}.wa-file-input{display:none}.wa-attach,.wa-mic{border:1px solid #dbe3ee;background:#fff;border-radius:18px;padding:13px 15px;font-weight:950;cursor:pointer;color:#128c7e}.wa-mic.recording{background:#fee2e2;color:#dc2626;border-color:#fecaca;animation:pulse 1s infinite}@keyframes pulse{50%{transform:scale(1.04)}}.wa-file-label{display:block;margin-top:6px;color:#64748b;font-weight:850}.wa-empty{background:rgba(255,255,255,.78);border:1px dashed #cbd5e1;border-radius:18px;color:#64748b;padding:18px;font-weight:850}.wa-toast{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);background:#0f172a;color:#fff;border-radius:999px;padding:10px 16px;font-weight:900;z-index:9999;opacity:0;pointer-events:none;transition:.2s}.wa-toast.show{opacity:1}@media(max-width:980px){.student-messenger-page{height:calc(100vh - 120px);min-height:620px}.student-messenger-hero{display:none}.wa-shell{grid-template-columns:1fr}.wa-sidebar{border-right:0;border-bottom:1px solid #e2e8f0;max-height:270px}.wa-chat{min-height:0}.wa-bubble-wrap{max-width:90%}}@media(max-width:640px){.student-messenger-page{height:calc(100vh - 95px);min-height:590px}.wa-shell{border-radius:18px}.wa-sidebar{max-height:240px}.wa-compose-form{grid-template-columns:auto auto 1fr}.wa-send{grid-column:1/4}.wa-chat-head{padding:9px 10px}.wa-messages{padding:10px}.wa-bubble-wrap{max-width:94%}}
</style>

<div class="student-messenger-page">
    <section class="student-messenger-hero"><h1>💬 Messagerie TIMAH</h1><p>Échange directement avec tes enseignants. Le champ d’écriture reste toujours visible en bas de la conversation.</p></section>

    @if($totalUnread > 0)
        <div class="wa-notice">🔔 {{ $totalUnread }} nouveau(x) message(s) enseignant non lu(s). <span>Ouvre la conversation pour confirmer la lecture.</span></div>
    @endif

    <section class="wa-shell">
        <aside class="wa-sidebar">
            <div class="wa-sidebar-head"><h2>Conversations</h2><label class="wa-search">🔎 <input type="search" id="waSearch" placeholder="Rechercher un enseignant"></label></div>
            <div class="wa-thread-list" id="waThreadList">
                @forelse($threads as $thread)
                    @php
                        $teacher = $thread->teacher;
                        $teacherName = $teacher->full_name ?? $teacher->name ?? $teacher->username ?? 'Enseignant';
                        $initials = collect(explode(' ', trim($teacherName)))->filter()->take(2)->map(fn($part)=>mb_strtoupper(mb_substr($part,0,1)))->join('') ?: 'E';
                        $latest = $thread->latest_message ?? null;
                        $latestText = $latest ? ($latest->isFromTeacher() ? '' : 'Vous : ') . ($latest->message ?: 'Pièce jointe') : 'Démarrer la conversation';
                        $time = $latest?->created_at?->format('H:i') ?? '';
                        $unread = (int) ($thread->unread_count ?? 0);
                        $subject = $thread->subject?->name ?? 'Matière';
                    @endphp
                    <a class="wa-thread {{ (int)$selectedThreadId === (int)$thread->assignment->id ? 'active' : '' }}" data-name="{{ strtolower($teacherName . ' ' . $subject) }}" href="{{ route('student.messages.index', ['thread' => $thread->assignment->id]) }}">
                        <div class="wa-avatar">{{ $initials }}</div>
                        <div style="min-width:0"><div class="wa-thread-title">{{ $teacherName }}</div><div class="wa-thread-sub">{{ $subject }} · {{ $thread->schoolClass?->name ?? $studentProfile->schoolClass->name ?? 'Classe' }}</div><div class="wa-thread-sub">{{ $latestText }}</div></div>
                        <div class="wa-thread-time"><div>{{ $time }}</div>@if($unread > 0)<span class="wa-badge">{{ $unread }}</span>@endif</div>
                    </a>
                @empty
                    <div class="wa-empty">Aucun enseignant affecté à ta classe pour le moment.</div>
                @endforelse
            </div>
        </aside>

        <main class="wa-chat">
            @if($selectedThread)
                @php
                    $teacher = $selectedThread->teacher;
                    $teacherName = $teacher->full_name ?? $teacher->name ?? $teacher->username ?? 'Enseignant';
                    $teacherInitials = collect(explode(' ', trim($teacherName)))->filter()->take(2)->map(fn($part)=>mb_strtoupper(mb_substr($part,0,1)))->join('') ?: 'E';
                    $messages = collect($selectedThread->messages ?? []);
                    $lastDay = null;
                @endphp
                <header class="wa-chat-head"><div class="wa-avatar">{{ $teacherInitials }}</div><div><h3>{{ $teacherName }}</h3><small>{{ $selectedThread->subject?->name ?? 'Matière' }} · {{ $messages->count() }} message(s)</small></div><a class="wa-close" href="{{ route('student.messages.index') }}" title="Fermer la conversation">✕</a></header>

                <div class="wa-messages" id="waMessages">
                    @forelse($messages as $message)
                        @php
                            $isMe = !method_exists($message, 'isFromTeacher') || !$message->isFromTeacher();
                            $day = $message->created_at?->format('d/m/Y');
                            $parent = $message->parentMessage ?? null;
                            $attachmentUrl = ($message->attachment_path && \Illuminate\Support\Facades\Route::has('student.messages.attachment')) ? route('student.messages.attachment', $message) : null;
                        @endphp
                        @if($day && $day !== $lastDay)<div class="wa-day">{{ $message->created_at?->isToday() ? 'Aujourd’hui' : ($message->created_at?->isYesterday() ? 'Hier' : $day) }}</div>@php $lastDay = $day; @endphp@endif
                        <div class="wa-bubble-wrap {{ $isMe ? 'me' : '' }}" id="msg-{{ $message->id }}">
                            <div class="wa-bubble">
                                @if($parent)<div class="wa-reply-quote"><strong>{{ $parent->isFromTeacher() ? $teacherName : 'Vous' }}</strong><br>{{ \Illuminate\Support\Str::limit($parent->message, 120) }}</div>@endif
                                @if($message->attachment_path && $attachmentUrl)
                                    <a class="wa-attachment" href="{{ $attachmentUrl }}" target="_blank" rel="noopener">
                                        @if($message->isImageAttachment())<img src="{{ $attachmentUrl }}" alt="{{ $message->attachment_name ?? 'Image' }}">
                                        @elseif($message->isAudioAttachment())<div class="wa-file" style="margin-bottom:8px"><span class="wa-file-icon">🎙️</span><strong>{{ $message->attachment_name ?? 'Audio' }}</strong></div><audio class="wa-audio" src="{{ $attachmentUrl }}" controls preload="metadata"></audio>
                                        @else<div class="wa-file"><span class="wa-file-icon">📄</span><div><strong>{{ $message->attachment_name ?? 'Document' }}</strong><br><small>{{ $message->humanAttachmentSize() }}</small></div></div>@endif
                                    </a>
                                @endif
                                @if(trim((string)$message->message) !== '')<div class="wa-text">{{ $message->message }}</div>@endif
                                <div class="wa-meta"><span>{{ $message->created_at?->format('H:i') }}</span>@if($isMe)<span class="wa-ticks">{{ $message->read_at ? '✓✓' : '✓' }}</span>@endif</div>
                            </div>
                            <div class="wa-msg-tools"><button class="wa-tool" type="button" data-reply-id="{{ $message->id }}" data-reply-text="{{ e(\Illuminate\Support\Str::limit($message->message ?: ($message->attachment_name ?: 'Pièce jointe'), 120)) }}">Répondre</button><button class="wa-tool" type="button" data-copy-text="{{ e($message->message) }}">Copier</button></div>
                        </div>
                    @empty
                        <div class="wa-empty">Aucun message dans cette conversation. Écris le premier message à ton enseignant.</div>
                    @endforelse
                </div>

                <footer class="wa-composer">
                    <div class="wa-reply-preview" id="replyPreview"><div><strong>Réponse à</strong><br><span id="replyText"></span></div><button class="wa-tool" type="button" id="cancelReply">Annuler</button></div>
                    <form class="wa-compose-form" method="POST" action="{{ route('student.messages.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="teacher_assignment_id" value="{{ $selectedThread->assignment->id }}">
                        <input type="hidden" name="title" value="Conversation">
                        <input type="hidden" name="parent_message_id" id="parentMessageId" value="">
                        <label class="wa-attach" title="Joindre image, PDF, Word ou audio">📎<input class="wa-file-input" type="file" name="attachment" id="waAttachment"></label>
                        <button class="wa-mic" type="button" id="voiceBtn" title="Enregistrer un vocal">🎙️</button>
                        <input type="file" class="wa-file-input" name="voice_note" id="voiceInput" accept="audio/*">
                        <textarea name="message" id="waTextArea" placeholder="Message" autocomplete="off"></textarea>
                        <button class="wa-send" type="submit">➤</button>
                    </form>
                    <small class="wa-file-label" id="fileNameLabel"></small>
                </footer>
            @else
                <header class="wa-chat-head"><div class="wa-avatar">💬</div><div><h3>Sélectionne une conversation</h3><small>Choisis un enseignant à gauche pour discuter.</small></div></header><div class="wa-messages"><div class="wa-empty">Aucune conversation sélectionnée.</div></div>
            @endif
        </main>
    </section>
</div>
<div class="wa-toast" id="waToast">Copié</div>

<script>
(() => {
const list=document.getElementById('waThreadList'),search=document.getElementById('waSearch');if(search&&list){search.addEventListener('input',()=>{const q=search.value.trim().toLowerCase();list.querySelectorAll('.wa-thread').forEach(i=>i.style.display=(i.dataset.name||'').includes(q)?'grid':'none')})}const messages=document.getElementById('waMessages');if(messages)messages.scrollTop=messages.scrollHeight;const toast=document.getElementById('waToast');const showToast=t=>{if(!toast)return;toast.textContent=t;toast.classList.add('show');setTimeout(()=>toast.classList.remove('show'),1500)};document.querySelectorAll('[data-copy-text]').forEach(btn=>btn.addEventListener('click',async()=>{const t=btn.dataset.copyText||'';if(!t)return showToast('Rien à copier');try{await navigator.clipboard.writeText(t);showToast('Message copié')}catch(e){showToast('Copie impossible')}}));const replyPreview=document.getElementById('replyPreview'),replyText=document.getElementById('replyText'),parentInput=document.getElementById('parentMessageId'),cancelReply=document.getElementById('cancelReply'),textArea=document.getElementById('waTextArea');document.querySelectorAll('[data-reply-id]').forEach(btn=>btn.addEventListener('click',()=>{if(!replyPreview||!replyText||!parentInput)return;parentInput.value=btn.dataset.replyId||'';replyText.textContent=btn.dataset.replyText||'Message';replyPreview.classList.add('is-visible');textArea?.focus()}));cancelReply?.addEventListener('click',()=>{if(parentInput)parentInput.value='';replyPreview?.classList.remove('is-visible')});const file=document.getElementById('waAttachment'),fileLabel=document.getElementById('fileNameLabel');file?.addEventListener('change',()=>{fileLabel.textContent=file.files?.[0]?.name?'Pièce jointe : '+file.files[0].name:''});const voiceBtn=document.getElementById('voiceBtn'),voiceInput=document.getElementById('voiceInput');let recorder=null,chunks=[];voiceBtn?.addEventListener('click',async()=>{if(!navigator.mediaDevices||!window.MediaRecorder)return showToast('Vocal non supporté sur ce navigateur');if(recorder&&recorder.state==='recording'){recorder.stop();return}try{const stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true,autoGainControl:true}});chunks=[];recorder=new MediaRecorder(stream);recorder.ondataavailable=e=>{if(e.data.size>0)chunks.push(e.data)};recorder.onstop=()=>{stream.getTracks().forEach(track=>track.stop());const blob=new Blob(chunks,{type:'audio/webm'});const f=new File([blob],'voice-note-'+Date.now()+'.webm',{type:'audio/webm'});const dt=new DataTransfer();dt.items.add(f);voiceInput.files=dt.files;voiceBtn.classList.remove('recording');voiceBtn.textContent='🎙️';fileLabel.textContent='Note vocale prête : '+f.name;showToast('Vocal prêt à envoyer')};recorder.start();voiceBtn.classList.add('recording');voiceBtn.textContent='⏹️';fileLabel.textContent='Enregistrement vocal en cours... clique encore pour arrêter'}catch(e){showToast('Micro refusé ou indisponible')}});setTimeout(()=>{if(document.visibilityState==='visible'&&!document.querySelector('textarea:focus')&&!document.querySelector('.wa-mic.recording'))window.location.reload()},45000);
})();
</script>
@endsection
