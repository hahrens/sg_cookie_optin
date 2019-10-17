/*
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * Details about the license can be found on the site behind the following link:
 * https://www.sgalinski.de/licenses/spl/
 */

(function() {
	var COOKIE_NAME = 'sg_cookie_optin-cookie';

	var COOKIE_GROUPS = {
		'essential': {
			'label': 'Essenziell',
			'description': 'Essenzielle Cookies ermöglichen grundlegende Funktionen und sind für die einwandfreie Funktion der Website erforderlich.',
			'required': true,
			'loadingJavaScript': 'console.log(123);',
		},
		'stats': {
			'label': 'Statistiken',
			'description': 'Statistik Cookies erfassen Informationen anonym. Diese Informationen helfen uns zu verstehen, wie unsere Besucher unsere Website nutzen.',
			'required': false,
			'loadingJavaScript': 'console.log(312);',
		},
		'stats1': {
			'label': 'Statistiken1',
			'description': 'Statistik Cookies erfassen Informationen anonym. Diese Informationen helfen uns zu verstehen, wie unsere Besucher unsere Website nutzen.',
			'required': false,
			'loadingJavaScript': 'console.log(312);',
		},
		'stats2': {
			'label': 'Statistiken2',
			'description': 'Statistik Cookies erfassen Informationen anonym. Diese Informationen helfen uns zu verstehen, wie unsere Besucher unsere Website nutzen.',
			'required': false,
			'loadingJavaScript': 'console.log(312);',
		}
	};

	var FOOTER_LINKS = {
		0: {
			'url': 'https://www.website-base.dev/datenschutz/',
			'name': 'Datenschutz'
		},
		1: {
			'url': 'https://www.website-base.dev/impressum/',
			'name': 'Impressum'
		}
	};

	/**
	 * Initializes the whole functionality.
	 */
	function initialize() {
		var cookieValue = getCookie(COOKIE_NAME);
		if (!cookieValue) {
			showCookieOptin();
			return;
		}

		var splitedCookieValue = cookieValue.split('|');
		for (var index in splitedCookieValue) {
			if (splitedCookieValue.hasOwnProperty(index)) {
				var cookieGroup = splitedCookieValue[index];
				if (COOKIE_GROUPS.hasOwnProperty(cookieGroup) && COOKIE_GROUPS[cookieGroup]['loadingJavaScript'] !== '') {
					var script = document.createElement("script");
					script.appendChild(document.createTextNode(COOKIE_GROUPS[cookieGroup]['loadingJavaScript']));
					document.body.appendChild(script);
				}
			}
		}
	}

	/**
	 * Accepts all cookies and saves them.
	 *
	 * @return {void}
	 */
	function hideAndReloadCookieOptIn() {
		document.querySelector('#SgCookieOptin').remove();
		initialize();
	}

	/**
	 * Shows the cookie optin box.
	 */
	function showCookieOptin() {
		var header = document.createElement("STRONG");
		header.classList.add("sg-cookie-optin-box-header");
		header.appendChild(document.createTextNode("Datenschutzeinstellungen"));

		var description = document.createElement("P");
		description.classList.add("sg-cookie-optin-box-description");
		description.appendChild(document.createTextNode("Wir nutzen Cookies auf unserer Website. Einige von ihnen sind essenziell, während andere uns helfen, diese Website und Ihre Erfahrung zu verbessern."));

		var cookieBox = document.createElement("DIV");
		cookieBox.classList.add("sg-cookie-optin-box");
		cookieBox.appendChild(header);
		cookieBox.appendChild(description);
		addCookieList(cookieBox);
		addAllButtons(cookieBox);
		addCookieDetails(cookieBox);
		addFooter(cookieBox);

		var wrapper = document.createElement("DIV");
		wrapper.id = 'SgCookieOptin';
		wrapper.appendChild(cookieBox);
		document.body.appendChild(wrapper);
	}

	/**
	 * Returns the cookie list DOM.
	 *
	 * @return {void}
	 */
	function addFooter(parentDOM) {
		var links = document.createElement("DIV");
		links.classList.add("sg-cookie-optin-box-footer-links");
		for (var index in FOOTER_LINKS) {
			if (FOOTER_LINKS.hasOwnProperty(index)) {
				var linkData = FOOTER_LINKS[index];
				var link = document.createElement("A");
				link.classList.add("sg-cookie-optin-box-footer-link");
				link.setAttribute('href', linkData['url']);
				link.appendChild(document.createTextNode(linkData['name']));

				if (links.childElementCount > 0) {
					var divider = document.createElement("SPAN");
					divider.classList.add("sg-cookie-optin-box-footer-divider");
					divider.appendChild(document.createTextNode('|'));
					links.appendChild(divider);
				}

				links.appendChild(link);
			}
		}

		var copyrightLink = document.createElement("A");
		copyrightLink.classList.add("sg-cookie-optin-box-copyright-link");
		copyrightLink.setAttribute('href', 'https://www.sgalinski.de/');
		copyrightLink.appendChild(document.createTextNode('© sgalinski Internet Services'));

		var copyright = document.createElement("DIV");
		copyright.classList.add("sg-cookie-optin-box-copyright");
		copyright.appendChild(copyrightLink);

		var footer = document.createElement("DIV");
		footer.classList.add("sg-cookie-optin-box-footer");
		footer.appendChild(copyright);
		footer.appendChild(links);

		parentDOM.appendChild(footer);
	}

	/**
	 * Returns the cookie list DOM.
	 *
	 * @return {void}
	 */
	function addCookieList(parentDOM) {
		var cookieList = document.createElement("UL");
		cookieList.classList.add("sg-cookie-optin-box-cookie-list");

		for (var groupName in COOKIE_GROUPS) {
			var cookieListItemCheckbox = document.createElement("INPUT");
			cookieListItemCheckbox.classList.add("sg-cookie-optin-checkbox");
			cookieListItemCheckbox.setAttribute('id', 'sg-cookie-optin-' + groupName);
			cookieListItemCheckbox.setAttribute('type', 'checkbox');
			cookieListItemCheckbox.setAttribute('name', 'cookies[]');
			cookieListItemCheckbox.setAttribute('value', groupName);

			if (COOKIE_GROUPS[groupName]['required']) {
				cookieListItemCheckbox.setAttribute('checked', '1');
				cookieListItemCheckbox.setAttribute('disabled', '1');
			}

			var cookieListItemCheckboxLabel = document.createElement("LABEL");
			cookieListItemCheckboxLabel.classList.add("sg-cookie-optin-checkbox-label");
			cookieListItemCheckboxLabel.setAttribute('for', 'sg-cookie-optin-' + groupName);
			cookieListItemCheckboxLabel.appendChild(document.createTextNode(COOKIE_GROUPS[groupName]['label']));

			var cookieListItem = document.createElement("LI");
			cookieListItem.classList.add("sg-cookie-optin-box-cookie-list-item");
			cookieListItem.appendChild(cookieListItemCheckbox);
			cookieListItem.appendChild(cookieListItemCheckboxLabel);

			cookieList.appendChild(cookieListItem);
		}

		parentDOM.appendChild(cookieList);
	}

	/**
	 * Adds the cookie buttons.
	 *
	 * @return {void}
	 */
	function addAllButtons(parentDOM) {
		var acceptAllButton = document.createElement("BUTTON");
		acceptAllButton.classList.add("sg-cookie-optin-box-button-accept-all");
		acceptAllButton.appendChild(document.createTextNode("Alle akzeptieren"));
		acceptAllButton.addEventListener("click", acceptAllCookies);

		var acceptSpecificButton = document.createElement("BUTTON");
		acceptSpecificButton.classList.add("sg-cookie-optin-box-button-accept-specific");
		acceptSpecificButton.appendChild(document.createTextNode("Speichern & schließen"));
		acceptSpecificButton.addEventListener("click", acceptSpecificCookies);

		var acceptEssentialButton = document.createElement("BUTTON");
		acceptEssentialButton.classList.add("sg-cookie-optin-box-button-accept-essential");
		acceptEssentialButton.appendChild(document.createTextNode("Nur essenzielle Cookies akzeptieren"));
		acceptEssentialButton.addEventListener("click", acceptEssentialCookies);

		parentDOM.appendChild(acceptAllButton);
		parentDOM.appendChild(acceptSpecificButton);
		parentDOM.appendChild(acceptEssentialButton);
	}

	/**
	 * Adds the cookie details.
	 *
	 * @return {void}
	 */
	function addCookieDetails(parentDOM) {
		var cookieList = document.createElement("UL");
		cookieList.classList.add("sg-cookie-optin-box-cookie-detail-list");

		for (var groupName in COOKIE_GROUPS) {
			var header = document.createElement("STRONG");
			header.classList.add("sg-cookie-optin-box-cookie-detail-header");
			header.appendChild(document.createTextNode(COOKIE_GROUPS[groupName]['label']));

			var description = document.createElement("P");
			description.classList.add("sg-cookie-optin-box-cookie-detail-description");
			description.appendChild(document.createTextNode(COOKIE_GROUPS[groupName]['description']));

			var cookieListItem = document.createElement("LI");
			cookieListItem.classList.add("sg-cookie-optin-box-cookie-detail-list-item");
			cookieListItem.appendChild(header);
			cookieListItem.appendChild(description);

			cookieList.appendChild(cookieListItem);
		}

		var openMoreLink = document.createElement("A");
		openMoreLink.classList.add("sg-cookie-optin-box-open-more-link");
		openMoreLink.setAttribute('href', '#');
		openMoreLink.appendChild(document.createTextNode('Individuelle Cookie-Einstellungen'));
		openMoreLink.addEventListener("click", openCookieDetails);

		var openMore = document.createElement("DIV");
		openMore.classList.add("sg-cookie-optin-box-open-more");
		openMore.appendChild(openMoreLink);

		parentDOM.appendChild(cookieList);
		parentDOM.appendChild(openMore);
	}

	/**
	 * Opens the cookie details box.
	 *
	 * @return {void}
	 */
	function openCookieDetails() {
		var cookieDetailList = document.querySelector('.sg-cookie-optin-box-cookie-detail-list');
		if (!cookieDetailList) {
			return;
		}

		if (cookieDetailList.classList.contains('visible')) {
			cookieDetailList.classList.remove('visible');
		} else {
			cookieDetailList.classList.add('visible');
		}
	}

	/**
	 * Accepts all cookies and saves them.
	 *
	 * @return {void}
	 */
	function acceptAllCookies() {
		var cookieData = '';
		for (var groupName in COOKIE_GROUPS) {
			cookieData += groupName + '|';
		}

		setCookie(COOKIE_NAME, cookieData, 30);
		hideAndReloadCookieOptIn();
	}

	/**
	 * Accepts specific cookies and saves them.
	 *
	 * @return {void}
	 */
	function acceptSpecificCookies() {
		var cookieData = '';
		var checkboxes = document.querySelectorAll('.sg-cookie-optin-checkbox:checked');
		for (var index = 0; index < checkboxes.length; ++index) {
			cookieData += checkboxes[index].value + '|';
		}

		setCookie(COOKIE_NAME, cookieData, 30);
		hideAndReloadCookieOptIn();
	}

	/**
	 * Accepts essential cookies and saves them.
	 *
	 * @return {void}
	 */
	function acceptEssentialCookies() {
		var cookieData = '';
		for (var groupName in COOKIE_GROUPS) {
			if (COOKIE_GROUPS[groupName]['required']) {
				cookieData += groupName + '|';
			}

		}

		setCookie(COOKIE_NAME, cookieData, 30);
		hideAndReloadCookieOptIn();
	}

	/**
	 * Returns the cookie, found with the given name, or null.
	 *
	 * @param {string} name
	 * @return {string}
	 */
	function getCookie(name) {
		var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
		return v ? v[2] : null;
	}

	/**
	 * Sets the given cookie with the given value for X days.
	 *
	 * @param {string} name
	 * @param {string} value
	 * @param {string} days
	 */
	function setCookie(name, value, days) {
		var d = new Date;
		d.setTime(d.getTime() + 24*60*60*1000*days);
		document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString();
	}

	/**
	 * Deletes a cookie, found the the given name.
	 *
	 * @param {string} name
	 */
	function deleteCookie(name) {
		setCookie(name, '', -1);
	}

	initialize();
})();
