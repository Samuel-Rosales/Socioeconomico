(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function hide(el) {
    if (!el) return;
    el.classList.add('hidden');
    el.setAttribute('aria-hidden', 'true');
  }

  function formatLocalDateTime(date) {
    var pad = function (value) {
      value = String(value);
      return value.length < 2 ? '0' + value : value;
    };

    return date.getFullYear() + '-' +
      pad(date.getMonth() + 1) + '-' +
      pad(date.getDate()) + ' ' +
      pad(date.getHours()) + ':' +
      pad(date.getMinutes()) + ':' +
      pad(date.getSeconds());
  }

  document.addEventListener('DOMContentLoaded', function () {
    var modal = qs('#init-modal');
    var acceptButton = qs('#init-accept');

    function closeModal() {
      hide(modal);
    }

    function setStartDate() {
      var surveyInicio = qs('#survey-start-date');
      var startDate = formatLocalDateTime(new Date());

      if (surveyInicio && !surveyInicio.value) {
        surveyInicio.value = startDate;
      }
    }

    if (acceptButton) {
      acceptButton.addEventListener('click', function () {
        setStartDate();
        closeModal();

        var firstField = qs('#cedula');
        if (firstField) {
          firstField.focus();
        }
      });
    }
  });
})();
