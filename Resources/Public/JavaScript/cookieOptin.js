/*
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * Commercial license
 * You can buy a license key on the following site:
 * https://www.sgalinski.de/en/typo3-produkte-webentwicklung/sgalinski-cookie-optin/
 */

var SgCookieOptin = {

	COOKIE_NAME: 'cookie_optin',
	COOKIE_GROUP_EXTERNAL_CONTENT: 'iframes',
	COOKIE_GROUP_ESSENTIAL: 'essential',

	/**
	 * The CSS selector to match possible external content
	 * @type {string}
	 */
	EXTERNAL_CONTENT_ELEMENT_SELECTOR: 'iframe, object, video, audio, [data-external-content-protection], .frame-external-content-protection',
	/**
	 * The CSS selector to match whitelisted external content
	 * @type {string}
	 */
	EXTERNAL_CONTENT_ALLOWED_ELEMENT_SELECTOR: '[data-external-content-no-protection], [data-iframe-allow-always], .frame-external-content-no-protection',

	externalContentObserver: null,
	protectedExternalContents: [],
	lastOpenedExternalContentId: 0,
	jsonData: {},

	/**
	 * Executes the script
	 */
	run: function() {
		SgCookieOptin.closestPolyfill();

		SgCookieOptin.jsonData = JSON.parse(document.getElementById('cookieOptinData').innerHTML);
		if (SgCookieOptin.jsonData) {
			// https://plainjs.com/javascript/events/running-code-when-the-document-is-ready-15/
			document.addEventListener('DOMContentLoaded', function() {
				SgCookieOptin.initialize();
			});

			SgCookieOptin.checkForExternalContents();
		}
	},

	/**
	 * Initializes the whole functionality.
	 *
	 * @return {void}
	 */
	initialize: function() {
		SgCookieOptin.handleScriptActivations();

		var optInContentElements = document.querySelectorAll('.sg-cookie-optin-plugin-uninitialized');
		for (var index = 0; index < optInContentElements.length; ++index) {
			var optInContentElement = optInContentElements[index];
			SgCookieOptin.openCookieOptin(optInContentElement, {hideBanner: true});
			optInContentElement.classList.remove('sg-cookie-optin-plugin-uninitialized');
			optInContentElement.classList.add('sg-cookie-optin-plugin-initialized');
		}

		// noinspection EqualityComparisonWithCoercionJS
		var disableOptIn = SgCookieOptin.getParameterByName('disableOptIn') == true;
		if (disableOptIn) {
			return;
		}

		// noinspection EqualityComparisonWithCoercionJS
		var showOptIn = SgCookieOptin.getParameterByName('showOptIn') == true;
		var cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		if ((!cookieValue && !SgCookieOptin.jsonData.settings.activate_testing_mode) || showOptIn) {
			SgCookieOptin.openCookieOptin(null, {hideBanner: false});
		}
	},

	/**
	 * Handles the scripts of the allowed cookie groups.
	 *
	 * @return {void}
	 */
	handleScriptActivations: function() {
		var cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
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

			for (var groupIndex in SgCookieOptin.jsonData.cookieGroups) {
				if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex) || SgCookieOptin.jsonData.cookieGroups[groupIndex]['groupName'] !== group) {
					continue;
				}

				if (
					SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingHTML'] &&
					SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingHTML'] !== ''
				) {
					var head = document.getElementsByTagName('head')[0];
					if (head) {
						var range = document.createRange();
						range.selectNode(head);
						head.appendChild(range.createContextualFragment(SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingHTML']));
					}
				}

				if (
					SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingJavaScript'] &&
					SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingJavaScript'] !== ''
				) {
					var script = document.createElement('script');
					script.setAttribute('src', SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingJavaScript']);
					script.setAttribute('type', 'text/javascript');
					document.body.appendChild(script);
				}
			}
		}
	},

	/**
	 * Opens the cookie optin box.
	 *
	 * Supported options:
	 * {boolean} hideBanner Whether to show the cookie banner or not, if it's enabled
	 *
	 * @param {dom} contentElement
	 * @param {object} options
	 *
	 * @return {void}
	 */
	openCookieOptin: function(contentElement, options) {
		var hideBanner = typeof options == 'object' && options.hideBanner === true;
		var wrapper = document.createElement('DIV');
		wrapper.id = 'SgCookieOptin';

		if (!contentElement && SgCookieOptin.jsonData.settings.banner_enable && !hideBanner) {
			wrapper.classList.add('sg-cookie-optin-banner-wrapper');
			wrapper.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.banner.markup);
		} else {
			wrapper.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.template.markup);
		}

		SgCookieOptin.addListeners(wrapper, contentElement);

		if (!contentElement) {
			document.body.insertAdjacentElement('beforeend', wrapper);
		} else {
			contentElement.appendChild(wrapper);
		}

		setTimeout(function() {
			SgCookieOptin.adjustDescriptionHeight(wrapper, contentElement);
			SgCookieOptin.updateCookieList();
		}, 10);
	},

	/**
	 * Adjusts the description height for each elements, for the new design.
	 *
	 * @param {dom} container
	 * @param {dom} contentElement
	 * @return {void}
	 */
	adjustDescriptionHeight: function(container, contentElement) {
		var columnsPerRow = 4;
		if (contentElement === null) {
			if (window.innerWidth <= 750) {
				return;
			} else if (window.innerWidth <= 1050) {
				columnsPerRow = 2;
			} else if (window.innerWidth <= 1250) {
				columnsPerRow = 3;
			}
		} else {
			columnsPerRow = 2;
		}

		var maxHeightPerRow = [];
		var maxHeightPerRowIndex = 0;
		var descriptions = container.querySelectorAll('.sg-cookie-optin-box-new .sg-cookie-optin-checkbox-description');
		for (var index = 0; index < descriptions.length; ++index) {
			if (!(index % columnsPerRow)) {
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
			if (!(index % columnsPerRow)) {
				++maxHeightPerRowIndex;
			}

			descriptions[index].style.height = maxHeightPerRow[maxHeightPerRowIndex] + 'px';
		}
	},

	/**
	 * Adjusts the description height for each elements, for the new design.
	 *
	 * @param {dom} container
	 * @param {dom} contentElement
	 * @return {void}
	 */
	adjustReasonHeight: function(container, contentElement) {
		var columnsPerRow = 3;
		if (contentElement === null) {
			if (window.innerWidth <= 750) {
				return;
			} else if (window.innerWidth <= 1050) {
				columnsPerRow = 2;
			}
		} else {
			columnsPerRow = 2;
		}

		var listItems = container.querySelectorAll('.sg-cookie-optin-box-new .sg-cookie-optin-box-cookie-list-item');
		for (var listItemIndex = 0; listItemIndex < listItems.length; ++listItemIndex) {
			var maxHeightPerRow = [];
			var maxHeightPerRowIndex = 0;

			var reasons = listItems[listItemIndex].querySelectorAll('.sg-cookie-optin-box-table-reason');
			for (var index = 0; index < reasons.length; ++index) {
				if (!(index % columnsPerRow)) {
					++maxHeightPerRowIndex;
				}

				var reasonHeight = reasons[index].getBoundingClientRect().height;
				reasonHeight -= parseInt(window.getComputedStyle(reasons[index], null).getPropertyValue('padding-top'));
				reasonHeight -= parseInt(window.getComputedStyle(reasons[index], null).getPropertyValue('padding-bottom'));
				var maxHeight = (maxHeightPerRow[maxHeightPerRowIndex] ? maxHeightPerRow[maxHeightPerRowIndex] : 0);
				if (reasonHeight > maxHeight) {
					maxHeightPerRow[maxHeightPerRowIndex] = reasonHeight;
				}
			}

			maxHeightPerRowIndex = 0;
			for (index = 0; index < reasons.length; ++index) {
				if (!(index % columnsPerRow)) {
					++maxHeightPerRowIndex;
				}

				reasons[index].style.height = maxHeightPerRow[maxHeightPerRowIndex] + 'px';
			}
		}
	},

	/**
	 * Adds the listeners to the given element.
	 *
	 * @param {dom} element
	 * @param {dom} contentElement
	 *
	 * @return {void}
	 */
	addListeners: function(element, contentElement) {
		var closeButtons = element.querySelectorAll('.sg-cookie-optin-box-close-button');
		SgCookieOptin.addEventListenerToList(closeButtons, 'click', function () {
			SgCookieOptin.acceptEssentialCookies();
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();

			if (!contentElement) {
				SgCookieOptin.hideCookieOptIn();
			}
		});

		var openMoreLinks = element.querySelectorAll('.sg-cookie-optin-box-open-more-link');
		SgCookieOptin.addEventListenerToList(openMoreLinks, 'click', SgCookieOptin.openCookieDetails);

		var openSubListLink = element.querySelectorAll('.sg-cookie-optin-box-sublist-open-more-link');
		SgCookieOptin.addEventListenerToList(openSubListLink, 'click', function (event) {
			SgCookieOptin.openSubList(event, contentElement);
		});

		var acceptAllButtons = element.querySelectorAll(
			'.sg-cookie-optin-box-button-accept-all, .sg-cookie-optin-banner-button-accept'
		);
		SgCookieOptin.addEventListenerToList(acceptAllButtons, 'click', function() {
			SgCookieOptin.acceptAllCookies();
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();

			if (!contentElement) {
				SgCookieOptin.hideCookieOptIn();
			} else {
				SgCookieOptin.showSaveConfirmation(contentElement);
			}
		});

		var acceptSpecificButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-specific');
		SgCookieOptin.addEventListenerToList(acceptSpecificButtons, 'click', function() {
			SgCookieOptin.acceptSpecificCookies(this);
			SgCookieOptin.updateCookieList(this);
			SgCookieOptin.handleScriptActivations();

			if (!contentElement) {
				SgCookieOptin.hideCookieOptIn();
			} else {
				SgCookieOptin.showSaveConfirmation(contentElement);
			}
		});

		var acceptEssentialButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-essential');
		SgCookieOptin.addEventListenerToList(acceptEssentialButtons, 'click', function() {
			SgCookieOptin.acceptEssentialCookies();
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();

			if (!contentElement) {
				SgCookieOptin.hideCookieOptIn();
			}  else {
				SgCookieOptin.showSaveConfirmation(contentElement);
			}
		});

		var openSettingsButtons = element.querySelectorAll('.sg-cookie-optin-banner-button-settings');
		SgCookieOptin.addEventListenerToList(openSettingsButtons, 'click', function() {
			SgCookieOptin.hideCookieOptIn();
			SgCookieOptin.openCookieOptin(null, {hideBanner: true});
		});
	},

	/**
	 * Adds an event to a given node list.
	 *
	 * @param {nodeList} list
	 * @param {string} event
	 * @param {function} assignedFunction
	 *
	 * @return {void}
	 */
	addEventListenerToList: function(list, event, assignedFunction) {
		for (var index = 0; index < list.length; ++index) {
			list[index].addEventListener(event, assignedFunction, false);
		}
	},

	/**
	 * Hides the cookie opt in.
	 *
	 * @return {void}
	 */
	hideCookieOptIn: function() {
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
	},

	/**
	 * Returns the cookie list DOM.
	 *
	 * @param {dom} triggerElement
	 *
	 * @return {void}
	 */
	updateCookieList: function(triggerElement) {
		var statusMap = {};
		var cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
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

		for (var groupIndex in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}

			var groupName = SgCookieOptin.jsonData.cookieGroups[groupIndex]['groupName'];
			if (!groupName) {
				continue;
			}

			if (!statusMap.hasOwnProperty(groupName)) {
				continue;
			}

			if (!triggerElement) {
				var checkBoxesContainer = document;
			} else {
				var checkBoxesContainer = triggerElement.closest('.sg-cookie-optin-box');
			}

			// fallback to document if not found
			if (!checkBoxesContainer) {
				checkBoxesContainer = document;
			}
			var cookieList = checkBoxesContainer.querySelectorAll('.sg-cookie-optin-checkbox[value="' + groupName + '"]');
			for (var index = 0; index < cookieList.length; ++index) {
				cookieList[index].checked = (statusMap[groupName] === 1);
			}
		}
	},

	/**
	 * Opens the cookie details box.
	 *
	 * @param {event} event
	 * @return {void}
	 */
	openCookieDetails: function(event) {
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
			event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_box_link_text;
		} else {
			cookieDetailList.classList.add('sg-cookie-optin-visible');
			event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_box_link_text_close;
		}
	},

	/**
	 * Opens the subList box.
	 *
	 * @param event
	 * @param {dom} contentElement
	 * @return {void}
	 */
	openSubList: function(event, contentElement) {
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
					event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_table_link_text;
				} else {
					cookieBox.classList.remove('sg-cookie-optin-invisible');
					cookieBox.classList.add('sg-cookie-optin-visible');
					SgCookieOptin.adjustReasonHeight(cookieOptin, contentElement);
					event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_table_link_text_close;
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
				event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_table_link_text;
			} else {
				cookieSubList.classList.add('sg-cookie-optin-visible');
				cookieSubList.style.height = 'auto';
				height = cookieSubList.getBoundingClientRect().height + 'px';
				cookieSubList.style.height = '';
				requestAnimationFrame(function() {
					setTimeout(function() {
						cookieSubList.style.height = height;
					}, 10);
				});

				event.target.innerHTML = SgCookieOptin.jsonData.textEntries.extend_table_link_text_close;
			}
		}
	},

	/**
	 * Accepts all cookies and saves them.
	 *
	 * @return {void}
	 */
	acceptAllCookies: function() {
		var cookieData = '';
		for (var index in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			var groupName = SgCookieOptin.jsonData.cookieGroups[index]['groupName'];
			if (!groupName) {
				continue;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + 1;
		}

		SgCookieOptin.setCookie(SgCookieOptin.COOKIE_NAME, cookieData, SgCookieOptin.jsonData.settings.cookie_lifetime);
		SgCookieOptin.acceptAllExternalContents();
	},

	/**
	 * Accepts specific cookies and saves them.
	 *
	 * @param {dom} triggerElement
	 * @return {void}
	 */
	acceptSpecificCookies: function(triggerElement) {
		var externalContentGroupFoundAndActive = false;
		var cookieData = '';
		var checkBoxesContainer = null;
		if (!triggerElement) {
			checkBoxesContainer = document;
		} else {
			checkBoxesContainer = triggerElement.closest('.sg-cookie-optin-box');
		}
		var checkboxes = checkBoxesContainer.querySelectorAll('.sg-cookie-optin-checkbox:checked');
		for (var index in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			var groupName = SgCookieOptin.jsonData.cookieGroups[index]['groupName'];
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

			if (groupName === SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT && status === 1) {
				externalContentGroupFoundAndActive = true;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + status;
		}

		SgCookieOptin.setCookie(SgCookieOptin.COOKIE_NAME, cookieData, SgCookieOptin.jsonData.settings.cookie_lifetime);

		if (SgCookieOptin.jsonData.settings.iframe_enabled) {
			if (externalContentGroupFoundAndActive) {
				SgCookieOptin.acceptAllExternalContents();
			} else {
				SgCookieOptin.checkForExternalContents();
			}
		}
	},

	/**
	 * Accepts essential cookies and saves them.
	 *
	 * @return {void}
	 */
	acceptEssentialCookies: function() {
		var cookieData = '';
		for (var index in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(index)) {
				continue;
			}

			var groupName = SgCookieOptin.jsonData.cookieGroups[index]['groupName'];
			if (!groupName) {
				continue;
			}

			var status = 0;
			if (SgCookieOptin.jsonData.cookieGroups[index]['required']) {
				status = 1;
			}

			if (cookieData.length > 0) {
				cookieData += '|';
			}
			cookieData += groupName + ':' + status;
		}

		SgCookieOptin.setCookieWrapper(cookieData);
	},

	/**
	 * Checks if external content elements are added to the dom and replaces them with a consent, if the cookie isn't accepted, or set.
	 *
	 * @return {void}
	 */
	checkForExternalContents: function() {
		if (!SgCookieOptin.jsonData.settings.iframe_enabled) {
			return;
		}

		// noinspection EqualityComparisonWithCoercionJS
		var showOptIn = SgCookieOptin.getParameterByName('showOptIn') == true;
		if (SgCookieOptin.jsonData.settings.activate_testing_mode && !showOptIn) {
			return;
		}

		if (!SgCookieOptin.externalContentObserver) {
			var externalContents = document.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR);
			if (externalContents.length > 0) {
				for (var externalContentIndex in externalContents) {
					if (!externalContents.hasOwnProperty(externalContentIndex)) {
						continue;
					}

					SgCookieOptin.replaceExternalContentWithConsent(externalContents[externalContentIndex]);
				}
			}
		}

		var cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		if (cookieValue) {
			// If the external content group exists, then check the status. If 1 no observer needed, otherwise always activated.
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
				if (group === SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT) {
					if (status === 1) {
						return;
					}

					break;
				}
			}
		}

		// Create an observer instance linked to the callback function
		SgCookieOptin.externalContentObserver = new MutationObserver(function(mutationsList, observer) {
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
					if (typeof addedNode.matches === 'function' && addedNode.matches(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR)) {
						SgCookieOptin.replaceExternalContentWithConsent(addedNode);
					} else if (addedNode.querySelectorAll && typeof addedNode.querySelectorAll === 'function') {
						// check if there is an external content in the subtree
						var externalContents = addedNode.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR);
						if (externalContents.length > 0) {
							for (var externalContentIndex in externalContents) {
								if (!externalContents.hasOwnProperty(externalContentIndex)) {
									continue;
								}
								SgCookieOptin.replaceExternalContentWithConsent(externalContents[externalContentIndex]);
							}
						}
					}
				}
			}
		});

		// Start observing the target node for configured mutations
		SgCookieOptin.externalContentObserver.observe(document, {subtree: true, childList: true});
	},

	/**
	 * Checks whether this element matches the rules in the whitelist
	 *
	 * @param {dom} externalContent
	 * @return {boolean}
	 */
	isContentWhiteListed: function(externalContent) {
		var regularExpressions = SgCookieOptin.jsonData.mustacheData.iframeWhitelist.markup.trim()
			.split(/\r?\n/).map(function (value) {
				return new RegExp(value);
			});
		if (typeof regularExpressions === 'object' && regularExpressions.length < 1) {
			return false;
		}

		switch (externalContent.tagName) {
			case 'IFRAME':
				return SgCookieOptin.isElementWhitelisted(externalContent, 'src', regularExpressions);
			case 'OBJECT':
				return SgCookieOptin.isElementWhitelisted(externalContent, 'data', regularExpressions);
			case 'VIDEO':
			case 'AUDIO':
				return SgCookieOptin.isAudioVideoWhitelisted(externalContent, regularExpressions);
			default:
				return false;
		}
	},

	/**
	 * Tests whether a dom element attribute is whitelisted
	 *
	 * @param {dom} externalContent
	 * @param {string} attribute
	 * @param {RegExp[]} regularExpressions
	 * @return {boolean}
	 */
	isElementWhitelisted: function(externalContent, attribute, regularExpressions) {
		if (typeof externalContent.getAttribute !== 'function') {
			return false;
		}

		for (var regExIndex in regularExpressions) {
			if (typeof externalContent.getAttribute === 'function' && regularExpressions[regExIndex].test(externalContent.getAttribute(attribute))) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Tests if an audio or video element is whitelisted
	 *
	 * @param {dom} externalContent
	 * @param {RegExp[]} regularExpressions
	 * @return {boolean}
	 */
	isAudioVideoWhitelisted: function(externalContent, regularExpressions) {
		if (externalContent.hasAttribute('src')) {
			return SgCookieOptin.isElementWhitelisted(externalContent, 'src', regularExpressions);
		}

		var sources = externalContent.querySelectorAll('source');
		for (var sourceIndex in sources) {
			// noinspection JSUnfilteredForInLoop
			if (SgCookieOptin.isElementWhitelisted(sources[sourceIndex], 'src', regularExpressions)) {
				return true;
			}
		}

		return false;
	},

	/**
	 * Checks whether the given element is in a container that is allowed to always render external content
	 * @param {dom} externalContent
	 * @returns {boolean}
	 */
	isElementInAllowedNode: function(externalContent) {
		var potentialParents = document.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ALLOWED_ELEMENT_SELECTOR);
		for (i in potentialParents) {
			if (typeof potentialParents[i].contains === 'function' && potentialParents[i].contains(externalContent)) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Adds a consent for externalContents, which needs to be accepted.
	 *
	 * @param {dom} externalContent
	 *
	 * @return {void}
	 */
	replaceExternalContentWithConsent: function(externalContent) {
		// Skip allowed elements and whitelisted sources
		// noinspection EqualityComparisonWithCoercionJS
		if (externalContent.matches(SgCookieOptin.EXTERNAL_CONTENT_ALLOWED_ELEMENT_SELECTOR)
			|| SgCookieOptin.isElementInAllowedNode(externalContent)
			|| SgCookieOptin.isContentWhiteListed(externalContent)) {
			return;
		}

		// Skip detached elements
		var parent = externalContent.parentElement;
		if (!parent) {
			return;
		}

		// Skip iframes with no source
		if (externalContent.tagName === 'IFRAME'
			&& (!externalContent.src || externalContent.src.indexOf('chrome-extension') >= 0)) {
			return;
		}

		// Get the position of the element within its parent
		var positionIndex = 0;
		var child = externalContent;
		while( (child = child.previousSibling) != null ) {
			positionIndex++;
		}
		externalContent.setAttribute('data-iframe-position-index', positionIndex);

		// Got problems with the zero.
		var externalContentId = SgCookieOptin.protectedExternalContents.length + 1;
		if (externalContentId === 0) {
			externalContentId = 1;
		}
		SgCookieOptin.protectedExternalContents[externalContentId] = externalContent;

		// Create the external content consent box DIV container
		var container = document.createElement('DIV');
		container.setAttribute('data-iframe-id', externalContentId);
		container.setAttribute('data-src', externalContent.src);
		container.setAttribute('style', 'height: ' + externalContent.offsetHeight + 'px;');
		container.classList.add('sg-cookie-optin-iframe-consent');
		container.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.iframeReplacement.markup);

		// Add event Listeners to the consent buttons
		var externalContentConsentAccept = container.querySelectorAll('.sg-cookie-optin-iframe-consent-accept');
		SgCookieOptin.addEventListenerToList(externalContentConsentAccept, 'click', function() {
			SgCookieOptin.acceptExternalContent(externalContentId)
		});

		var externalContentConsentLink = container.querySelectorAll('.sg-cookie-optin-iframe-consent-link');
		SgCookieOptin.addEventListenerToList(externalContentConsentLink, 'click', SgCookieOptin.openExternalContentConsent);

		// Replace the element
		if (parent.childNodes.length > 0) {
			parent.insertBefore(container, parent.childNodes[positionIndex]);
		} else {
			parent.appendChild(container);
		}

		// Accept child nodes as well when it is accepted
		externalContent.addEventListener('externalContentAccepted', function() {
			SgCookieOptin.acceptContainerChildNodes(externalContent);
			return true;
		});

		// Because of the IE11 no .remove();
		parent.removeChild(externalContent);

		// Emit event for replaced content
		var externalContentReplacedEvent = new CustomEvent('externalContentReplaced', {
			bubbles: true,
			detail: {
				positionIndex: positionIndex,
				parent: parent,
				externalContent: externalContent
			}
		});
		container.dispatchEvent(externalContentReplacedEvent);
	},

	/**
	 * Adds a consent for iFrames, which needs to be accepted.
	 *
	 * @return {void}
	 */
	openExternalContentConsent: function() {
		var parent = this.parentElement;
		if (!parent) {
			return;
		}

		var externalContentId = parent.getAttribute('data-iframe-id');
		if (!externalContentId) {
			return;
		}

		SgCookieOptin.lastOpenedExternalContentId = externalContentId;
		var externalContent = SgCookieOptin.protectedExternalContents[externalContentId];
		if (!externalContent) {
			return;
		}

		var wrapper = document.createElement('DIV');
		wrapper.id = 'SgCookieOptin';
		wrapper.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.iframe.markup);

		var flashMessageContainer = wrapper.querySelector('.sg-cookie-optin-box-flash-message');
		if (flashMessageContainer !== null) {
			var flashMessageText = externalContent.getAttribute('data-consent-description');
			if (flashMessageText) {
				flashMessageContainer.appendChild(document.createTextNode(flashMessageText));
			} else {
				flashMessageContainer.remove();
			}
		}

		SgCookieOptin.addExternalContentListeners(wrapper);

		document.body.insertAdjacentElement('beforeend', wrapper);
	},

	/**
	 * Adds the listeners to the given element.
	 *
	 * @param {dom} element
	 *
	 * @return {void}
	 */
	addExternalContentListeners: function(element) {
		var closeButtons = element.querySelectorAll('.sg-cookie-optin-box-close-button');
		SgCookieOptin.addEventListenerToList(closeButtons, 'click', SgCookieOptin.hideCookieOptIn);

		var acceptAllButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-all');
		SgCookieOptin.addEventListenerToList(acceptAllButtons, 'click', function() {
			SgCookieOptin.acceptAllExternalContents();
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();
			SgCookieOptin.hideCookieOptIn();
		});

		var acceptSpecificButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-specific');
		SgCookieOptin.addEventListenerToList(acceptSpecificButtons, 'click', function() {
			SgCookieOptin.acceptExternalContent(SgCookieOptin.lastOpenedExternalContentId);
			SgCookieOptin.updateCookieList();
			SgCookieOptin.handleScriptActivations();
			SgCookieOptin.hideCookieOptIn();
		});
	},

	/**
	 * Replaces all external content consent containers with the corresponding external content and adapts the cookie for further requests.
	 *
	 * @return {void}
	 */
	acceptAllExternalContents: function() {
		if (SgCookieOptin.jsonData.settings.iframe_enabled && SgCookieOptin.externalContentObserver) {
			SgCookieOptin.externalContentObserver.disconnect();
		}

		for (var index in SgCookieOptin.protectedExternalContents) {
			index = parseInt(index);
			if (!document.querySelector('div[data-iframe-id="' + index + '"]')) {
				continue;
			}

			SgCookieOptin.acceptExternalContent(index)
		}

		var cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		if (!cookieValue) {
			SgCookieOptin.acceptAllCookies();
			SgCookieOptin.hideCookieOptIn();
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
			if (group === SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT) {
				groupFound = true;
				status = 1;
			}

			if (newCookieValue.length > 0) {
				newCookieValue += '|';
			}
			newCookieValue += group + ':' + status;
		}

		if (!groupFound) {
			newCookieValue += '|' + SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT + ':' + 1;
		}

		SgCookieOptin.setCookieWrapper(newCookieValue);
	},

	/**
	 * Replaces an external content consent container with the external content element.
	 *
	 * @param {int} externalContentId
	 * @param {dom} container
	 * @return {void}
	 */
	acceptExternalContent: function(externalContentId) {
		if (!externalContentId) {
			externalContentId = parent.getAttribute('data-iframe-id');
			if (!externalContentId) {
				return;
			}
		}

		var container = document.querySelector('div[data-iframe-id="' + externalContentId + '"]');
		var externalContent = SgCookieOptin.protectedExternalContents[externalContentId];
		if (!externalContent || !container) {
			return;
		}

		externalContent.setAttribute('data-iframe-allow-always', 1);
		var positionIndex = externalContent.getAttribute('data-iframe-position-index');
		externalContent.removeAttribute('data-iframe-position-index');

		// Because of the IE11 no .replaceWith();
		var parentNode = container.parentNode;
		parentNode.removeChild(container);
		if (parentNode.childNodes.length > 0) {
			parentNode.insertBefore(externalContent, parentNode.childNodes[positionIndex]);
		} else {
			parentNode.appendChild(externalContent);
		}

		// Emit event when the external content has been accepted
		var externalContentAcceptedEvent = new CustomEvent('externalContentAccepted', {
			bubbles: true,
			detail: {
				positionIndex: positionIndex,
				parent: parent,
				externalContent: externalContent
			}
		});
		externalContent.dispatchEvent(externalContentAcceptedEvent);

	},

	/**
	 * Accept the external contents nested into the given container
	 *
	 * @param {dom} container
	 */
	acceptContainerChildNodes: function(container) {
		var replacedChildren = container.querySelectorAll('[data-iframe-id]');
		if (replacedChildren.length > 0) {
			for (var childIndex in replacedChildren) {
				var childElement = replacedChildren[childIndex];
				if (typeof childElement.getAttribute !== 'function') {
					continue;
				}
				var externalContentId = childElement.getAttribute('data-iframe-id');
				if (!externalContentId) {
					continue;
				}
				SgCookieOptin.acceptExternalContent(externalContentId);
			}
		}
	},

	/**
	 * todo Optimize the cookie handling with hasSetting, addSetting, removeSetting and use it everywhere.
	 *
	 * Returns the cookie, found with the given name, or null.
	 *
	 * @param {string} name
	 * @return {string}
	 */
	getCookie: function(name) {
		var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
		return v ? v[2] : null;
	},

	/**
	 * Sets the given cookie with the given value for X days.
	 *
	 * @param {string} name
	 * @param {string} value
	 * @param {string} days
	 */
	setCookie: function(name, value, days) {
		var d = new Date;
		d.setTime(d.getTime() + 24 * 60 * 60 * 1000 * days);
		var cookie = name + '=' + value + ';path=/';
		if (SgCookieOptin.jsonData.settings.set_cookie_for_domain.length > 0) {
			cookie += ';domain=' + SgCookieOptin.jsonData.settings.set_cookie_for_domain;
		}
		cookie += ';expires=' + d.toGMTString() + '; SameSite=Lax';
		document.cookie = cookie;
	},

	/**
	 * Sets the given cookie with the given value only for the current session.
	 *
	 * @param {string} name
	 * @param {string} value
	 */
	setSessionCookie: function(name, value) {
		document.cookie = name + '=' + value + '; path=/; SameSite=Lax';
	},

	/**
	 * Cookie is set with lifetime if the user has accepted a non-essential group that exists.
	 *
	 * @param {string} cookieValue
	 */
	setCookieWrapper: function(cookieValue) {
		var setCookieForSessionOnly = false;
		if (SgCookieOptin.jsonData.settings.session_only_essential_cookies) {
			var hasNonEssentialGroups = false;
			var hasAcceptedNonEssentials = false;
			var splitCookieValue = cookieValue.split('|');
			for (var cookieValueIndex in splitCookieValue) {
				if (!splitCookieValue.hasOwnProperty(cookieValueIndex)) {
					continue;
				}

				var valueEntry = splitCookieValue[cookieValueIndex];
				if (valueEntry.indexOf(SgCookieOptin.COOKIE_GROUP_ESSENTIAL) === 0 || valueEntry.indexOf(SgCookieOptin.COOKIE_GROUP_IFRAME) === 0) {
					continue;
				}

				hasNonEssentialGroups = true;
				if (valueEntry.indexOf(':1') > 0) {
					hasAcceptedNonEssentials = true;
					break;
				}
			}

			setCookieForSessionOnly = hasNonEssentialGroups && !(hasNonEssentialGroups && hasAcceptedNonEssentials);
		}

		if (setCookieForSessionOnly) {
			SgCookieOptin.setSessionCookie(SgCookieOptin.COOKIE_NAME, cookieValue);
		} else {
			SgCookieOptin.setCookie(SgCookieOptin.COOKIE_NAME, cookieValue, SgCookieOptin.jsonData.settings.cookie_lifetime);
		}
	},

	/**
	 * Displays a notification as a box as first element in the given contentElement
	 *
	 * @param {dom} contentElement
	 */
	showSaveConfirmation: function(contentElement) {
		var oldNotification = contentElement.firstChild;
		if (!oldNotification.classList.contains('sg-cookie-optin-save-confirmation')) {
			var notification = document.createElement('DIV');
			notification.classList.add('sg-cookie-optin-save-confirmation');
			notification.insertAdjacentText('afterbegin', SgCookieOptin.jsonData.textEntries.save_confirmation_text);
			contentElement.insertBefore(notification, contentElement.firstChild);
		}
	},

	/**
	 * Returns the value of a query parameter as a string, or null on error.
	 *
	 * @param {string} name
	 * @param {string} url
	 * @return {string|null}
	 */
	getParameterByName: function(name, url) {
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
	},

	/**
	 * Returns all the protected and unaccepted elements that match the given selector
	 *
	 * @param {string} selector
	 * @returns {[]}
	 */
	findProtectedElementsBySelector: function(selector) {
		var foundElements = [];
		for (var elementIndex in SgCookieOptin.protectedExternalContents) {
			if (typeof SgCookieOptin.protectedExternalContents[elementIndex].matches === 'function'
				&& SgCookieOptin.protectedExternalContents[elementIndex].matches(selector)) {
				foundElements.push(SgCookieOptin.protectedExternalContents[elementIndex]);
			}
		}
		return foundElements;
	},

	/**
	 * Adds a trigger function to all protected elements that will be fired when the respective element has been granted
	 * consent. You may filter the elements by a CSS selector.
	 *
	 * @param {function} callback
	 * @param {string} selector
	 */
	addAcceptHandlerToProtectedElements: function(callback, selector) {
		if (!selector) {
			selector = '*';
		}

		var elements = SgCookieOptin.findProtectedElementsBySelector(selector);
		this.addEventListenerToList(elements, 'externalContentAccepted', callback);
	},

	closestPolyfill: function() {
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
};

SgCookieOptin.run();
