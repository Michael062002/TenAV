/* TenAV Lead Form: validation and in-page sending. Without JavaScript the form still posts normally. */
(function () {
	'use strict';

	var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
	var PHONE_RE = /^\+?[\d\s().\-]{7,30}$/;
	var GENERIC_ERROR = 'Sorry, your enquiry could not be sent. Please try again, or email us directly at info@tenav.co.uk.';

	function validate(input) {
		var value = input.value.trim();
		switch (input.name) {
			case 'first_name':
				return value ? '' : 'Please enter your first name.';
			case 'last_name':
				return value ? '' : 'Please enter your last name.';
			case 'email':
				if (!value) return 'Please enter your email address.';
				return EMAIL_RE.test(value) ? '' : 'Please enter a valid email address, like name@company.co.uk.';
			case 'phone':
				if (!value) return '';
				return PHONE_RE.test(value) && value.replace(/\D/g, '').length >= 7
					? '' : 'Please enter a valid phone number.';
			case 'project':
				return value ? '' : 'Please tell us a little about your project.';
			default:
				return '';
		}
	}

	function init(root) {
		var form = root.querySelector('.tenav-lead__form');
		if (!form || !window.fetch || !window.FormData) return;

		var alertBox = root.querySelector('.tenav-lead__alert');
		var successBox = root.querySelector('.tenav-lead__success');
		var button = form.querySelector('.tenav-lead__submit');
		var buttonLabel = form.querySelector('.tenav-lead__submit-label');
		var inputs = Array.prototype.slice.call(form.querySelectorAll('.tenav-lead__field input, .tenav-lead__field textarea'));

		// We show our own messages instead of the browser's validation bubbles
		form.noValidate = true;

		function showError(input, message) {
			var errorEl = document.getElementById(input.getAttribute('aria-describedby'));
			if (errorEl) errorEl.textContent = message;
			if (message) input.setAttribute('aria-invalid', 'true');
			else input.removeAttribute('aria-invalid');
		}

		function showAlert(message, detail) {
			alertBox.textContent = message;
			if (detail) {
				var detailEl = document.createElement('span');
				detailEl.className = 'tenav-lead__alert-detail';
				detailEl.textContent = detail;
				alertBox.appendChild(detailEl);
			}
			alertBox.hidden = false;
			alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}

		function showSuccess(data) {
			var firstName = form.elements.first_name.value.trim();
			if (firstName) {
				successBox.querySelector('.tenav-lead__success-title').textContent = 'Thanks, ' + firstName + ', we\'ve got it';
			}
			if (data && data.adminNotice) {
				var note = document.createElement('p');
				note.className = 'tenav-lead__admin-note';
				note.textContent = data.adminNotice;
				successBox.appendChild(note);
			}
			form.hidden = true;
			successBox.hidden = false;
			successBox.focus();
		}

		function setSending(sending) {
			button.disabled = sending;
			button.classList.toggle('is-sending', sending);
			buttonLabel.textContent = sending ? 'Sending…' : 'Send enquiry';
		}

		// Re-check a field once the person has moved on from it, and clear errors as they fix them
		inputs.forEach(function (input) {
			input.addEventListener('blur', function () {
				if (input.value.trim() || input.hasAttribute('aria-invalid')) showError(input, validate(input));
			});
			input.addEventListener('input', function () {
				if (input.hasAttribute('aria-invalid')) showError(input, validate(input));
			});
		});

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			alertBox.hidden = true;

			var firstInvalid = null;
			inputs.forEach(function (input) {
				var message = validate(input);
				showError(input, message);
				if (message && !firstInvalid) firstInvalid = input;
			});
			if (firstInvalid) {
				firstInvalid.focus();
				return;
			}

			setSending(true);

			// getAttribute, because the hidden input named "action" shadows form.action
			fetch(form.getAttribute('action'), {
				method: 'POST',
				body: new FormData(form),
				headers: { 'Accept': 'application/json' },
				credentials: 'same-origin'
			})
				.then(function (response) {
					return response.json().catch(function () {
						var badResponse = new Error('The website returned an unexpected response (status ' + response.status + ').');
						badResponse.fromServer = true;
						throw badResponse;
					});
				})
				.then(function (result) {
					if (result && result.success) {
						showSuccess(result.data);
						return;
					}

					var data = (result && result.data) || {};
					var errors = data.errors || {};
					var firstServerInvalid = null;
					inputs.forEach(function (input) {
						if (errors[input.name]) {
							showError(input, errors[input.name]);
							if (!firstServerInvalid) firstServerInvalid = input;
						}
					});
					showAlert(data.message || GENERIC_ERROR, data.detail);
					if (firstServerInvalid) firstServerInvalid.focus();
				})
				.catch(function (error) {
					console.error('TenAV lead form submission failed:', error);
					showAlert(GENERIC_ERROR, error && error.fromServer
						? error.message
						: 'Could not connect to the website. Please check your internet connection.');
				})
				.then(function () {
					setSending(false);
				});
		});
	}

	function start() {
		Array.prototype.forEach.call(document.querySelectorAll('.tenav-lead'), init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
