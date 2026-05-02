<!-- Vendor and plugin scripts -->
<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/vendors/chart.js/chart.umd.js') }}"></script>
<script src="{{ asset('assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

<!-- Core template scripts -->
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/misc.js') }}"></script>
<script src="{{ asset('assets/js/settings.js') }}"></script>
<script src="{{ asset('assets/js/todolist.js') }}"></script>
<script src="{{ asset('assets/js/jquery.cookie.js') }}"></script>
@vite(['resources/js/app.js'])

<script>
	(function () {
		document.addEventListener('click', function (event) {
			const button = event.target.closest('.btn-submit');
			if (!button || button.disabled) {
				return;
			}

			const form = button.closest('form');
			if (!form) {
				return;
			}

			const confirmMessage = button.getAttribute('data-confirm');
			if (confirmMessage && !window.confirm(confirmMessage)) {
				return;
			}

			event.preventDefault();

			if (!form.checkValidity()) {
				form.reportValidity();
				return;
			}

			if (!button.dataset.originalHtml) {
				button.dataset.originalHtml = button.innerHTML;
			}

			button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...';
			button.disabled = true;

			form.submit();
		});
	})();
</script>

