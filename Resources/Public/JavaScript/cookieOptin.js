/*
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * Commercial license
 * You can buy a license key on the following site:
 * https://www.sgalinski.de/en/typo3-produkte-webentwicklung/sgalinski-cookie-optin/
 */

(function() {
	var COOKIE_NAME = 'cookie_optin';

	var COOKIE_GROUPS = ###COOKIE_GROUPS###;
	var FOOTER_LINKS = ###FOOTER_LINKS###;
	var TEXT_ENTRIES = ###TEXT_ENTRIES###;

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
			if (!splitedCookieValue.hasOwnProperty(index)) {
				continue;
			}

			var groupAndStatus = splitedCookieValue[index].split(':');
			if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
				continue;
			}

			var group = groupAndStatus[0];
			var status = parseInt(groupAndStatus[1]);
			if (!status) {
				continue;
			}

			if (COOKIE_GROUPS.hasOwnProperty(group) && COOKIE_GROUPS[group]['loadingJavaScript'] !== '') {
				var script = document.createElement("script");
				script.setAttribute('src', COOKIE_GROUPS[group]['loadingJavaScript']);
				script.setAttribute('type', 'text/javascript');
				document.body.appendChild(script);
			}
		}
	}

	/**
	 * Accepts all cookies and saves them.
	 *
	 * @return {void}
	 */
	function hideAndReloadCookieOptIn() {
		// Because of the IE11 no .remove();
		var optin = document.querySelector('#SgCookieOptin');
		optin.parentNode.removeChild(optin);

		initialize();
	}

	/**
	 * Shows the cookie optin box.
	 */
	function showCookieOptin() {
		var header = document.createElement("STRONG");
		header.classList.add("sg-cookie-optin-box-header");
		header.appendChild(document.createTextNode(TEXT_ENTRIES.header));

		var description = document.createElement("P");
		description.classList.add("sg-cookie-optin-box-description");
		description.appendChild(document.createTextNode(TEXT_ENTRIES.description));

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
				link.setAttribute('target', '_blank');
				link.appendChild(document.createTextNode(linkData['name']));

				if (links.childElementCount > 0) {
					var divider = document.createElement("SPAN");
					divider.classList.add("sg-cookie-optin-box-footer-divider");
					divider.appendChild(document.createTextNode(' | '));
					links.appendChild(divider);
				}

				links.appendChild(link);
			}
		}

		var lineBreak = document.createElement("BR");
		var copyrightLink = document.createElement("A");
		copyrightLink.classList.add("sg-cookie-optin-box-copyright-link");
		copyrightLink.setAttribute('href', 'https://www.sgalinski.de/typo3-produkte-webentwicklung/sgalinski-cookie-optin/');
		copyrightLink.setAttribute('target', '_blank');
		copyrightLink.appendChild(document.createTextNode('Powered by'));
		copyrightLink.appendChild(lineBreak);
		copyrightLink.appendChild(document.createTextNode('sgalinski Cookie Opt In'));

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
		acceptAllButton.appendChild(document.createTextNode(TEXT_ENTRIES.accept_all_text));
		acceptAllButton.addEventListener("click", acceptAllCookies);

		var acceptSpecificButton = document.createElement("BUTTON");
		acceptSpecificButton.classList.add("sg-cookie-optin-box-button-accept-specific");
		acceptSpecificButton.appendChild(document.createTextNode(TEXT_ENTRIES.accept_specific_text));
		acceptSpecificButton.addEventListener("click", acceptSpecificCookies);

		var acceptEssentialButton = document.createElement("BUTTON");
		acceptEssentialButton.classList.add("sg-cookie-optin-box-button-accept-essential");
		acceptEssentialButton.appendChild(document.createTextNode(TEXT_ENTRIES.accept_essential_text));
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
			if (!COOKIE_GROUPS.hasOwnProperty(groupName)) {
				continue;
			}

			var groupData = COOKIE_GROUPS[groupName];
			var header = document.createElement("STRONG");
			header.classList.add("sg-cookie-optin-box-cookie-detail-header");
			header.appendChild(document.createTextNode(groupData['label']));

			var description = document.createElement("P");
			description.classList.add("sg-cookie-optin-box-cookie-detail-description");
			description.appendChild(document.createTextNode(groupData['description']));

			var cookieListItem = document.createElement("LI");
			cookieListItem.classList.add("sg-cookie-optin-box-cookie-detail-list-item");
			cookieListItem.appendChild(header);
			cookieListItem.appendChild(description);

			if (groupData.hasOwnProperty('cookieData') && groupData['cookieData'].length > 0) {
				var cookieSublist = document.createElement("DIV");
				cookieSublist.classList.add("sg-cookie-optin-box-cookie-detail-sublist");
				addSubListTable(cookieSublist, groupData['cookieData']);

				var openSubListLink = document.createElement("A");
				openSubListLink.setAttribute('href', '#');
				openSubListLink.appendChild(document.createTextNode(TEXT_ENTRIES.extend_table_link_text));
				openSubListLink.addEventListener("click", openSubList);

				cookieListItem.appendChild(cookieSublist);
				cookieListItem.appendChild(openSubListLink);
			}

			cookieList.appendChild(cookieListItem);
		}

		var openMoreLink = document.createElement("A");
		openMoreLink.classList.add("sg-cookie-optin-box-open-more-link");
		openMoreLink.setAttribute('href', '#');
		openMoreLink.appendChild(document.createTextNode(TEXT_ENTRIES.extend_box_link_text));
		openMoreLink.addEventListener("click", openCookieDetails);

		var openMore = document.createElement("DIV");
		openMore.classList.add("sg-cookie-optin-box-open-more");
		openMore.appendChild(openMoreLink);

		parentDOM.appendChild(cookieList);
		parentDOM.appendChild(openMore);
	}

	/**
	 * Returns the sublist table for the cookie details.
	 *
	 * @param {dom} parentDom
	 * @param {array} cookieData
	 * @return {void}
	 */
	function addSubListTable(parentDom, cookieData) {
		if (cookieData.length <= 0) {
			return;
		}

		for (var index in cookieData) {
			if (!cookieData.hasOwnProperty(index)) {
				continue;
			}

			var tbody = document.createElement("TBODY");
			for (var headerText in cookieData[index]) {
				var header = document.createElement("TH");
				header.appendChild(document.createTextNode(headerText));

				var data = document.createElement("TD");
				data.appendChild(document.createTextNode(cookieData[index][headerText]));

				var row = document.createElement("TR");
				row.appendChild(header);
				row.appendChild(data);

				tbody.appendChild(row);
			}

			var table = document.createElement("TABLE");
			table.appendChild(tbody);
			parentDom.appendChild(table);
		}

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
	 * Opens the subList box.
	 *
	 * @param event
	 * @return {void}
	 */
	function openSubList(event) {
		var cookieList = event.target.previousSibling;
		if (!cookieList) {
			return;
		}

		if (cookieList.classList.contains('visible')) {
			cookieList.classList.remove('visible');
		} else {
			cookieList.classList.add('visible');
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
			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + 1;
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
		for (var groupName in COOKIE_GROUPS) {
			var status = 0;
			for (var index = 0; index < checkboxes.length; ++index) {
				if (checkboxes[index].value === groupName) {
					status = 1;
					break;
				}
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + status;
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
			var status = 0;
			if (COOKIE_GROUPS[groupName]['required']) {
				status = 1;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + status;
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
