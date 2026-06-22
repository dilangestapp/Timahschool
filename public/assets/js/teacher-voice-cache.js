(() => {
    const audios = Array.from(document.querySelectorAll('audio.msg-audio[src]'));
    if (!audios.length) return;

    const supportsCache = 'caches' in window && window.isSecureContext;
    const cacheName = 'timah-teacher-voices-v1';

    const toast = (text) => {
        const existing = document.getElementById('msgToast');
        if (existing) {
            existing.textContent = text;
            existing.classList.add('show');
            setTimeout(() => existing.classList.remove('show'), 1700);
            return;
        }
        console.log(text);
    };

    const cacheVoice = async (url) => {
        if (!supportsCache) {
            window.open(url + (url.includes('?') ? '&' : '?') + 'download=1', '_blank');
            toast('Téléchargement lancé.');
            return;
        }
        const cache = await caches.open(cacheName);
        const response = await fetch(url, { credentials: 'same-origin' });
        if (!response.ok) throw new Error('download_failed');
        await cache.put(url, response.clone());
        toast('Vocal enregistré pour écoute hors ligne sur ce navigateur.');
    };

    audios.forEach((audio) => {
        audio.preload = 'auto';
        const url = audio.getAttribute('src');
        const holder = audio.closest('.msg-bubble') || audio.parentElement;
        if (!holder || holder.querySelector('[data-cache-voice]')) return;

        const actions = document.createElement('div');
        actions.className = 'msg-tools';

        const cacheButton = document.createElement('button');
        cacheButton.type = 'button';
        cacheButton.className = 'msg-tool';
        cacheButton.dataset.cacheVoice = '1';
        cacheButton.textContent = 'Écouter hors ligne';
        cacheButton.addEventListener('click', async () => {
            try {
                await cacheVoice(url);
            } catch (error) {
                toast('Impossible de sauvegarder ce vocal.');
            }
        });

        const downloadLink = document.createElement('a');
        downloadLink.className = 'msg-tool';
        downloadLink.href = url + (url.includes('?') ? '&' : '?') + 'download=1';
        downloadLink.textContent = 'Télécharger';
        downloadLink.style.textDecoration = 'none';

        actions.appendChild(cacheButton);
        actions.appendChild(downloadLink);
        holder.appendChild(actions);
    });
})();
