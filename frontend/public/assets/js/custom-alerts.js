// custom-alerts.js - Sistema de alertas y confirmaciones personalizadas

function showAlertModal(message, title) {
    return new Promise(resolve => {
        const modal = document.getElementById('alert-modal');
        const modalMessage = document.getElementById('alert-modal-message');
        const modalTitle = document.getElementById('alert-modal-title');
        const acceptBtn = document.getElementById('alert-modal-accept');

        if (!modal || !modalMessage || !acceptBtn) {
            alert(message);
            resolve();
            return;
        }

        modalMessage.textContent = message;
        if (title) modalTitle.textContent = title;

        modal.classList.remove('hidden');

        function close() {
            modal.classList.add('hidden');
            acceptBtn.removeEventListener('click', close);
            resolve();
        }

        acceptBtn.addEventListener('click', close);
    });
}

function showConfirmModal(message, title) {
    return new Promise(resolve => {
        const modal = document.getElementById('confirm-modal');
        const modalMessage = document.getElementById('confirm-modal-message');
        const modalTitle = document.getElementById('confirm-modal-title');
        const cancelBtn = document.getElementById('confirm-modal-cancel');
        const acceptBtn = document.getElementById('confirm-modal-accept');

        if (!modal || !modalMessage || !cancelBtn || !acceptBtn) {
            resolve(confirm(message));
            return;
        }

        modalMessage.textContent = message;
        if (title) modalTitle.textContent = title;

        modal.classList.remove('hidden');

        function close(result) {
            modal.classList.add('hidden');
            cancelBtn.removeEventListener('click', onCancel);
            acceptBtn.removeEventListener('click', onAccept);
            resolve(result);
        }

        function onCancel() { close(false); }
        function onAccept() { close(true); }

        cancelBtn.addEventListener('click', onCancel);
        acceptBtn.addEventListener('click', onAccept);
    });
}
