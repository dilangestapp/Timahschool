(function(){
    const messages = document.getElementById('wa-messages');
    if(messages){ messages.scrollTop = messages.scrollHeight; }

    const search = document.querySelector('[data-wa-search]');
    if(search){
        search.addEventListener('input', function(){
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('[data-wa-thread]').forEach(function(item){
                item.classList.toggle('wa-hidden', q && !(item.dataset.name || '').includes(q));
            });
        });
    }

    document.querySelectorAll('[data-reply-id]').forEach(function(btn){
        btn.addEventListener('click', function(){
            const field = document.querySelector('[data-reply-field]');
            const preview = document.querySelector('[data-reply-preview]');
            const previewText = document.querySelector('[data-reply-preview-text]');
            if(field){ field.value = this.dataset.replyId || ''; }
            if(previewText){ previewText.textContent = this.dataset.replyText || ''; }
            if(preview){ preview.style.display = 'block'; }
            document.querySelector('.wa-compose-text')?.focus();
        });
    });

    document.querySelector('[data-reply-cancel]')?.addEventListener('click', function(){
        const field = document.querySelector('[data-reply-field]');
        const preview = document.querySelector('[data-reply-preview]');
        if(field){ field.value = ''; }
        if(preview){ preview.style.display = 'none'; }
    });

    document.querySelectorAll('[data-copy-text]').forEach(function(btn){
        btn.addEventListener('click', function(){
            if(navigator.clipboard){ navigator.clipboard.writeText(this.dataset.copyText || ''); }
        });
    });

    let typing = false;
    const composer = document.querySelector('.wa-compose-text');
    if(composer){
        composer.addEventListener('input', function(){
            typing = true;
            clearTimeout(window.waTypingTimer);
            window.waTypingTimer = setTimeout(function(){ typing = false; }, 6000);
        });
    }

    setInterval(function(){
        if(!typing && !document.hidden){ window.location.reload(); }
    }, 25000);
})();
