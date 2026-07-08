window.addEventListener('load', () => {
	const mainDiv = document.querySelector('#form_vibes_widget-0');

	if (mainDiv === null) {
		return;
	}

	const settingDiv = document.querySelector('#fv-dashboard-settings');

	const attachGearIcon = () => {
		const headerBar = document.querySelector('#form_vibes_widget-0 .ui-sortable-handle');

		if (headerBar === null) {
			return false;
		}

		// Avoid adding duplicate icons
		if (headerBar.querySelector('.fv-dashboard-toggle-icon')) {
			return true;
		}

		const toggleIcon = document.createElement('span');
		toggleIcon.classList.add('dashicons', 'dashicons-admin-generic', 'fv-dashboard-toggle-icon');
		toggleIcon.onclick = () => {
			mainDiv.classList.toggle('closed');
			settingDiv.classList.toggle('fv-hidden');
		};
		headerBar.appendChild(toggleIcon);
		return true;
	};

	// Try immediately — works if jQuery UI sortable is already initialized
	if (attachGearIcon()) {
		return;
	}

	// Fall back to MutationObserver for when jQuery UI initializes after load
	const observer = new MutationObserver(() => {
		if (attachGearIcon()) {
			observer.disconnect();
		}
	});

	observer.observe(mainDiv, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });

	// Safety timeout — disconnect after 10s if sortable never initializes
	setTimeout(() => observer.disconnect(), 10000);
});
