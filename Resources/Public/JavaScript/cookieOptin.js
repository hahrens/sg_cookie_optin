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

	var iFrameObserver = null;
	var protectedIFrames = [];
	var lastOpenedIFrameId = 0;
	var jsonData = {};

	/**
	 * Initializes the whole functionality.
	 *
	 * @return {void}
	 */
	function initialize() {
		handleScriptActivations();

		var optInContentElements = document.querySelectorAll('.sg-cookie-optin-plugin-uninitialized');
		for (var index = 0; index < optInContentElements.length; ++index) {
			var optInContentElement = optInContentElements[index];
			showCookieOptin(optInContentElement, true);
			optInContentElement.classList.remove('sg-cookie-optin-plugin-uninitialized');
			optInContentElement.classList.add('sg-cookie-optin-plugin-initialized');
		}

		// noinspection EqualityComparisonWithCoercionJS
		var disableOptIn = getParameterByName('disableOptIn') == true;
		if (disableOptIn) {
			return;
		}

		// noinspection EqualityComparisonWithCoercionJS
		var showOptIn = getParameterByName('showOptIn') == true;
		var cookieValue = getCookie(COOKIE_NAME);
		if ((!cookieValue && !jsonData.settings.activate_testing_mode) || showOptIn) {
			showCookieOptin(null, false);
		}
	}

	/**
	 * Handles the scripts of the allowed cookie groups.
	 *
	 * @return {void}
	 */
	function handleScriptActivations() {
		var cookieValue = getCookie(COOKIE_NAME);
		if (!cookieValue) {
			return;
		}

		var splitedCookieValue = cookieValue.split('|');
		for (var splitedCookieValueIndex in splitedCookieValue) {
			if (!splitedCookieValue.hasOwnProperty(splitedCookieValueIndex)) {
				continue;
			}

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

			for (var groupIndex in jsonData.cookieGroups) {
				if (!jsonData.cookieGroups.hasOwnProperty(groupIndex) || jsonData.cookieGroups[groupIndex]['groupName'] !== group) {
					continue;
				}

				if (jsonData.cookieGroups[groupIndex]['loadingHTML'] !== '') {
					var head = document.getElementsByTagName('head')[0];
					if (head) {
						var range = document.createRange();
						range.selectNode(head);
						head.appendChild(range.createContextualFragment(jsonData.cookieGroups[groupIndex]['loadingHTML']));
					}
				}

				if (jsonData.cookieGroups[groupIndex]['loadingJavaScript'] !== '') {
					var script = document.createElement('script');
					script.setAttribute('src', jsonData.cookieGroups[groupIndex]['loadingJavaScript']);
					script.setAttribute('type', 'text/javascript');
					document.body.appendChild(script);
				}
			}
		}
	}

	/**
	 * Shows the cookie optin box.
	 *
	 * @param {dom} contentElement
	 * @param {bool} hideBanner
	 *
	 * @return {void}
	 */
	function showCookieOptin(contentElement, hideBanner) {
		var wrapper = document.createElement('DIV');
		wrapper.id = 'SgCookieOptin';

		if (contentElement === null && jsonData.settings.banner_enable && !hideBanner) {
			wrapper.classList.add('sg-cookie-optin-banner-wrapper');
			wrapper.insertAdjacentHTML('afterbegin', jsonData.mustacheData.banner.markup);
		} else {
			wrapper.insertAdjacentHTML('afterbegin', jsonData.mustacheData.template.markup);
		}

		addListeners(wrapper, contentElement);

		if (contentElement === null) {
			document.body.insertAdjacentElement('beforeend', wrapper);
		} else {
			contentElement.appendChild(wrapper);
		}

		setTimeout(function() {
			adjustDescriptionHeight(wrapper);
			updateCookieList();
		}, 10);
	}

	/**
	 * Adjusts the description height for each elements, for the new design.
	 *
	 * @param {dom} container
	 * @return {void}
	 */
	function adjustDescriptionHeight(container) {
		var maxHeightPerRow = [];
		var maxHeightPerRowIndex = 0;
		var descriptions = container.querySelectorAll('.sg-cookie-optin-box-new .sg-cookie-optin-checkbox-description');
		for (var index = 0; index < descriptions.length; ++index) {
			if (!(index % 4)) {
				++maxHeightPerRowIndex;
			}

			var descriptionHeight = descriptions[index].getBoundingClientRect().height;
			var maxHeight = (maxHeightPerRow[maxHeightPerRowIndex] ? maxHeightPerRow[maxHeightPerRowIndex] : 0);
			if (descriptionHeight > maxHeight) {
				maxHeightPerRow[maxHeightPerRowIndex] = descriptionHeight;
			}
		}

		maxHeightPerRowIndex = 0;
		for (index = 0; index < descriptions.length; ++index) {
			if (!(index % 4)) {
				++maxHeightPerRowIndex;
			}

			descriptions[index].style.height = maxHeightPerRow[maxHeightPerRowIndex] + 'px';
		}
	}

	/**
	 * Adjusts the description height for each elements, for the new design.
	 *
	 * @param {dom} container
	 * @return {void}
	 */
	function adjustReasonHeight(container) {
		var listItems = container.querySelectorAll('.sg-cookie-optin-box-new .sg-cookie-optin-box-cookie-list-item');
		for (var listItemIndex = 0; listItemIndex < listItems.length; ++listItemIndex) {
			var maxHeightPerRow = [];
			var maxHeightPerRowIndex = 0;

			var reasons = listItems[listItemIndex].querySelectorAll('.sg-cookie-optin-box-table-reason');
			for (var index = 0; index < reasons.length; ++index) {
				if (!(index % 3)) {
					++maxHeightPerRowIndex;
				}

				var reasonHeight = reasons[index].getBoundingClientRect().height;
				var maxHeight = (maxHeightPerRow[maxHeightPerRowIndex] ? maxHeightPerRow[maxHeightPerRowIndex] : 0);
				if (reasonHeight > maxHeight) {
					maxHeightPerRow[maxHeightPerRowIndex] = reasonHeight;
				}
			}

			maxHeightPerRowIndex = 0;
			for (index = 0; index < reasons.length; ++index) {
				if (!(index % 3)) {
					++maxHeightPerRowIndex;
				}

				reasons[index].style.height = maxHeightPerRow[maxHeightPerRowIndex] + 'px';
			}
		}
	}

	/**
	 * Adds the listeners to the given element.
	 *
	 * @param {dom} element
	 * @param {dom} contentElement
	 *
	 * @return {void}
	 */
	function addListeners(element, contentElement) {
		var closeButtons = element.querySelectorAll('.sg-cookie-optin-box-close-button');
		addEventListenerToList(closeButtons, 'click', function () {
			acceptEssentialCookies();
			updateCookieList();
			handleScriptActivations();

			if (contentElement === null) {
				hideCookieOptIn();
			}
		});

		var openMoreLinks = element.querySelectorAll('.sg-cookie-optin-box-open-more-link');
		addEventListenerToList(openMoreLinks, 'click', openCookieDetails);

		var openSubListLink = element.querySelectorAll('.sg-cookie-optin-box-sublist-open-more-link');
		addEventListenerToList(openSubListLink, 'click', openSubList);

		var acceptAllButtons = element.querySelectorAll(
			'.sg-cookie-optin-box-button-accept-all, .sg-cookie-optin-banner-button-accept'
		);
		addEventListenerToList(acceptAllButtons, 'click', function() {
			acceptAllCookies();
			updateCookieList();
			handleScriptActivations();

			if (contentElement === null) {
				hideCookieOptIn();
			}
		});

		var acceptSpecificButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-specific');
		addEventListenerToList(acceptSpecificButtons, 'click', function() {
			acceptSpecificCookies();
			updateCookieList();
			handleScriptActivations();

			if (contentElement === null) {
				hideCookieOptIn();
			}
		});

		var acceptEssentialButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-essential');
		addEventListenerToList(acceptEssentialButtons, 'click', function() {
			acceptEssentialCookies();
			updateCookieList();
			handleScriptActivations();

			if (contentElement === null) {
				hideCookieOptIn();
			}
		});

		var openSettingsButtons = element.querySelectorAll('.sg-cookie-optin-banner-button-settings');
		addEventListenerToList(openSettingsButtons, 'click', function() {
			hideCookieOptIn();
			showCookieOptin(null, true);
		});
	}

	/**
	 * Adds an event to a given node list.
	 *
	 * @param {nodeList} list
	 * @param {string} event
	 * @param {function} assignedFunction
	 *
	 * @return {void}
	 */
	function addEventListenerToList(list, event, assignedFunction) {
		for (var index = 0; index < list.length; ++index) {
			list[index].addEventListener(event, assignedFunction, false);
		}
	}

	/**
	 * Hides the cookie opt in.
	 *
	 * @return {void}
	 */
	function hideCookieOptIn() {
		// The content element cookie optins aren't removed, because querySelector gets only the first entry and it's
		// always the modular one.
		var optins = document.querySelectorAll('#SgCookieOptin');
		for (var index in optins) {
			if (!optins.hasOwnProperty(index)) {
				continue;
			}

			// Because of the IE11 no .remove();
			var optin = optins[index];
			var parentNode = optin.parentNode;
			if (!parentNode || parentNode.classList.contains('sg-cookie-optin-plugin')) {
				continue;
			}

			parentNode.removeChild(optin);
		}
	}

	/**
	 * Returns the cookie list DOM.
	 *
	 * @return {void}
	 */
	function updateCookieList() {
		var statusMap = {};
		var cookieValue = getCookie(COOKIE_NAME);
		if (cookieValue) {
			var splitedCookieValue = cookieValue.split('|');
			for (var valueIndex in splitedCookieValue) {
				if (!splitedCookieValue.hasOwnProperty(valueIndex)) {
					continue;
				}

				var splitedCookieValueEntry = splitedCookieValue[valueIndex];
				var groupAndStatus = splitedCookieValueEntry.split(':');
				if (!groupAndStatus.hasOwnProperty(0) || !groupAndStatus.hasOwnProperty(1)) {
					continue;
				}

				statusMap[groupAndStatus[0]] = parseInt(groupAndStatus[1]);
			}
		}

		for (var groupIndex in jsonData.cookieGroups) {
			if (!jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}

			var groupName = jsonData.cookieGroups[groupIndex]['groupName'];
			if (!groupName) {
				continue;
			}

			if (!statusMap.hasOwnProperty(groupName)) {
				continue;
			}

			var cookieList = document.querySelectorAll('.sg-cookie-optin-checkbox[value="' + groupName + '"]');
			for (var index = 0; index < cookieList.length; ++index) {
				cookieList[index].checked = (statusMap[groupName] === 1);
			}
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

		var cookieDetailList = openMoreElement.previousElementSibling;
		if (!cookieDetailList) {
			return;
		}

		if (cookieDetailList.classList.contains('sg-cookie-optin-visible')) {
			cookieDetailList.classList.remove('sg-cookie-optin-visible');
			event.target.innerHTML = jsonData.textEntries.extend_box_link_text;
		} else {
			cookieDetailList.classList.add('sg-cookie-optin-visible');
			event.target.innerHTML = jsonData.textEntries.extend_box_link_text_close;
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

		// todo remove redundant code.
		var height = 0;
		var cookieSubList = event.target.previousElementSibling;
		if (!cookieSubList || !cookieSubList.classList.contains('sg-cookie-optin-box-cookie-detail-sublist')) {
			var cookieOptin = event.target.closest('#SgCookieOptin');
			var cookieBoxes = cookieOptin.querySelectorAll('.sg-cookie-optin-box-new');
			for (var index in cookieBoxes) {
				if (!cookieBoxes.hasOwnProperty(index)) {
					continue;
				}

				var visible = true;
				var cookieBox = cookieBoxes[index];
				if (cookieBox.classList.contains('sg-cookie-optin-visible')) {
					visible = false;
					cookieBox.classList.remove('sg-cookie-optin-visible');
					cookieBox.classList.add('sg-cookie-optin-invisible');
					event.target.innerHTML = jsonData.textEntries.extend_table_link_text;
				} else {
					cookieBox.classList.remove('sg-cookie-optin-invisible');
					cookieBox.classList.add('sg-cookie-optin-visible');
					adjustReasonHeight(cookieOptin);
					event.target.innerHTML = jsonData.textEntries.extend_table_link_text_close;
				}

				var descriptions = cookieBox.querySelectorAll('.sg-cookie-optin-checkbox-description');
				for (var descriptionIndex in descriptions) {
					if (!descriptions.hasOwnProperty(descriptionIndex)) {
						continue;
					}

					var description = descriptions[descriptionIndex];
					if (visible) {
						description.setAttribute('data-optimal-height', description.style.height);
						description.style.height = 'auto';
					} else {
						description.style.height = description.getAttribute('data-optimal-height');
					}
				}
			}
		} else {
			if (cookieSubList.classList.contains('sg-cookie-optin-visible')) {
				cookieSubList.classList.remove('sg-cookie-optin-visible');
				cookieSubList.style.height = '';
				event.target.innerHTML = jsonData.textEntries.extend_table_link_text;
			} else {
				cookieSubList.classList.add('sg-cookie-optin-visible');
				cookieSubList.style.height = 'auto';
				height = cookieSubList.getBoundingClientRect().height + 'px';
				cookieSubList.style.height = '';
				requestAnimationFrame(function(item, style) {
					setTimeout(function() {
						item.style.height = style;
					}, 10);
				}(cookieSubList, height));

				event.target.innerHTML = jsonData.textEntries.extend_table_link_text_close;
			}
		}
	}

	/**
	 * Accepts all cookies and saves them.
	 *
	 * @return {void}
	 */
	function acceptAllCookies() {
		var cookieData = '';
		for (var index in jsonData.cookieGroups) {
			if (!jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			var groupName = jsonData.cookieGroups[index]['groupName'];
			if (!groupName) {
				continue;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + 1;
		}

		setCookie(COOKIE_NAME, cookieData, jsonData.settings.cookie_lifetime);
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
		for (var index in jsonData.cookieGroups) {
			if (!jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			var groupName = jsonData.cookieGroups[index]['groupName'];
			if (!groupName) {
				continue;
			}

			var status = 0;
			for (var subIndex = 0; subIndex < checkboxes.length; ++subIndex) {
				if (checkboxes[subIndex].value === groupName) {
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

		setCookie(COOKIE_NAME, cookieData, jsonData.settings.cookie_lifetime);

		if (jsonData.settings.iframe_enabled) {
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
		for (var index in jsonData.cookieGroups) {
			if (!jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			var groupName = jsonData.cookieGroups[index]['groupName'];
			if (!groupName) {
				continue;
			}

			var status = 0;
			if (jsonData.cookieGroups[index]['required']) {
				status = 1;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + status;
		}

		setCookie(COOKIE_NAME, cookieData, jsonData.settings.cookie_lifetime);
	}

	/**
	 * Checks if iFrames are added to the dom and replaces them with a consent, if the cookie isn't accepted, or set.
	 *
	 * @return {void}
	 */
	function checkForIFrames() {
		if (!jsonData.settings.iframe_enabled) {
			return;
		}

		// noinspection EqualityComparisonWithCoercionJS
		var showOptIn = getParameterByName('showOptIn') == true;
		if (jsonData.settings.activate_testing_mode && !showOptIn) {
			return;
		}

		if (!iFrameObserver) {
			var iframes = document.querySelectorAll('iframe');
			if (iframes.length > 0) {
				for (var iframeIndex in iframes) {
					if (!iframes.hasOwnProperty(iframeIndex)) {
						continue;
					}

					replaceIFrameWithConsent(iframes[iframeIndex]);
				}
			}
		}

		var cookieValue = getCookie(COOKIE_NAME);
		if (cookieValue) {
			// If the iframe group exists, then check the status. If 1 no observer needed, otherwise always activated.
			var splitedCookieValue = cookieValue.split('|');
			for (var splitedCookieValueIndex in splitedCookieValue) {
				if (!splitedCookieValue.hasOwnProperty(splitedCookieValueIndex)) {
					continue;
				}

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
				if (!mutationsList.hasOwnProperty(index)) {
					continue;
				}

				var mutation = mutationsList[index];
				if (mutation.type !== 'childList' || mutation.addedNodes.length <= 0) {
					continue;
				}

				for (var addedNodeIndex in mutation.addedNodes) {
					if (!mutation.addedNodes.hasOwnProperty(addedNodeIndex)) {
						continue;
					}

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

		var container = document.createElement('DIV');
		container.setAttribute('data-iframe-id', iframeId);
		container.setAttribute('data-src', iframe.src);
		container.setAttribute('style', 'height: ' + iframe.offsetHeight + 'px;');
		container.classList.add('sg-cookie-optin-iframe-consent');
		container.insertAdjacentHTML('afterbegin', jsonData.mustacheData.iframeReplacement.markup);

		var iframeConsentAccept = container.querySelectorAll('.sg-cookie-optin-iframe-consent-accept');
		addEventListenerToList(iframeConsentAccept, 'click', function() {
			acceptIFrame(iframeId)
		});

		var iframeConsentLink = container.querySelectorAll('.sg-cookie-optin-iframe-consent-link');
		addEventListenerToList(iframeConsentLink, 'click', openIFrameConsent);

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

		var wrapper = document.createElement('DIV');
		wrapper.id = 'SgCookieOptin';
		wrapper.insertAdjacentHTML('afterbegin', jsonData.mustacheData.iframe.markup);

		var flashMessageContainer = wrapper.querySelector('.sg-cookie-optin-box-flash-message');
		if (flashMessageContainer !== null) {
			var flashMessageText = iframe.getAttribute('data-consent-description');
			if (flashMessageText) {
				flashMessageContainer.appendChild(document.createTextNode(flashMessageText));
			} else {
				flashMessageContainer.remove();
			}
		}

		addIframeListeners(wrapper);

		document.body.insertAdjacentElement('beforeend', wrapper);
	}

	/**
	 * Adds the listeners to the given element.
	 *
	 * @param {dom} element
	 *
	 * @return {void}
	 */
	function addIframeListeners(element) {
		var closeButtons = element.querySelectorAll('.sg-cookie-optin-box-close-button');
		addEventListenerToList(closeButtons, 'click', hideCookieOptIn);

		var acceptAllButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-all');
		addEventListenerToList(acceptAllButtons, 'click', function() {
			acceptAllIFrames();
			updateCookieList();
			handleScriptActivations();
			hideCookieOptIn();
		});

		var acceptSpecificButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-specific');
		addEventListenerToList(acceptSpecificButtons, 'click', function() {
			acceptIFrame(lastOpenedIFrameId);
			updateCookieList();
			handleScriptActivations();
			hideCookieOptIn();
		});
	}

	/**
	 * Replaces all iFrame consent containers with the corresponding iframe and adapts the cookie for further requests.
	 *
	 * @return {void}
	 */
	function acceptAllIFrames() {
		if (jsonData.settings.iframe_enabled && iFrameObserver) {
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
			hideCookieOptIn();
			return;
		}

		var groupFound = false;
		var newCookieValue = '';
		var splitedCookieValue = cookieValue.split('|');
		for (var splitedCookieValueIndex in splitedCookieValue) {
			if (!splitedCookieValue.hasOwnProperty(splitedCookieValueIndex)) {
				continue;
			}

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

		setCookie(COOKIE_NAME, newCookieValue, jsonData.settings.cookie_lifetime);
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

	function closestPolyfill() {
		const ElementPrototype = window.Element.prototype;

		if (typeof ElementPrototype.matches !== 'function') {
			ElementPrototype.matches = ElementPrototype.msMatchesSelector || ElementPrototype.mozMatchesSelector || ElementPrototype.webkitMatchesSelector || function matches(selector) {
				let element = this;
				const elements = (element.document || element.ownerDocument).querySelectorAll(selector);
				let index = 0;

				while (elements[index] && elements[index] !== element) {
					++index;
				}

				return Boolean(elements[index]);
			};
		}

		if (typeof ElementPrototype.closest !== 'function') {
			ElementPrototype.closest = function closest(selector) {
				let element = this;

				while (element && element.nodeType === 1) {
					if (element.matches(selector)) {
						return element;
					}

					element = element.parentNode;
				}

				return null;
			};
		}
	}
	closestPolyfill();

	jsonData = JSON.parse(document.getElementById('cookieOptinData').innerHTML);
	if (jsonData) {
		// https://plainjs.com/javascript/events/running-code-when-the-document-is-ready-15/
		document.addEventListener('DOMContentLoaded', function() {
			initialize();
		});

		checkForIFrames();
	}
})();
