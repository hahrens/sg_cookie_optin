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
	var COOKIE_GROUP_IFRAME = 'iframes';

	// @formatter:off
	var SETTINGS = ###SETTINGS###;
	var COOKIE_GROUPS = ###COOKIE_GROUPS###;
	var FOOTER_LINKS = ###FOOTER_LINKS###;
	var TEXT_ENTRIES = ###TEXT_ENTRIES###;
	var MARKUP = ###MARKUP###;
	// @formatter:on

	var iFrameObserver = null;
	var protectedIFrames = [];
	var lastOpenedIFrameId = 0;

	/**
	 * Initializes the whole functionality.
	 *
	 * @param {boolean} ignoreShowOptInParameter
	 * @return {void}
	 */
	function initialize(ignoreShowOptInParameter) {
		var optInContentElements = document.querySelectorAll('.sg-cookie-optin-plugin-uninitialized');
		for (var index = 0; index < optInContentElements.length; ++index) {
			var optInContentElement = optInContentElements[index];
			showCookieOptin(optInContentElement);
			optInContentElement.classList.remove('sg-cookie-optin-plugin-uninitialized');
		}

		// noinspection EqualityComparisonWithCoercionJS
		var disableOptIn = getParameterByName('disableOptIn') == true;
		if (disableOptIn) {
			return;
		}

		// noinspection EqualityComparisonWithCoercionJS
		var showOptIn = getParameterByName('showOptIn') == true;
		var cookieValue = getCookie(COOKIE_NAME);
		if (!cookieValue || showOptIn && !ignoreShowOptInParameter) {
			showCookieOptin(null);
			return;
		}

		var splitedCookieValue = cookieValue.split('|');
		for (var splitedCookieValueIndex in splitedCookieValue) {
			var splitedCookieValueEntry = splitedCookieValue[splitedCookieValueIndex];
			var groupAndStatus = splitedCookieValueEntry.split(':');
			if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
				continue;
			}

			var group = groupAndStatus[0];
			var status = parseInt(groupAndStatus[1]);
			if (!status) {
				continue;
			}

			if (COOKIE_GROUPS.hasOwnProperty(group)) {
				if (COOKIE_GROUPS[group]['loadingHTML'] !== '') {
					var head = document.getElementsByTagName('head')[0];
					if (head) {
						var range = document.createRange();
						range.selectNode(head);
						head.appendChild(range.createContextualFragment(COOKIE_GROUPS[group]['loadingHTML']));
					}
				}

				if (COOKIE_GROUPS[group]['loadingJavaScript'] !== '') {
					var script = document.createElement('script');
					script.setAttribute('src', COOKIE_GROUPS[group]['loadingJavaScript']);
					script.setAttribute('type', 'text/javascript');
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
		// The content element cookie optins aren't removed, because querySelector gets only the first entry and it's
		// always the modular one.
		var optin = document.querySelector('#SgCookieOptin');
		// Because of the IE11 no .remove();
		optin.parentNode.removeChild(optin);

		initialize(true);
	}

	/**
	 * Shows the cookie optin box.
	 *
	 * @param {dom} contentElement
	 *
	 * @return {void}
	 */
	function showCookieOptin(contentElement) {
		var cookieBox = document.createElement('DIV');
		cookieBox.classList.add('sg-cookie-optin-box');

		if (contentElement === null) {
			var closeButton = document.createElement('SPAN');
			closeButton.classList.add('sg-cookie-optin-box-close-button');
			closeButton.appendChild(document.createTextNode('✕'));
			closeButton.addEventListener('click', function() {
				acceptEssentialCookies();
				hideAndReloadCookieOptIn();
			});
			cookieBox.appendChild(closeButton);
		}

		var header = document.createElement('STRONG');
		header.classList.add('sg-cookie-optin-box-header');
		header.appendChild(document.createTextNode(TEXT_ENTRIES.header));

		var description = document.createElement('P');
		description.classList.add('sg-cookie-optin-box-description');
		description.appendChild(document.createTextNode(TEXT_ENTRIES.description));

		cookieBox.appendChild(header);
		cookieBox.appendChild(description);
		cookieBox.appendChild(getCookieList());

		var container = document.createElement('DIV');
		container.classList.add('sg-cookie-optin-box-button');
		addOptInButtons(container, contentElement);
		cookieBox.appendChild(container);

		addCookieDetails(cookieBox);
		addFooter(cookieBox);

		var wrapper = document.createElement('DIV');
		wrapper.id = 'SgCookieOptin';
		// wrapper.appendChild(cookieBox);

		// @todo remove me!
		wrapper.insertAdjacentHTML('afterbegin', MARKUP);

		if (contentElement === null) {
			document.body.insertBefore(wrapper, document.body.firstChild);
		} else {
			contentElement.appendChild(wrapper);
		}
	}

	/**
	 * Returns the cookie list DOM.
	 *
	 * @return {void}
	 */
	function addFooter(parentDOM) {
		var links = document.createElement('DIV');
		links.classList.add('sg-cookie-optin-box-footer-links');
		for (var index in FOOTER_LINKS) {
			if (FOOTER_LINKS.hasOwnProperty(index)) {
				var linkData = FOOTER_LINKS[index];
				var link = document.createElement('A');
				link.classList.add('sg-cookie-optin-box-footer-link');
				link.setAttribute('href', linkData['url']);
				link.setAttribute('target', '_blank');
				link.appendChild(document.createTextNode(linkData['name']));

				if (links.childElementCount > 0) {
					var divider = document.createElement('SPAN');
					divider.classList.add('sg-cookie-optin-box-footer-divider');
					divider.appendChild(document.createTextNode(' | '));
					links.appendChild(divider);
				}

				links.appendChild(link);
			}
		}

		var lineBreak = document.createElement('BR');
		var copyrightLink = document.createElement('A');
		copyrightLink.classList.add('sg-cookie-optin-box-copyright-link');
		copyrightLink.setAttribute('href', 'https://www.sgalinski.de/typo3-produkte-webentwicklung/sgalinski-cookie-optin/');
		copyrightLink.setAttribute('target', '_blank');
		copyrightLink.appendChild(document.createTextNode('Powered by'));
		copyrightLink.appendChild(lineBreak);
		copyrightLink.appendChild(document.createTextNode('sgalinski Cookie Opt In'));

		var copyright = document.createElement('DIV');
		copyright.classList.add('sg-cookie-optin-box-copyright');
		copyright.appendChild(copyrightLink);

		var footer = document.createElement('DIV');
		footer.classList.add('sg-cookie-optin-box-footer');
		footer.appendChild(copyright);
		footer.appendChild(links);

		parentDOM.appendChild(footer);
	}

	/**
	 * Returns the cookie list DOM.
	 *
	 * @return {void}
	 */
	function getCookieList() {
		var statusMap = {};
		var cookieValue = getCookie(COOKIE_NAME);
		if (cookieValue) {
			var splitedCookieValue = cookieValue.split('|');
			for (var index in splitedCookieValue) {
				var splitedCookieValueEntry = splitedCookieValue[index];
				var groupAndStatus = splitedCookieValueEntry.split(':');
				if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
					continue;
				}

				statusMap[groupAndStatus[0]] = parseInt(groupAndStatus[1]);
			}
		}

		var cookieList = document.createElement('UL');
		cookieList.classList.add('sg-cookie-optin-box-cookie-list');
		for (var groupName in COOKIE_GROUPS) {
			var cookieListItemCheckbox = document.createElement('INPUT');
			cookieListItemCheckbox.classList.add('sg-cookie-optin-checkbox');
			cookieListItemCheckbox.setAttribute('id', 'sg-cookie-optin-' + groupName);
			cookieListItemCheckbox.setAttribute('type', 'checkbox');
			cookieListItemCheckbox.setAttribute('name', 'cookies[]');
			cookieListItemCheckbox.setAttribute('value', groupName);

			if (COOKIE_GROUPS[groupName]['required']) {
				cookieListItemCheckbox.setAttribute('checked', '1');
				cookieListItemCheckbox.setAttribute('disabled', '1');
			}

			if (statusMap.hasOwnProperty(groupName) && statusMap[groupName] === 1) {
				cookieListItemCheckbox.setAttribute('checked', '1');
			}

			var cookieListItemCheckboxLabel = document.createElement('LABEL');
			cookieListItemCheckboxLabel.classList.add('sg-cookie-optin-checkbox-label');
			cookieListItemCheckboxLabel.setAttribute('for', 'sg-cookie-optin-' + groupName);
			cookieListItemCheckboxLabel.appendChild(document.createTextNode(COOKIE_GROUPS[groupName]['label']));

			var cookieListItem = document.createElement('LI');
			cookieListItem.classList.add('sg-cookie-optin-box-cookie-list-item');
			cookieListItem.appendChild(cookieListItemCheckbox);
			cookieListItem.appendChild(cookieListItemCheckboxLabel);

			cookieList.appendChild(cookieListItem);
		}

		return cookieList;
	}

	/**
	 * Adds the cookie buttons.
	 *
	 * @param {dom} parentDOM
	 * @param {dom} contentElement
	 *
	 * @return {void}
	 */
	function addOptInButtons(parentDOM, contentElement) {
		var acceptAllButton = document.createElement('BUTTON');
		acceptAllButton.classList.add('sg-cookie-optin-box-button-accept-all');
		acceptAllButton.appendChild(document.createTextNode(TEXT_ENTRIES.accept_all_text));
		acceptAllButton.addEventListener('click', function() {
			acceptAllCookies();

			if (contentElement !== null) {
				var cookieList = contentElement.querySelector('.sg-cookie-optin-box-cookie-list');
				if (cookieList) {
					cookieList.parentNode.replaceChild(getCookieList(), cookieList);
				}
			} else {
				hideAndReloadCookieOptIn();
			}
		});

		var acceptSpecificButton = document.createElement('BUTTON');
		acceptSpecificButton.classList.add('sg-cookie-optin-box-button-accept-specific');
		acceptSpecificButton.appendChild(document.createTextNode(TEXT_ENTRIES.accept_specific_text));
		acceptSpecificButton.addEventListener('click', function() {
			acceptSpecificCookies();

			if (contentElement !== null) {
				var cookieList = contentElement.querySelector('.sg-cookie-optin-box-cookie-list');
				if (cookieList) {
					cookieList.parentNode.replaceChild(getCookieList(), cookieList);
				}
			} else {
				hideAndReloadCookieOptIn();
			}
		});

		var acceptEssentialButton = document.createElement('BUTTON');
		acceptEssentialButton.classList.add('sg-cookie-optin-box-button-accept-essential');
		acceptEssentialButton.appendChild(document.createTextNode(TEXT_ENTRIES.accept_essential_text));
		acceptEssentialButton.addEventListener('click', function() {
			acceptEssentialCookies();

			if (contentElement !== null) {
				var cookieList = contentElement.querySelector('.sg-cookie-optin-box-cookie-list');
				if (cookieList) {
					cookieList.parentNode.replaceChild(getCookieList(), cookieList);
				}
			} else {
				hideAndReloadCookieOptIn();
			}
		});

		parentDOM.appendChild(acceptAllButton);
		parentDOM.appendChild(acceptSpecificButton);
		parentDOM.appendChild(acceptEssentialButton);
	}

	/**
	 * Adds the cookie buttons.
	 *
	 * @return {void}
	 */
	function addIFrameButtons(parentDOM) {
		var acceptAllButton = document.createElement('BUTTON');
		acceptAllButton.classList.add('sg-cookie-optin-box-button-accept-all');
		acceptAllButton.appendChild(document.createTextNode(TEXT_ENTRIES.iframe_button_allow_all_text));
		acceptAllButton.addEventListener('click', function() {
			acceptAllIFrames();
			hideAndReloadCookieOptIn();
		});

		var acceptSpecificButton = document.createElement('BUTTON');
		acceptSpecificButton.classList.add('sg-cookie-optin-box-button-accept-specific');
		acceptSpecificButton.appendChild(document.createTextNode(TEXT_ENTRIES.iframe_button_allow_one_text));
		acceptSpecificButton.addEventListener('click', function() {
			acceptIFrame(lastOpenedIFrameId);
			hideAndReloadCookieOptIn();
		});

		parentDOM.appendChild(acceptAllButton);
		parentDOM.appendChild(acceptSpecificButton);
	}

	/**
	 * Adds the cookie details.
	 *
	 * @param {dom} parentDOM
	 *
	 * @return {void}
	 */
	function addCookieDetails(parentDOM) {
		var cookieList = document.createElement('UL');
		cookieList.classList.add('sg-cookie-optin-box-cookie-detail-list');

		for (var groupName in COOKIE_GROUPS) {
			if (!COOKIE_GROUPS.hasOwnProperty(groupName)) {
				continue;
			}

			var groupData = COOKIE_GROUPS[groupName];
			var header = document.createElement('STRONG');
			header.classList.add('sg-cookie-optin-box-cookie-detail-header');
			header.appendChild(document.createTextNode(groupData['label']));

			var description = document.createElement('P');
			description.classList.add('sg-cookie-optin-box-cookie-detail-description');
			description.appendChild(document.createTextNode(groupData['description']));

			var cookieListItem = document.createElement('LI');
			cookieListItem.classList.add('sg-cookie-optin-box-cookie-detail-list-item');
			cookieListItem.appendChild(header);
			cookieListItem.appendChild(description);

			if (groupData.hasOwnProperty('cookieData') && groupData['cookieData'].length > 0) {
				var cookieSublist = document.createElement('DIV');
				cookieSublist.classList.add('sg-cookie-optin-box-cookie-detail-sublist');
				addSubListTable(cookieSublist, groupData['cookieData']);

				var openSubListLink = document.createElement('A');
				openSubListLink.setAttribute('href', '#');
				openSubListLink.appendChild(document.createTextNode(TEXT_ENTRIES.extend_table_link_text));
				openSubListLink.addEventListener('click', openSubList);

				cookieListItem.appendChild(cookieSublist);
				cookieListItem.appendChild(openSubListLink);
			}

			cookieList.appendChild(cookieListItem);
		}

		var openMoreLink = document.createElement('A');
		openMoreLink.classList.add('sg-cookie-optin-box-open-more-link');
		openMoreLink.setAttribute('href', '#');
		openMoreLink.appendChild(document.createTextNode(TEXT_ENTRIES.extend_box_link_text));
		openMoreLink.addEventListener('click', openCookieDetails);

		var openMore = document.createElement('DIV');
		openMore.classList.add('sg-cookie-optin-box-open-more');
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

			var name = document.createElement('TH');
			name.appendChild(document.createTextNode(TEXT_ENTRIES.cookie_name_text));

			var nameData = document.createElement('TD');
			nameData.appendChild(document.createTextNode(cookieData[index]['Name']));

			var nameRow = document.createElement('TR');
			nameRow.appendChild(name);
			nameRow.appendChild(nameData);

			var provider = document.createElement('TH');
			provider.appendChild(document.createTextNode(TEXT_ENTRIES.cookie_provider_text));

			var providerData = document.createElement('TD');
			providerData.appendChild(document.createTextNode(cookieData[index]['Provider']));

			var providerRow = document.createElement('TR');
			providerRow.appendChild(provider);
			providerRow.appendChild(providerData);

			var purpose = document.createElement('TH');
			purpose.appendChild(document.createTextNode(TEXT_ENTRIES.cookie_purpose_text));

			var purposeData = document.createElement('TD');
			purposeData.appendChild(document.createTextNode(cookieData[index]['Purpose']));

			var purposeRow = document.createElement('TR');
			purposeRow.appendChild(purpose);
			purposeRow.appendChild(purposeData);

			var lifetime = document.createElement('TH');
			lifetime.appendChild(document.createTextNode(TEXT_ENTRIES.cookie_lifetime_text));

			var lifetimeData = document.createElement('TD');
			lifetimeData.appendChild(document.createTextNode(cookieData[index]['Lifetime']));

			var lifetimeRow = document.createElement('TR');
			lifetimeRow.appendChild(lifetime);
			lifetimeRow.appendChild(lifetimeData);

			var tbody = document.createElement('TBODY');
			tbody.appendChild(nameRow);
			tbody.appendChild(providerRow);
			tbody.appendChild(purposeRow);
			tbody.appendChild(lifetimeRow);

			var table = document.createElement('TABLE');
			table.appendChild(tbody);
			parentDom.appendChild(table);
		}
	}

	/**
	 * Opens the cookie details box.
	 *
	 * @return {void}
	 */
	function openCookieDetails(event) {
		event.preventDefault();

		var openMoreElement = event.target.parentNode;
		if (!openMoreElement) {
			return;
		}

		var cookieDetailList = openMoreElement.previousSibling;
		if (!cookieDetailList) {
			return;
		}

		if (cookieDetailList.classList.contains('visible')) {
			cookieDetailList.classList.remove('visible');
			event.target.innerHTML = TEXT_ENTRIES.extend_box_link_text;
		} else {
			cookieDetailList.classList.add('visible');
			event.target.innerHTML = TEXT_ENTRIES.extend_box_link_text_close;
		}
	}

	/**
	 * Opens the subList box.
	 *
	 * @param event
	 * @return {void}
	 */
	function openSubList(event) {
		event.preventDefault();

		var cookieList = event.target.previousSibling;
		if (!cookieList) {
			return;
		}

		if (cookieList.classList.contains('visible')) {
			cookieList.classList.remove('visible');
			event.target.innerHTML = TEXT_ENTRIES.extend_table_link_text;
		} else {
			cookieList.classList.add('visible');
			event.target.innerHTML = TEXT_ENTRIES.extend_table_link_text_close;
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

		setCookie(COOKIE_NAME, cookieData, SETTINGS.cookie_lifetime);
		acceptAllIFrames();
	}

	/**
	 * Accepts specific cookies and saves them.
	 *
	 * @return {void}
	 */
	function acceptSpecificCookies() {
		var iframeGroupFoundAndActive = false;
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

			if (groupName === COOKIE_GROUP_IFRAME && status === 1) {
				iframeGroupFoundAndActive = true;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + status;
		}

		setCookie(COOKIE_NAME, cookieData, SETTINGS.cookie_lifetime);

		if (SETTINGS.iframe_enabled) {
			if (iframeGroupFoundAndActive) {
				acceptAllIFrames();
			} else {
				checkForIFrames();
			}
		}
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

		setCookie(COOKIE_NAME, cookieData, SETTINGS.cookie_lifetime);
	}

	/**
	 * Checks if iFrames are added to the dom and replaces them with a consent, if the cookie isn't accepted, or set.
	 *
	 * @return {void}
	 */
	function checkForIFrames() {
		if (!SETTINGS.iframe_enabled) {
			return;
		}

		if (!iFrameObserver) {
			var iframes = document.querySelectorAll('iframe');
			if (iframes.length > 0) {
				for (var iframeIndex in iframes) {
					replaceIFrameWithConsent(iframes[iframeIndex]);
				}
			}
		}

		var cookieValue = getCookie(COOKIE_NAME);
		if (cookieValue) {
			// If the iframe group exists, then check the status. If 1 no observer needed, otherwise always activated.
			var splitedCookieValue = cookieValue.split('|');
			for (var splitedCookieValueIndex in splitedCookieValue) {
				var splitedCookieValueEntry = splitedCookieValue[splitedCookieValueIndex];
				var groupAndStatus = splitedCookieValueEntry.split(':');
				if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
					continue;
				}

				var group = groupAndStatus[0];
				var status = parseInt(groupAndStatus[1]);
				if (group === COOKIE_GROUP_IFRAME) {
					if (status === 1) {
						return;
					}

					break;
				}
			}
		}

		// Create an observer instance linked to the callback function
		iFrameObserver = new MutationObserver(function(mutationsList, observer) {
			// Use traditional 'for loops' for IE 11
			for (var index in mutationsList) {
				var mutation = mutationsList[index];
				if (mutation.type !== 'childList' || mutation.addedNodes.length <= 0) {
					continue;
				}

				for (var addedNodeIndex in mutation.addedNodes) {
					var addedNode = mutation.addedNodes[addedNodeIndex];
					if (addedNode.tagName === 'IFRAME') {
						replaceIFrameWithConsent(addedNode);
					}
				}
			}
		});

		// Start observing the target node for configured mutations
		iFrameObserver.observe(document, {subtree: true, childList: true});
	}

	/**
	 * Adds a consent for iFrames, which needs to be accepted.
	 *
	 * @param {dom} iframe
	 *
	 * @return {void}
	 */
	function replaceIFrameWithConsent(iframe) {
		// noinspection EqualityComparisonWithCoercionJS
		if (iframe.getAttribute('data-iframe-allow-always') == true) {
			return;
		}

		var parent = iframe.parentElement;
		if (!parent) {
			return;
		}

		if (!iframe.src || iframe.src.indexOf('chrome-extension') >= 0) {
			return;
		}

		// Got problems with the zero.
		var iframeId = protectedIFrames.length + 1;
		if (iframeId === 0) {
			iframeId = 1;
		}
		protectedIFrames[iframeId] = iframe;

		var button = document.createElement('BUTTON');
		button.appendChild(document.createTextNode(TEXT_ENTRIES.iframe_button_load_one_text));
		button.addEventListener('click', function() {
			acceptIFrame(iframeId)
		});

		var settingsLink = document.createElement('A');
		settingsLink.appendChild(document.createTextNode(TEXT_ENTRIES.iframe_open_settings_text));
		settingsLink.addEventListener('click', openIFrameConsent);

		var container = document.createElement('DIV');
		container.setAttribute('data-iframe-id', iframeId);
		container.setAttribute('data-src', iframe.src);
		container.setAttribute('style', 'height: ' + iframe.offsetHeight + 'px;');
		container.classList.add('sg-cookie-optin-iframe-consent');
		container.appendChild(button);
		container.appendChild(settingsLink);

		parent.appendChild(container);

		// Because of the IE11 no .remove();
		parent.removeChild(iframe);
	}

	/**
	 * Adds a consent for iFrames, which needs to be accepted.
	 *
	 * @return {void}
	 */
	function openIFrameConsent() {
		var parent = this.parentElement;
		if (!parent) {
			return;
		}

		var iframeId = parent.getAttribute('data-iframe-id');
		if (!iframeId) {
			return;
		}

		lastOpenedIFrameId = iframeId;
		var iframe = protectedIFrames[iframeId];
		if (!iframe) {
			return;
		}

		var closeButton = document.createElement('SPAN');
		closeButton.classList.add('sg-cookie-optin-box-close-button');
		closeButton.addEventListener('click', hideAndReloadCookieOptIn);
		closeButton.appendChild(document.createTextNode('✕'));

		var header = document.createElement('STRONG');
		header.classList.add('sg-cookie-optin-box-header');
		header.appendChild(document.createTextNode(COOKIE_GROUPS['iframes']['label']));

		var description = document.createElement('P');
		description.classList.add('sg-cookie-optin-box-description');
		description.appendChild(document.createTextNode(COOKIE_GROUPS['iframes']['description']));

		var cookieBox = document.createElement('DIV');
		cookieBox.classList.add('sg-cookie-optin-box');
		cookieBox.appendChild(closeButton);
		cookieBox.appendChild(header);
		cookieBox.appendChild(description);

		var container = document.createElement('DIV');
		container.classList.add('sg-cookie-optin-box-button');
		addIFrameButtons(container);

		var flashMessageText = iframe.getAttribute('data-consent-description');
		if (flashMessageText) {
			var flashMessage = document.createElement('P');
			flashMessage.classList.add('sg-cookie-optin-box-flash-message');
			flashMessage.appendChild(document.createTextNode(flashMessageText));

			container.appendChild(flashMessage);
		}
		cookieBox.appendChild(container);

		addFooter(cookieBox);

		var wrapper = document.createElement('DIV');
		wrapper.id = 'SgCookieOptin';
		wrapper.appendChild(cookieBox);
		document.body.insertBefore(wrapper, document.body.firstChild);
	}

	/**
	 * Replaces all iFrame consent containers with the corresponding iframe and adapts the cookie for further requests.
	 *
	 * @return {void}
	 */
	function acceptAllIFrames() {
		if (SETTINGS.iframe_enabled && iFrameObserver) {
			iFrameObserver.disconnect();
		}

		for (var index in protectedIFrames) {
			index = parseInt(index);
			if (!document.querySelector('div[data-iframe-id="' + index + '"]')) {
				continue;
			}

			acceptIFrame(index)
		}

		var cookieValue = getCookie(COOKIE_NAME);
		if (!cookieValue) {
			acceptAllCookies();
			hideAndReloadCookieOptIn();
			return;
		}

		var groupFound = false;
		var newCookieValue = '';
		var splitedCookieValue = cookieValue.split('|');
		for (var splitedCookieValueIndex in splitedCookieValue) {
			var splitedCookieValueEntry = splitedCookieValue[splitedCookieValueIndex];
			var groupAndStatus = splitedCookieValueEntry.split(':');
			if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
				continue;
			}

			var group = groupAndStatus[0];
			var status = parseInt(groupAndStatus[1]);
			if (group === COOKIE_GROUP_IFRAME) {
				groupFound = true;
				status = 1;
			}

			if (newCookieValue.length > 0) {
				newCookieValue += '|';
			}
			newCookieValue += group + ':' + status;
		}

		if (!groupFound) {
			newCookieValue += '|' + COOKIE_GROUP_IFRAME + ':' + 1;
		}

		setCookie(COOKIE_NAME, newCookieValue, SETTINGS.cookie_lifetime);
	}

	/**
	 * Replaces a iFrame consent container with the iframe.
	 *
	 * @param {int} iframeId
	 *
	 * @return {void}
	 */
	function acceptIFrame(iframeId) {
		if (!iframeId) {
			iframeId = parent.getAttribute('data-iframe-id');
			if (!iframeId) {
				return;
			}
		}

		var container = document.querySelector('div[data-iframe-id="' + iframeId + '"]');
		var iframe = protectedIFrames[iframeId];
		if (!iframe || !container) {
			return;
		}

		iframe.setAttribute('data-iframe-allow-always', 1);

		// Because of the IE11 no .replaceWith();
		var parentNode = container.parentNode;
		parentNode.removeChild(container);
		parentNode.appendChild(iframe);
	}

	/**
	 * todo Optimize the cookie handling with hasSetting, addSetting, removeSetting and use it everywhere.
	 *
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
		d.setTime(d.getTime() + 24 * 60 * 60 * 1000 * days);
		document.cookie = name + '=' + value + ';path=/;expires=' + d.toGMTString();
	}

	/**
	 * Deletes a cookie, found the the given name.
	 *
	 * @param {string} name
	 */
	function deleteCookie(name) {
		setCookie(name, '', -1);
	}

	/**
	 * Returns the value of a query parameter as a string, or null on error.
	 *
	 * @param {string} name
	 * @param {string} url
	 * @return {string|null}
	 */
	function getParameterByName(name, url) {
		if (!url) {
			url = window.location.href;
		}

		name = name.replace(/[\[\]]/g, '\\$&');
		var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
			results = regex.exec(url);
		if (!results) {
			return null;
		}

		if (!results[2]) {
			return '';
		}

		return decodeURIComponent(results[2].replace(/\+/g, ' '));
	}

	// https://plainjs.com/javascript/events/running-code-when-the-document-is-ready-15/
	document.addEventListener('DOMContentLoaded', function() {
		initialize(false);
	});

	checkForIFrames();
})();
