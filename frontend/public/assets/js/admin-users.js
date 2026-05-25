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
