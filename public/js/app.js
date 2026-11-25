// Client-side validation (Bootstrap styles) and delete confirmation. Server-side validation is the source of truth.
(function () {
  'use strict';

  document.querySelectorAll('form.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        var firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
      }
      form.classList.add('was-validated');
    });
  });

  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.confirm(form.getAttribute('data-confirm'))) event.preventDefault();
    });
  });

  // Date of birth: block future dates and children over 18 before the request is sent.
  var dob = document.getElementById('date_of_birth');
  if (dob) {
    dob.addEventListener('input', function () {
      var value = new Date(dob.value);
      var today = new Date();
      var min = new Date(); min.setFullYear(today.getFullYear() - 18);
      dob.setCustomValidity(isNaN(value) || value > today || value < min ? 'Enter a valid date for a child under 18.' : '');
    });
  }

  // Auto-dismiss flash alerts
  document.querySelectorAll('.alert-dismissible').forEach(function (el) {
    setTimeout(function () { el.classList.add('d-none'); }, 5000);
  });
})();
