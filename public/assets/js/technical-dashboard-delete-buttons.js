(() => {
    const page = document.querySelector('.technical-page');
    if (!page) return;

    const token = document.querySelector('input[name="_token"]')?.value || '';

    const makeDeleteForm = (action, label, confirmMessage) => {
        if (!action || !token) return null;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        form.dataset.autoDeleteButton = '1';
        form.addEventListener('submit', (event) => {
            if (!confirm(confirmMessage)) event.preventDefault();
        });

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_token';
        input.value = token;

        const button = document.createElement('button');
        button.type = 'submit';
        button.className = 'ts-btn ts-btn-danger';
        button.textContent = label || 'Supprimer';

        form.appendChild(input);
        form.appendChild(button);
        return form;
    };

    const appendOnce = (container, key, form) => {
        if (!container || !form || container.querySelector(`[data-auto-delete-key="${key}"]`)) return;
        form.dataset.autoDeleteKey = key;
        container.appendChild(form);
    };

    document.querySelectorAll('form[action*="/classes/"][action$="/update"]').forEach((form) => {
        const details = form.closest('details');
        const panel = details || form.parentElement;
        const action = form.action.replace('/update', '/delete');
        appendOnce(panel, 'delete-class', makeDeleteForm(action, 'Supprimer la classe', 'Supprimer cette classe technique ? La suppression sera bloquée si des données y sont déjà liées.'));
    });

    document.querySelectorAll('form[action*="/matieres/"][action$="/update"]').forEach((form) => {
        const details = form.closest('details');
        const panel = details || form.parentElement;
        const action = form.action.replace('/update', '/delete');
        appendOnce(panel, 'delete-subject', makeDeleteForm(action, 'Supprimer la matière', 'Supprimer cette matière ? La suppression sera bloquée si elle est déjà utilisée.'));
    });

    document.querySelectorAll('form[action*="/enseignants/"][action$="/toggle"]').forEach((form) => {
        const actions = form.closest('.ts-row-actions') || form.parentElement;
        const action = form.action.replace('/toggle', '/delete');
        appendOnce(actions, 'delete-teacher', makeDeleteForm(action, 'Supprimer', 'Supprimer cet enseignant ? La suppression sera bloquée s’il possède encore des affectations, cours ou TD.'));
    });

    document.querySelectorAll('form[action*="/cours/"]').forEach((form) => {
        const actions = form.closest('.ts-row-actions') || form.parentElement;
        if (!actions || actions.querySelector('[data-auto-delete-key="delete-course"]')) return;
        const action = form.action.replace(/\/(publish|archive)$/i, '/delete');
        appendOnce(actions, 'delete-course', makeDeleteForm(action, 'Supprimer', 'Supprimer ce cours technique ?'));
    });

    document.querySelectorAll('form[action*="/td/"]').forEach((form) => {
        const actions = form.closest('.ts-row-actions') || form.parentElement;
        if (!actions || actions.querySelector('[data-auto-delete-key="delete-td"]')) return;
        const action = form.action.replace(/\/(publish|archive)$/i, '/delete');
        appendOnce(actions, 'delete-td', makeDeleteForm(action, 'Supprimer', 'Supprimer ce TD technique ? La suppression sera bloquée s’il contient déjà des soumissions.'));
    });
})();
