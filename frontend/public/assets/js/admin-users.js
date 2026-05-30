(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function show(el) {
    if (!el) return;
    el.classList.remove('hidden');
    el.setAttribute('aria-hidden', 'false');
  }

  function hide(el) {
    if (!el) return;
    el.classList.add('hidden');
    el.setAttribute('aria-hidden', 'true');
  }

  document.addEventListener('DOMContentLoaded', function () {
    var modal = qs('#user-modal');
    var btnNew = qs('#btn-new-user');
    var form = qs('#user-form');
    var title = qs('#user-modal-title');

    var inputCi = qs('#user-ci');
    var inputNombre = qs('#user-nombre');
    var inputPassword = qs('#user-password');
    var selectRol = qs('#user-rol');
    var selectInstituto = qs('#user-instituto');
    var checkActivo = qs('#user-activo');

    var filterForm = qs('#users-filter-form');
    var searchInput = qs('#users-search');

    function openCreate() {
      if (!form) return;
      form.action = (window.__USERS_PAGE__ && window.__USERS_PAGE__.baseUrl ? window.__USERS_PAGE__.baseUrl : '') + '/admin/usuarios/create';
      if (title) title.textContent = 'Nuevo Usuario';

      if (inputCi) inputCi.value = '';
      if (inputNombre) inputNombre.value = '';
      if (inputPassword) inputPassword.value = '';
      if (inputPassword) inputPassword.required = true;
      if (selectRol) selectRol.value = '';
      if (selectInstituto) selectInstituto.value = '';
      if (checkActivo) checkActivo.checked = true;

      show(modal);
      if (inputCi) inputCi.focus();
    }

    function openEdit(btn) {
      if (!form) return;
      var id = btn.getAttribute('data-id');
      form.action = (window.__USERS_PAGE__ && window.__USERS_PAGE__.baseUrl ? window.__USERS_PAGE__.baseUrl : '') + '/admin/usuarios/update/' + encodeURIComponent(id);
      if (title) title.textContent = 'Editar Usuario';

      if (inputCi) inputCi.value = btn.getAttribute('data-ci') || '';
      if (inputNombre) inputNombre.value = btn.getAttribute('data-nombre') || '';
      if (inputPassword) inputPassword.value = '';
      if (inputPassword) inputPassword.required = false;

      if (selectRol) {
        var rid = btn.getAttribute('data-rol-id') || '';
        selectRol.value = rid;
      }

      if (selectInstituto) {
        var iid = btn.getAttribute('data-instituto-id') || '';
        selectInstituto.value = iid;
      }

      if (checkActivo) {
        var activo = btn.getAttribute('data-activo');
        checkActivo.checked = String(activo) === '1';
      }

      show(modal);
      if (inputNombre) inputNombre.focus();
    }

    function closeModal() {
      hide(modal);
    }

    // Delete confirmation modal - using event delegation on document
    var confirmModal = null;
    var confirmMessage = null;
    var confirmCancel = null;
    var confirmAccept = null;
    var pendingDeleteId = null;

    document.addEventListener('click', function (e) {
      // Find delete button if clicked
      var deleteBtn = e.target.closest('.js-delete-user');
      if (deleteBtn) {
        e.preventDefault();
        e.stopPropagation();

        // Lazy init modal elements
        if (!confirmModal) confirmModal = qs('#confirm-modal');
        if (!confirmMessage) confirmMessage = qs('#confirm-modal-message');
        if (!confirmAccept) confirmAccept = qs('#confirm-modal-accept');

        if (!confirmModal) return;

        var userId = deleteBtn.getAttribute('data-id');
        var userName = deleteBtn.getAttribute('data-nombre') || '';
        pendingDeleteId = userId;

        if (confirmMessage) confirmMessage.textContent = '¿Estás seguro de eliminar al usuario "' + (userName || 'este usuario') + '"?';
        if (confirmAccept) confirmAccept.setAttribute('data-id', userId);
        confirmModal.classList.remove('hidden');
        confirmModal.setAttribute('aria-hidden', 'false');
        return;
      }

      // Handle confirm accept button
      if (e.target.id === 'confirm-modal-accept' || e.target.closest('#confirm-modal-accept')) {
        if (confirmAccept) {
          var userId = confirmAccept.getAttribute('data-id');
          if (userId) {
            var baseUrl = (window.__USERS_PAGE__ && window.__USERS_PAGE__.baseUrl ? window.__USERS_PAGE__.baseUrl : '');
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = baseUrl + '/admin/usuarios/delete/' + encodeURIComponent(userId);
            document.body.appendChild(form);
            form.submit();
          }
        }
        if (confirmModal) {
          confirmModal.classList.add('hidden');
          confirmModal.setAttribute('aria-hidden', 'true');
        }
        pendingDeleteId = null;
        return;
      }

      // Handle cancel / close
      if (e.target.id === 'confirm-modal-cancel' ||
          e.target.closest('#confirm-modal-cancel') ||
          (confirmModal && e.target === confirmModal.querySelector('.absolute'))) {
        if (confirmModal) {
          confirmModal.classList.add('hidden');
          confirmModal.setAttribute('aria-hidden', 'true');
        }
        pendingDeleteId = null;
        return;
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        if (confirmModal && !confirmModal.classList.contains('hidden')) {
          confirmModal.classList.add('hidden');
          confirmModal.setAttribute('aria-hidden', 'true');
          pendingDeleteId = null;
        } else {
          closeModal();
        }
      }
    });

    // Open create
    if (btnNew) {
      btnNew.addEventListener('click', function () {
        openCreate();
      });
    }

    // Open edit
    qsa('.js-edit-user').forEach(function (btn) {
      btn.addEventListener('click', function () {
        openEdit(btn);
      });
    });

    // Close modal (overlay + close buttons)
    qsa('[data-modal-close]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        closeModal();
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    });

    // Debounced submit for search
    if (filterForm && searchInput) {
      var timer = null;
      searchInput.addEventListener('input', function () {
        if (timer) clearTimeout(timer);
        timer = setTimeout(function () {
          // reset to first page
          var pageField = qs('input[name="page"]', filterForm);
          if (pageField) pageField.value = '1';
          filterForm.submit();
        }, 350);
      });
    }
  });
})();
