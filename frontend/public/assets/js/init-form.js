(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function hide(el) {
    if (!el) return;
    el.classList.add('hidden');
    el.setAttribute('aria-hidden', 'true');
  }

  function clearStartDate() {
    var surveyInicio = qs('#survey-start-date');
    if (surveyInicio) {
      surveyInicio.value = '';
    }
  }

  function formatLocalDateTime(date) {
    var pad = function (value) {
      value = String(value);
      return value.length < 2 ? '0' + value : value;
    };

    return date.getUTCFullYear() + '-' +
      pad(date.getUTCMonth() + 1) + '-' +
      pad(date.getUTCDate()) + ' ' +
      pad(date.getUTCHours()) + ':' +
      pad(date.getUTCMinutes()) + ':' +
      pad(date.getUTCSeconds());
  }

  document.addEventListener('DOMContentLoaded', function () {
    var modal = qs('#init-modal');
    var acceptButton = qs('#init-accept');

    clearStartDate();

    window.addEventListener('pageshow', function () {
      clearStartDate();
    });

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

        var firstField = qs('#nombres');
        if (firstField) {
          firstField.focus();
        }
      });
    }
  });
})();
