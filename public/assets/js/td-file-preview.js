(() => {
    function bytesToSize(bytes) {
        if (!bytes && bytes !== 0) return '';
        const units = ['o', 'Ko', 'Mo', 'Go'];
        let size = bytes;
        let i = 0;
        while (size >= 1024 && i < units.length - 1) {
            size /= 1024;
            i++;
        }
        return `${size.toFixed(i === 0 ? 0 : 2)} ${units[i]}`;
    }

    function isTdUpload(input) {
        if (!input || input.type !== 'file') return false;
        const name = (input.name || '').toLowerCase();
        const id = (input.id || '').toLowerCase();
        return ['document', 'correction_document'].includes(name)
            || id.includes('td-source-file')
            || id.includes('correction')
            || id.includes('document');
    }

    function labelFor(input) {
        const name = (input.name || input.id || '').toLowerCase();
        return name.includes('correction') ? 'Aperçu du corrigé' : 'Aperçu du TD';
    }

    function ensurePreview(input) {
        if (input.dataset.previewReady === '1') return document.getElementById(input.dataset.previewTarget);

        const id = `td-preview-${Math.random().toString(36).slice(2)}`;
        const box = document.createElement('div');
        box.className = 'td-file-preview';
        box.id = id;
        box.hidden = true;
        box.innerHTML = `
            <div class="td-file-preview__head">
                <div class="td-file-preview__title">
                    <strong>${labelFor(input)}</strong>
                    <span data-preview-name>Aucun fichier sélectionné.</span>
                </div>
                <span class="td-file-preview__badge">À vérifier</span>
            </div>
            <div class="td-file-preview__body">
                <div data-preview-content class="td-file-preview__notice">Choisis un fichier pour afficher l’aperçu avant validation.</div>
                <div class="td-file-preview__actions">
                    <label class="td-file-preview__confirm">
                        <input type="checkbox" data-preview-confirm>
                        <span>J’ai vérifié l’aperçu de ce fichier</span>
                    </label>
                    <a href="#" target="_blank" rel="noopener" class="td-file-preview__open" data-preview-open hidden>Ouvrir dans un nouvel onglet</a>
                </div>
            </div>
        `;
        input.insertAdjacentElement('afterend', box);
        input.dataset.previewReady = '1';
        input.dataset.previewTarget = id;
        return box;
    }

    function showError(input, message) {
        let error = input.parentElement.querySelector('.td-file-preview-error');
        if (!error) {
            error = document.createElement('div');
            error.className = 'td-file-preview-error';
            input.parentElement.appendChild(error);
        }
        error.textContent = message;
    }

    function clearError(input) {
        const error = input.parentElement.querySelector('.td-file-preview-error');
        if (error) error.remove();
    }

    function renderPreview(input) {
        const box = ensurePreview(input);
        const file = input.files && input.files[0] ? input.files[0] : null;
        const nameEl = box.querySelector('[data-preview-name]');
        const content = box.querySelector('[data-preview-content]');
        const confirm = box.querySelector('[data-preview-confirm]');
        const open = box.querySelector('[data-preview-open]');

        clearError(input);
        confirm.checked = false;
        input.dataset.previewConfirmed = '0';

        if (!file) {
            box.hidden = true;
            content.innerHTML = 'Choisis un fichier pour afficher l’aperçu avant validation.';
            open.hidden = true;
            open.removeAttribute('href');
            return;
        }

        box.hidden = false;
        nameEl.textContent = `${file.name} — ${bytesToSize(file.size)}`;

        const previousUrl = input.dataset.previewUrl;
        if (previousUrl) URL.revokeObjectURL(previousUrl);
        const url = URL.createObjectURL(file);
        input.dataset.previewUrl = url;
        open.href = url;
        open.hidden = false;

        const type = (file.type || '').toLowerCase();
        const lower = file.name.toLowerCase();

        if (type.startsWith('image/') || /\.(png|jpe?g|webp|gif)$/i.test(lower)) {
            content.className = '';
            content.innerHTML = `<img class="td-file-preview__image" src="${url}" alt="Aperçu du fichier sélectionné">`;
            return;
        }

        if (type === 'application/pdf' || lower.endsWith('.pdf')) {
            content.className = 'td-file-preview__frame';
            content.innerHTML = `<object data="${url}" type="application/pdf"><iframe src="${url}"></iframe></object>`;
            return;
        }

        if (type.startsWith('text/') || /\.(txt|html?|rtf|csv)$/i.test(lower)) {
            const reader = new FileReader();
            reader.onload = () => {
                content.className = 'td-file-preview__text';
                content.textContent = String(reader.result || '').slice(0, 50000) || 'Le fichier texte est vide.';
            };
            reader.onerror = () => {
                content.className = 'td-file-preview__notice';
                content.textContent = 'Impossible de lire l’aperçu texte. Ouvre le fichier dans un nouvel onglet pour vérifier.';
            };
            reader.readAsText(file);
            return;
        }

        content.className = 'td-file-preview__notice';
        content.innerHTML = `
            <strong>Aperçu direct limité pour ce format.</strong><br>
            Les fichiers Word/ODT peuvent être importés, mais le navigateur ne peut pas toujours les afficher directement.
            Clique sur « Ouvrir dans un nouvel onglet » ou vérifie le fichier avant de valider.
        `;
    }

    function bindInput(input) {
        ensurePreview(input);
        input.addEventListener('change', () => renderPreview(input));
        const box = document.getElementById(input.dataset.previewTarget);
        const confirm = box ? box.querySelector('[data-preview-confirm]') : null;
        if (confirm) {
            confirm.addEventListener('change', () => {
                input.dataset.previewConfirmed = confirm.checked ? '1' : '0';
                if (confirm.checked) clearError(input);
            });
        }
    }

    function bindForm(form) {
        if (form.dataset.tdPreviewBound === '1') return;
        form.dataset.tdPreviewBound = '1';
        form.addEventListener('submit', (event) => {
            const inputs = Array.from(form.querySelectorAll('input[type="file"]')).filter(isTdUpload);
            const invalid = inputs.find((input) => input.files && input.files.length > 0 && input.dataset.previewConfirmed !== '1');
            if (invalid) {
                event.preventDefault();
                const box = ensurePreview(invalid);
                box.hidden = false;
                showError(invalid, 'Avant de valider, affiche et confirme l’aperçu du fichier sélectionné.');
                box.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }

    function init() {
        const inputs = Array.from(document.querySelectorAll('input[type="file"]')).filter(isTdUpload);
        inputs.forEach(bindInput);
        const forms = new Set(inputs.map((input) => input.closest('form')).filter(Boolean));
        forms.forEach(bindForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
