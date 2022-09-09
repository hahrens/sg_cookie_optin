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
	LAST_PREFERENCES_COOKIE_NAME: 'cookie_optin_last_preferences',
	LAST_PREFERENCES_LOCAL_STORAGE_NAME: 'SgCookieOptin.lastPreferences',
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
	isExternalGroupAccepted: false,
	fingerprintIcon: null,

	/**
	 * Executes the script
	 */
	run: function() {
		SgCookieOptin.closestPolyfill();
		SgCookieOptin.customEventPolyfill();

		SgCookieOptin.jsonData = JSON.parse(document.getElementById('cookieOptinData').innerHTML);
		if (SgCookieOptin.jsonData) {
			SgCookieOptin.setCookieNameBasedOnLanguage();
			// https://plainjs.com/javascript/events/running-code-when-the-document-is-ready-15/
			document.addEventListener('DOMContentLoaded', function() {
				SgCookieOptin.initialize();
			});
			SgCookieOptin.isExternalGroupAccepted = SgCookieOptin.checkIsExternalGroupAccepted();
			SgCookieOptin.checkForExternalContents();

			if (SgCookieOptin.jsonData.settings.iframe_enabled && SgCookieOptin.isExternalGroupAccepted) {
				document.addEventListener("DOMContentLoaded", function() {
					SgCookieOptin.checkForDataAttributeExternalContents();
				});
			}
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
		if (showOptIn || !SgCookieOptin.jsonData.settings.activate_testing_mode &&
			(SgCookieOptin.shouldShowBannerBasedOnLastPreferences(cookieValue))
		) {
			SgCookieOptin.openCookieOptin(null, {hideBanner: false});
		} else {
			SgCookieOptin.showFingerprint();
		}
	},

	/**
	 * Sets the sg_cookie_optin cookie name accordingly based on the current language settings
	 */
	setCookieNameBasedOnLanguage: function() {
		if (!SgCookieOptin.jsonData.settings.unified_cookie_name) {
			SgCookieOptin.COOKIE_NAME += '_' + SgCookieOptin.jsonData.settings.identifier + '_'
				+ SgCookieOptin.jsonData.settings.language;
			SgCookieOptin.LAST_PREFERENCES_COOKIE_NAME += '_' + SgCookieOptin.jsonData.settings.identifier + '_'
				+ SgCookieOptin.jsonData.settings.language;
		}
	},

	/**
	 * Checks if the external cookie group has been accepted
	 * @returns {boolean}
	 */
	checkIsExternalGroupAccepted: function() {
		return SgCookieOptin.checkIsGroupAccepted(SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT);
	},

	/**
	 * Checks whether the given group has been accepted or not
	 *
	 * @param {string} groupName
	 * @param {string} cookieValue
	 * @returns {boolean}
	 */
	checkIsGroupAccepted: function(groupName, cookieValue) {
		if (typeof cookieValue === 'undefined') {
			cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		}
		if (cookieValue) {
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
				if (group === groupName) {
					if (status === 1) {
						return true;
					}
					break;
				}
			}
		}
		return false;
	},

	/**
	 * Checks whether the given group has been accepted or not
	 *
	 * @param {string} groupName
	 * @param {string} cookieValue
	 * @returns {boolean}
	 */
	readCookieValues: function(cookieValue) {
		if (typeof cookieValue === 'undefined') {
			cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);
		}

		var cookieValues = [];

		if (cookieValue) {
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
				cookieValues[group] = status;
			}
		}

		return cookieValues;
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
						// Emit event
						var addedLoadingHTMLEvent = new CustomEvent('addedLoadingHTML', {
							bubbles: true,
							detail: {
								src: SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingHTML']
							}
						});
						head.dispatchEvent(addedLoadingHTMLEvent);
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

					// Emit event
					var addedLoadingScriptEvent = new CustomEvent('addedLoadingScript', {
						bubbles: true,
						detail: {
							src: SgCookieOptin.jsonData.cookieGroups[groupIndex]['loadingJavaScript']
						}
					});
					script.dispatchEvent(addedLoadingScriptEvent);
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
		if (!SgCookieOptin.shouldShowOptinBanner()) {
			return;
		}

		SgCookieOptin.hideFingerprint();
		var hideBanner = typeof options == 'object' && options.hideBanner === true;
		var fromBanner = typeof options == 'object' && options.fromBanner === true;

		var wrapper = document.createElement('DIV');
		wrapper.setAttribute('data-nosnippet', '');
		wrapper.id = 'SgCookieOptin';

		var forceBanner;
		if (SgCookieOptin.jsonData.settings.banner_force_min_width && parseInt(SgCookieOptin.jsonData.settings.banner_force_min_width) > 0) {
			forceBanner = window.matchMedia('(max-width: ' + parseInt(SgCookieOptin.jsonData.settings.banner_force_min_width) + 'px)').matches;
		} else {
			forceBanner = false;
		}

		if (!fromBanner && !hideBanner && ((!contentElement && SgCookieOptin.jsonData.settings.banner_enable && !hideBanner) || forceBanner)) {
			wrapper.classList.add('sg-cookie-optin-banner-wrapper');
			wrapper.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.banner.markup);
		} else {
			wrapper.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.template.markup);
		}

		SgCookieOptin.insertUserUuid(wrapper);

		SgCookieOptin.addListeners(wrapper, contentElement);

		if (!contentElement) {
			document.body.insertAdjacentElement('beforeend', wrapper);
		} else {
			contentElement.appendChild(wrapper);
		}

		setTimeout(function() {
			SgCookieOptin.adjustDescriptionHeight(wrapper, contentElement);
			SgCookieOptin.updateCookieList();
			// Emit event
			var cookieOptinShownEvent = new CustomEvent('cookieOptinShown', {
				bubbles: true,
				detail: {}
			});
			document.body.dispatchEvent(cookieOptinShownEvent);

			// check if there is a cookie optin plugin on the page - then don't focus the checkboxes
			if (document.getElementsByClassName('sg-cookie-optin-plugin-initialized').length > 0) {
				return;
			}

			var checkboxes = document.getElementsByClassName('sg-cookie-optin-checkbox');
			if (checkboxes.length > 1) {
				if (checkboxes[1].focus) {
					checkboxes[1].focus();
				}
			}

		}, 10);
	},

	/**
	 * Checks if the cookie banner should be shown
	 *
	 * @returns {boolean}
	 */
	shouldShowOptinBanner: function() {
		// check doNotTrack
		if (typeof SgCookieOptin.jsonData.settings.consider_do_not_track !== 'undefined'
			&& SgCookieOptin.jsonData.settings.consider_do_not_track
			&& typeof navigator.doNotTrack !== 'undefined' && navigator.doNotTrack === '1') {
			console.log('Cookie Consent: DoNotTrack detected - Auto-OptOut');
			return false;
		}

		// check if there is a cookie optin plugin on the page
		if (document.getElementsByClassName('sg-cookie-optin-plugin-initialized').length > 0) {
			return false;
		}

		// test if the current URL matches one of the whitelist regex
		if (typeof SgCookieOptin.jsonData.settings.cookiebanner_whitelist_regex !== 'undefined'
			&& SgCookieOptin.jsonData.settings.cookiebanner_whitelist_regex.trim() !== ''
		) {
			var regularExpressions = SgCookieOptin.jsonData.settings.cookiebanner_whitelist_regex.trim()
				.split(/\r?\n/).map(function(value) {
					return new RegExp(value);
				});
			if (typeof regularExpressions === 'object' && regularExpressions.length > 0) {
				for (var regExIndex in regularExpressions) {
					if (regularExpressions[regExIndex].test(window.location)) {
						return false;
					}
				}
			}
		}

		return true;
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
	 * Checks whether we must show the cookie banner based on the last preferences of the user.
	 * This may be necessary if there are new groups or new cookies since the last preferences save,
	 * or the user didn't select all preferences and the configured interval has expired
	 *
	 * @param {String} cookieValue
	 * @returns {boolean}
	 */
	shouldShowBannerBasedOnLastPreferences: function(cookieValue) {
		if (!cookieValue) {
			return true;
		}

		var lastPreferences = SgCookieOptin.getLastPreferences();

		if (typeof lastPreferences.timestamp === 'undefined') {
			return true;
		}

		if (lastPreferences.version !== SgCookieOptin.jsonData.settings.version) {
			return true;
		}

		for (var groupIndex in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}

			// is the group new
			if (typeof SgCookieOptin.jsonData.cookieGroups[groupIndex].crdate !== 'undefined' &&
				SgCookieOptin.jsonData.cookieGroups[groupIndex].crdate > lastPreferences.timestamp) {
				return true;
			}

			// is there a new cookie in this group
			for (var cookieIndex in SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData) {
				if (!SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData.hasOwnProperty(cookieIndex)) {
					continue;
				}

				if (typeof SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData[cookieIndex].crdate !== 'undefined'
					&& SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData[cookieIndex].crdate > lastPreferences.timestamp) {
					return true;
				}
			}

			// if the user didn't select all group last time, check if the group was not accepted and the configured interval has expired
			if (!lastPreferences.isAll &&
				typeof SgCookieOptin.jsonData.cookieGroups[groupIndex].groupName !== 'undefined' &&
				!SgCookieOptin.checkIsGroupAccepted(SgCookieOptin.jsonData.cookieGroups[groupIndex].groupName,
					lastPreferences.cookieValue)
				&& (new Date().getTime() / 1000) > (lastPreferences.timestamp + 24 * 60 * 60
					* SgCookieOptin.jsonData.settings.banner_show_again_interval)) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Gets the current user uuid
	 *
	 * @param {boolean} setIfEmpty
	 * @returns {string}
	 */
	getUserUuid: function(setIfEmpty) {
		var lastPreferences = SgCookieOptin.getLastPreferences();
		var userUuid = lastPreferences.uuid;
		if (setIfEmpty && !userUuid && !SgCookieOptin.jsonData.settings.disable_usage_statistics) {
			userUuid = SgCookieOptin.generateUUID();
			lastPreferences.uuid = userUuid;
			window.localStorage.setItem(SgCookieOptin.LAST_PREFERENCES_LOCAL_STORAGE_NAME, JSON.stringify(lastPreferences));
		}

		return userUuid;
	},

	/**
	 * Shows the UserUiid in the cookie banner
	 *
	 * @param {HTMLElement} wrapper
	 */
	insertUserUuid: function(wrapper) {
		var hashContainer = wrapper.querySelector('.sg-cookie-optin-box-footer-user-hash');
		var hashContainerParent = wrapper.querySelector('.sg-cookie-optin-box-footer-user-hash-container');

		// The banner does not have a hash container
		if (!hashContainer || !hashContainerParent) {
			return;
		}

		if (SgCookieOptin.jsonData.settings.disable_usage_statistics) {
			hashContainerParent.style.display = 'none';
			return;
		}

		var uuid = SgCookieOptin.getUserUuid(false);

		if (typeof hashContainer.innerText === 'string') {
			if (uuid) {
				hashContainer.innerText = uuid;
			} else {
				hashContainerParent.style.display = 'none';
			}
		}
	},

	/**
	 * Stores the last saved preferences in the localstorage
	 *
	 * @param {string} cookieValue
	 */
	saveLastPreferences: function(cookieValue) {
		var isAll = true;
		for (var groupIndex in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}
			if (!SgCookieOptin.checkIsGroupAccepted(SgCookieOptin.jsonData.cookieGroups[groupIndex].groupName)) {
				isAll = false;
			}
		}

		var lastPreferences = SgCookieOptin.getLastPreferences();

		lastPreferences.timestamp = Math.floor(new Date().getTime() / 1000);
		lastPreferences.cookieValue = cookieValue;
		lastPreferences.isAll = isAll;
		lastPreferences.version = SgCookieOptin.jsonData.settings.version;
		lastPreferences.identifier = SgCookieOptin.jsonData.settings.identifier;

		if (SgCookieOptin.lastPreferencesFromCookie()) {
			SgCookieOptin.setCookie(SgCookieOptin.LAST_PREFERENCES_COOKIE_NAME, JSON.stringify(lastPreferences), 365)
		} else {
			window.localStorage.setItem(SgCookieOptin.LAST_PREFERENCES_LOCAL_STORAGE_NAME, JSON.stringify(lastPreferences));
		}

		SgCookieOptin.saveLastPreferencesForStats(lastPreferences);
	},

	/**
	 * Decides whether to read the last preferences from cookie or from local storage
	 *
	 * @returns {boolean}
	 */
	lastPreferencesFromCookie: function() {
		return (SgCookieOptin.jsonData.settings.subdomain_support || SgCookieOptin.jsonData.settings.set_cookie_for_domain);
	},

	/**
	 * Saves the preferences for statistics via AJAX
	 *
	 * @param lastPreferences
	 */
	saveLastPreferencesForStats: function(lastPreferences) {
		if (SgCookieOptin.jsonData.settings.disable_usage_statistics) {
			return;
		}

		lastPreferences.uuid = SgCookieOptin.getUserUuid(true);

		var request = new XMLHttpRequest();
		var formData = new FormData();
		formData.append('lastPreferences', JSON.stringify(lastPreferences));
		var url = SgCookieOptin.jsonData.settings.save_history_webhook;

		if (!url) {
			return;
		}

		request.open('POST', url);
		request.send(formData);
	},

	/**
	 * Generates a RFC4122 v4 compliant UUID
	 *
	 * @returns {string}
	 */
	generateUUID: function() { // Public Domain/MIT
		var d = new Date().getTime();//Timestamp
		var d2 = (performance && performance.now && (performance.now() * 1000)) || 0;//Time in microseconds since page-load or 0 if unsupported
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = Math.random() * 16;//random number between 0 and 16
			if (d > 0) {//Use timestamp until depleted
				r = (d + r) % 16 | 0;
				d = Math.floor(d / 16);
			} else {//Use microseconds since page-load if supported
				r = (d2 + r) % 16 | 0;
				d2 = Math.floor(d2 / 16);
			}
			return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
		});
	},

	/**
	 * Delete all cookies that match the regex of the cookie name if a given group has been unselected by the user
	 */
	deleteCookiesForUnsetGroups: function() {
		for (var groupIndex in SgCookieOptin.jsonData.cookieGroups) {
			if (!SgCookieOptin.jsonData.cookieGroups.hasOwnProperty(groupIndex)) {
				continue;
			}
			var documentCookies = document.cookie.split('; ');
			if (!SgCookieOptin.checkIsGroupAccepted(SgCookieOptin.jsonData.cookieGroups[groupIndex].groupName)) {
				for (var cookieIndex in SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData) {
					if (isNaN(parseInt(cookieIndex))) {
						continue;
					}

					for (var documentCookieIndex in documentCookies) {
						if (isNaN(parseInt(documentCookieIndex))) {
							continue;
						}

						var cookieName = documentCookies[documentCookieIndex].split('=')[0];
						var regExString = SgCookieOptin.jsonData.cookieGroups[groupIndex].cookieData[cookieIndex]
							.Name.trim();

						if (!regExString) {
							continue;
						}

						var regEx = new RegExp(regExString);
						if (regEx.test(cookieName)) {
							// delete the cookie
							SgCookieOptin.deleteGroupCookie(cookieName);
						}
					}
				}
			}
		}
	},

	/**
	 * Delete the cookie from the current domain and the additional domains
	 *
	 * @param cookieName
	 */
	deleteGroupCookie: function(cookieName) {
		var cookie = cookieName + '=; path=/; Max-Age=-99999999;';
		document.cookie = cookie; // This is important in case the configuration that we test below has been changed
		var currentHost = window.location.hostname;

		if (SgCookieOptin.jsonData.settings.set_cookie_for_domain && SgCookieOptin.jsonData.settings.set_cookie_for_domain.length > 0) {
			cookie += ';domain=' + SgCookieOptin.jsonData.settings.set_cookie_for_domain;
		} else if (SgCookieOptin.jsonData.settings.subdomain_support) {
			var domainParts = currentHost.split('.');
			if (domainParts.length > 2) {
				domainParts.shift();
				var hostnameToFirstDot = '.' + domainParts.join('.');
				cookie += ';domain=' + hostnameToFirstDot;
			}
		}

		document.cookie = cookie;

		var additionalDomains = SgCookieOptin.jsonData.settings.domains_to_delete_cookies_for.trim()
			.split(/\r?\n/).map(function(value) {
				return value.trim();
			});

		for (var additionalDomainIndex in additionalDomains) {
			if (!additionalDomains.hasOwnProperty(additionalDomainIndex)) {
				continue;
			}

			document.cookie = cookieName + '=; path=/; ' + 'domain=' + additionalDomains[additionalDomainIndex] + '; Max-Age=-99999999;';
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
		SgCookieOptin.addEventListenerToList(closeButtons, 'click', function() {
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
		SgCookieOptin.addEventListenerToList(openSubListLink, 'click', function(event) {
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
			} else {
				SgCookieOptin.showSaveConfirmation(contentElement);
			}
		});

		var openSettingsButtons = element.querySelectorAll('.sg-cookie-optin-banner-button-settings');
		SgCookieOptin.addEventListenerToList(openSettingsButtons, 'click', function() {
			SgCookieOptin.hideCookieOptIn();
			SgCookieOptin.openCookieOptin(null, {hideBanner: true, fromBanner: true});
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

			// Emit event
			var cookieOptinHiddenEvent = new CustomEvent('cookieOptinHidden', {
				bubbles: true,
				detail: {}
			});
			parentNode.dispatchEvent(cookieOptinHiddenEvent);
		}
		SgCookieOptin.showFingerprint();
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

			if (groupName === SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT) {
				SgCookieOptin.acceptAllExternalContents();
			}
		}
		SgCookieOptin.setCookieWrapper(cookieData);
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

		SgCookieOptin.setCookieWrapper(cookieData);

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

		if (SgCookieOptin.isExternalGroupAccepted) {
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
			.split(/\r?\n/).map(function(value) {
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

		if (SgCookieOptin.isUrlLocal(externalContent.dataset['contentReplaceSrc'] || externalContent.getAttribute(attribute))) {
			return true;
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
		var foundNonWhitelisted = false;
		for (var sourceIndex in sources) {
			if (!sources.hasOwnProperty(sourceIndex)) {
				continue;
			}

			if (!sources[sourceIndex].getAttribute('src')) {
				continue;
			}

			// noinspection JSUnfilteredForInLoop
			if (!SgCookieOptin.isElementWhitelisted(sources[sourceIndex], 'src', regularExpressions)) {
				foundNonWhitelisted = true;
			}
		}

		return !foundNonWhitelisted;
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
	 * Checks if the given URL is a local or relative path
	 * @param {string} url
	 */
	isUrlLocal: function(url) {
		var tempA = document.createElement('a');
		tempA.setAttribute('href', url);
		return window.location.protocol === tempA.protocol && window.location.host === tempA.host;
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

		var src = externalContent.dataset['contentReplaceSrc'] || externalContent.src;
		// Skip iframes with no source
		if (externalContent.tagName === 'IFRAME'
			&& (!src || externalContent.src.indexOf('chrome-extension') >= 0)) {
			return;
		}

		// Get the position of the element within its parent
		var positionIndex = 0;
		var child = externalContent;
		while ((child = child.previousSibling) != null) {
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
		container.setAttribute('data-content-replace-src', src);
		container.setAttribute('style', 'height: ' + externalContent.offsetHeight + 'px;');
		container.classList.add('sg-cookie-optin-iframe-consent');
		container.insertAdjacentHTML('afterbegin', SgCookieOptin.jsonData.mustacheData.iframeReplacement.markup);

		// Add event Listeners to the consent buttons
		var externalContentConsentAccept = container.querySelectorAll('.sg-cookie-optin-iframe-consent-accept');
		SgCookieOptin.addEventListenerToList(externalContentConsentAccept, 'click', function() {
			SgCookieOptin.acceptExternalContent(externalContentId)
		});

		// Set custom accept text if available
		if (externalContent.getAttribute('data-consent-button-text')) {
			for (var acceptButtonIndex = 0; acceptButtonIndex < externalContentConsentAccept.length; ++acceptButtonIndex) {
				externalContentConsentAccept[acceptButtonIndex].innerText = externalContent.getAttribute('data-consent-button-text');
			}
		}

		SgCookieOptin.setExternalContentDescriptionText(container, externalContent);

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
	 * Sets external content description text bellow the button and in the settings description
	 * @param {HTMLElement} wrapper
	 * @param {HTMLElement} externalContent
	 */
	setExternalContentDescriptionText: function(wrapper, externalContent) {
		var flashMessageContainer = wrapper.querySelector('.sg-cookie-optin-box-flash-message');
		if (flashMessageContainer !== null) {
			var flashMessageText = externalContent.getAttribute('data-consent-description');
			// fallback to default if no data attribute has been set
			if (!flashMessageText) {
				flashMessageText = SgCookieOptin.jsonData.textEntries.iframe_button_load_one_description;
			}

			if (flashMessageText) {
				flashMessageContainer.appendChild(document.createTextNode(flashMessageText));
			} else {
				flashMessageContainer.remove();
			}
		}
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

		SgCookieOptin.setExternalContentDescriptionText(wrapper, externalContent);
		SgCookieOptin.addExternalContentListeners(wrapper);

		document.body.insertAdjacentElement('beforeend', wrapper);

		// focus the first button for better accessability
		var buttons = document.getElementsByClassName('sg-cookie-optin-box-button-accept-all');
		if (buttons.length > 0) {
			if (buttons[0].focus) {
				buttons[0].focus();
			}
		}

		// Emit event
		var externalContentConsentDisplayedEvent = new CustomEvent('externalContentConsentDisplayed', {
			bubbles: true,
			detail: {}
		});
		wrapper.dispatchEvent(externalContentConsentDisplayedEvent);
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

		var rejectButtons = element.querySelectorAll('.sg-cookie-optin-box-button-iframe-reject');
		SgCookieOptin.addEventListenerToList(rejectButtons, 'click', SgCookieOptin.hideCookieOptIn);

		var acceptAllButtons = element.querySelectorAll('.sg-cookie-optin-box-button-accept-all');
		SgCookieOptin.addEventListenerToList(acceptAllButtons, 'click', function() {
			SgCookieOptin.acceptAllExternalContents();
			SgCookieOptin.acceptExternalConentGroup();
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
	},

	/**
	 * Accepts the external content group and updates the cookie value if needed
	 *
	 * @return {void}
	 */
	acceptExternalConentGroup: function() {
		var cookieValue = SgCookieOptin.getCookie(SgCookieOptin.COOKIE_NAME);

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

		if (cookieValue !== newCookieValue) {
			SgCookieOptin.setCookieWrapper(newCookieValue);
		}
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

		SgCookieOptin.fixExternalContentAttributes(externalContent);
		SgCookieOptin.emitExternalContentAcceptedEvent(externalContent);
	},

	/**
	 * Emit event when the external content element has been accepted
	 * @param {dom} externalContent
	 */
	emitExternalContentAcceptedEvent: function(externalContent) {
		var externalContentAcceptedEvent = new CustomEvent('externalContentAccepted', {
			bubbles: true,
			detail: {
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

		var currentHost = window.location.hostname;
		var cookieStringEnd = ';expires=' + d.toUTCString() + '; SameSite=None; Secure';

		if (SgCookieOptin.jsonData.settings.set_cookie_for_domain && SgCookieOptin.jsonData.settings.set_cookie_for_domain.length > 0) {
			cookie += ';domain=' + SgCookieOptin.jsonData.settings.set_cookie_for_domain;
		} else if (SgCookieOptin.jsonData.settings.subdomain_support) {
			var domainParts = currentHost.split('.');
			if (domainParts.length > 2) {
				domainParts.shift();
				var hostnameToFirstDot = '.' + domainParts.join('.');
				cookie += ';domain=' + hostnameToFirstDot;
			}
		}

		document.cookie = cookie + cookieStringEnd;
	},

	/**
	 * Sets the given cookie with the given value only for the current session.
	 *
	 * @param {string} name
	 * @param {string} value
	 */
	setSessionCookie: function(name, value) {
		var cookie = name + '=' + value + '; path=/';

		var currentHost = window.location.hostname;
		var cookieStringEnd = ';SameSite=None; Secure';

		if (SgCookieOptin.jsonData.settings.set_cookie_for_domain && SgCookieOptin.jsonData.settings.set_cookie_for_domain.length > 0) {
			cookie += ';domain=' + SgCookieOptin.jsonData.settings.set_cookie_for_domain;
		} else if (SgCookieOptin.jsonData.settings.subdomain_support) {
			var domainParts = currentHost.split('.');
			if (domainParts.length > 2) {
				domainParts.shift();
				var hostnameToFirstDot = '.' + domainParts.join('.');
				cookie += ';domain=' + hostnameToFirstDot;
			}
		}

		document.cookie = cookie + cookieStringEnd;
	},

	/**
	 * Cookie is set with lifetime if the user has accepted a non-essential group that exists.
	 *
	 * @param {string} cookieValue
	 */
	setCookieWrapper: function(cookieValue, cookieName) {
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
				if (valueEntry.indexOf(SgCookieOptin.COOKIE_GROUP_ESSENTIAL) === 0 || valueEntry.indexOf(SgCookieOptin.COOKIE_GROUP_EXTERNAL_CONTENT) === 0) {
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

		SgCookieOptin.saveLastPreferences(cookieValue);
		SgCookieOptin.deleteCookiesForUnsetGroups();

		// Emit event
		var consentCookieSetEvent = new CustomEvent('consentCookieSet', {
			bubbles: true,
			detail: {
				cookieValue: cookieValue
			}
		});
		document.body.dispatchEvent(consentCookieSetEvent);
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
		if (typeof callback !== 'function') {
			throw new Error('Required argument "callback" has not been passed.');
		}

		if (!selector) {
			selector = '*';
		}

		var elements = SgCookieOptin.findProtectedElementsBySelector(selector);
		if (elements.length > 0) {
			SgCookieOptin.addEventListenerToList(elements, 'externalContentAccepted', callback);
		} else {
			// workaround for when the external group has been accepted and we have no protected elements
			if (SgCookieOptin.isExternalGroupAccepted) {
				SgCookieOptin.triggerAcceptedEventListenerToExternalContentElements(callback, selector);
			}
		}
	},

	/**
	 * Filters the elements that would have been replaced by the external protection by the given selector and triggers
	 * the ExternalContentAccepted Event for each of the elements found
	 *
	 * @param {function} callback
	 * @param {string} selector
	 */
	triggerAcceptedEventListenerToExternalContentElements: function(callback, selector) {
		var elements = [];
		var externalContentElements = document.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR);
		for (var index = 0; index < externalContentElements.length; ++index) {
			if (typeof externalContentElements[index].matches === 'function' && externalContentElements[index].matches(selector)) {
				elements.push(externalContentElements[index]);
			}
		}

		if (elements.length > 0) {
			// add the listener with the same callback to keep the same API
			SgCookieOptin.addEventListenerToList(elements, 'externalContentAccepted', callback);
			// trigger the event immediately
			for (var index = 0; index < elements.length; ++index) {
				SgCookieOptin.emitExternalContentAcceptedEvent(elements[index]);
			}
		}
	},

	/**
	 * Polyfill for the Element.matches and Element.closest
	 */
	closestPolyfill: function() {
		var ElementPrototype = window.Element.prototype;

		if (typeof ElementPrototype.matches !== 'function') {
			ElementPrototype.matches = ElementPrototype.msMatchesSelector || ElementPrototype.mozMatchesSelector || ElementPrototype.webkitMatchesSelector || function matches(selector) {
				var element = this;
				var elements = (element.document || element.ownerDocument).querySelectorAll(selector);
				var index = 0;

				while (elements[index] && elements[index] !== element) {
					++index;
				}

				return Boolean(elements[index]);
			};
		}

		if (typeof ElementPrototype.closest !== 'function') {
			ElementPrototype.closest = function closest(selector) {
				var element = this;

				while (element && element.nodeType === 1) {
					if (element.matches(selector)) {
						return element;
					}

					element = element.parentNode;
				}

				return null;
			};
		}
	},

	/**
	 * Adds the Polyfill for the Window.CustomEvent
	 * @returns {boolean}
	 */
	customEventPolyfill: function() {
		if (typeof window.CustomEvent === "function") {
			return false;
		}

		function CustomEvent(event, params) {
			params = params || {bubbles: false, cancelable: false, detail: null};
			var evt = document.createEvent('CustomEvent');
			evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
			return evt;
		}

		window.CustomEvent = CustomEvent;
	},

	/**
	 * Get the last preferences object in the local storage
	 *
	 * @return {Object}|undefined
	 */
	getLastPreferences: function() {
		if (SgCookieOptin.lastPreferencesFromCookie()) {
			var lastPreferences = SgCookieOptin.getCookie(SgCookieOptin.LAST_PREFERENCES_COOKIE_NAME);
		} else {
			var lastPreferences = window.localStorage.getItem(SgCookieOptin.LAST_PREFERENCES_LOCAL_STORAGE_NAME);
		}

		if (!lastPreferences) {
			return {};
		}

		try {
			lastPreferences = JSON.parse(lastPreferences);
			return lastPreferences;
		} catch (e) { // we don't want to break the rest of the code if the JSON is malformed for some reason
			return {};
		}
	},

	/**
	 * Replaces the data- attribute placeholders for source URLs with actual attributes depending on the element type
	 *
	 * @param {HTMLElement}externalContent
	 * @return {void}
	 */
	fixExternalContentAttributes: function(externalContent) {
		switch (externalContent.tagName) {
			case 'IFRAME':
				return SgCookieOptin.fixExternalContentAttribute(externalContent, 'src');
			case 'OBJECT':
				return SgCookieOptin.fixExternalContentAttribute(externalContent, 'data');
			default:
				return;
		}
	},

	/**
	 * Replaces the data-src and data-data with actual src and data attributes
	 *
	 * @param {dom} externalContent
	 * @param {string} attribute
	 */
	fixExternalContentAttribute: function(externalContent, attribute) {
		if (externalContent.dataset['contentReplaceSrc']) {
			externalContent.setAttribute(attribute, externalContent.dataset['contentReplaceSrc']);
		}
	},

	/**
	 * Checks if there are any external contents with data-src attributes and sets their real attributes to the
	 * data-attribute value
	 */
	checkForDataAttributeExternalContents: function() {
		if (SgCookieOptin.jsonData.settings.iframe_enabled && SgCookieOptin.isExternalGroupAccepted) {
			var externalContents = document.querySelectorAll(SgCookieOptin.EXTERNAL_CONTENT_ELEMENT_SELECTOR);
			if (externalContents.length > 0) {
				for (var externalContentToFixIndex in externalContents) {
					if (!externalContents.hasOwnProperty(externalContentToFixIndex)) {
						continue;
					}

					SgCookieOptin.fixExternalContentAttributes(externalContents[externalContentToFixIndex]);
				}
			}
		}
	},

	/**
	 * Shows the fingerprint and creates the element if necessary
	 */
	showFingerprint: function() {
		if (!(SgCookieOptin.jsonData.settings.fingerprint_position > 0) || !SgCookieOptin.shouldShowOptinBanner()) {
			return;
		}

		if (SgCookieOptin.fingerprintIcon === null) {
			SgCookieOptin.fingerprintIcon = SgCookieOptin.createFingeprint();
		}

		SgCookieOptin.fingerprintIcon.style.display = 'block';
	},

	/**
	 * Hides the fingerprint
	 */
	hideFingerprint: function() {
		if (SgCookieOptin.fingerprintIcon === null || !SgCookieOptin.shouldShowOptinBanner()) {
			return;
		}

		SgCookieOptin.fingerprintIcon.style.display = 'none';
	},

	/**
	 * Creates the fingerprint HTML element
	 * @return {HTMLDivElement}
	 */
	createFingeprint: function() {
		var fingerprintContainer = document.createElement('div');
		var iconPositionClass = '';

		switch (SgCookieOptin.jsonData.settings.fingerprint_position) {
			case 1:
				iconPositionClass = 'bottom-left';
				break;
			case 2:
				iconPositionClass = 'bottom-right';
				break;
			case 3:
				iconPositionClass = 'top-left';
				break;
			case 4:
				iconPositionClass = 'top-right';
				break;
		}

		fingerprintContainer.classList.add('sg-cookie-optin-fingerprint');
		fingerprintContainer.classList.add('sg-cookie-optin-fingerprint-' + iconPositionClass);

		fingerprintContainer.innerHTML = '<svg\n' +
			'   class="sg-cookie-optin-fingerprint-icon"\n' +
			'   xmlns:dc="http://purl.org/dc/elements/1.1/"\n' +
			'   xmlns:cc="http://creativecommons.org/ns#"\n' +
			'   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"\n' +
			'   xmlns:svg="http://www.w3.org/2000/svg"\n' +
			'   xmlns="http://www.w3.org/2000/svg"\n' +
			'   xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"\n' +
			'   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"\n' +
			'   version="1.1"\n' +
			'   id="Layer_3"\n' +
			'   x="0px"\n' +
			'   y="0px"\n' +
			'   viewBox="0 0 496.1 496"\n' +
			'   style="enable-background:new 0 0 496.1 496;"\n' +
			'   xml:space="preserve"\n' +
			'   sodipodi:docname="cookie_table-icon.svg"\n' +
			'   inkscape:version="0.92.3 (2405546, 2018-03-11)"><metadata\n' +
			'   id="metadata37"><rdf:RDF><cc:Work\n' +
			'       rdf:about=""><dc:format>image/svg+xml</dc:format><dc:type\n' +
			'         rdf:resource="http://purl.org/dc/dcmitype/StillImage" /></cc:Work></rdf:RDF></metadata><defs\n' +
			'   id="defs35" /><sodipodi:namedview\n' +
			'   pagecolor="#ffffff"\n' +
			'   bordercolor="#666666"\n' +
			'   borderopacity="1"\n' +
			'   objecttolerance="10"\n' +
			'   gridtolerance="10"\n' +
			'   guidetolerance="10"\n' +
			'   inkscape:pageopacity="0"\n' +
			'   inkscape:pageshadow="2"\n' +
			'   inkscape:window-width="2560"\n' +
			'   inkscape:window-height="991"\n' +
			'   id="namedview33"\n' +
			'   showgrid="false"\n' +
			'   inkscape:zoom="0.33644597"\n' +
			'   inkscape:cx="-287.44236"\n' +
			'   inkscape:cy="417.13792"\n' +
			'   inkscape:window-x="2560"\n' +
			'   inkscape:window-y="0"\n' +
			'   inkscape:window-maximized="1"\n' +
			'   inkscape:current-layer="Layer_3" />\n' +
			'<style\n' +
			'   type="text/css"\n' +
			'   id="style2">\n' +
			'\t.st0{fill:none;stroke:' + SgCookieOptin.jsonData.cssData.color_fingerprint_border + ';stroke-width:33;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;}\n' +
			'</style>\n' +
			'<g\n' +
			'   id="g6">\n' +
			'\t<circle\n' +
			'   cx="101"\n' +
			'   cy="273.6"\n' +
			'   r="36.7"\n' +
			'   id="circle4" />\n' +
			'</g>\n' +
			'<g\n' +
			'   id="g10">\n' +
			'\t<path\n' +
			'   d="M243.9,285.2c-1.5,19-18.1,33.2-37.1,31.7c-19-1.5-33.2-18.1-31.7-37.1c1.5-19,18.1-33.2,37.1-31.7   C231.2,249.6,245.4,266.2,243.9,285.2z"\n' +
			'   id="path8" />\n' +
			'</g>\n' +
			'<g\n' +
			'   id="g14">\n' +
			'\t<path\n' +
			'   d="M350.7,240.6c-1.7,21.2-20.2,37.1-41.5,35.4c-21.2-1.7-37.1-20.2-35.4-41.5c1.7-21.2,20.2-37.1,41.5-35.4   C336.5,200.8,352.3,219.3,350.7,240.6z"\n' +
			'   id="path12" />\n' +
			'</g>\n' +
			'<g\n' +
			'   id="g18">\n' +
			'\t<path\n' +
			'   d="M209.4,379.2c-1.7,21.2-20.2,37.1-41.5,35.4c-21.2-1.7-37.1-20.2-35.4-41.5c1.7-21.2,20.2-37.1,41.5-35.4   C195.2,339.4,211.1,357.9,209.4,379.2z"\n' +
			'   id="path16" />\n' +
			'</g>\n' +
			'<g\n' +
			'   id="g22">\n' +
			'\t<path\n' +
			'   d="M345.5,370.9c-2.1,26.2-25,45.7-51.1,43.7c-26.2-2.1-45.7-25-43.7-51.1c2.1-26.2,25-45.7,51.1-43.7   C328,321.8,347.6,344.7,345.5,370.9z"\n' +
			'   id="path20" />\n' +
			'</g>\n' +
			'<g\n' +
			'   id="g26">\n' +
			'\t<path\n' +
			'   d="M232.2,165.7c0.2,28.4-22.7,51.5-51.1,51.7c-28.4,0.2-51.5-22.7-51.7-51.1c-0.2-28.4,22.7-51.5,51.1-51.7   C208.9,114.5,232,137.3,232.2,165.7z"\n' +
			'   id="path24" />\n' +
			'</g>\n' +
			'<path\n' +
			'   class="st0"\n' +
			'   d="M407.3,258.9c-0.1-18,8.1-34.1,21-44.7c-33.7-7.1-59.7-34.9-64.2-69.6c-8.3,3.1-17.3,4.8-26.6,4.8  c-43.2,0.3-78.4-34.5-78.7-77.7c-0.1-9.8,1.7-19.1,4.9-27.7c-9.6-1.2-19.3-1.9-29.3-1.8C114.6,43,17.9,140.8,18.6,260.8  c0.7,119.9,98.5,216.6,218.5,215.9c101-0.6,185.5-70.1,209.2-163.7C423.8,305.3,407.4,284.1,407.3,258.9z"\n' +
			'   id="path28" />\n' +
			'\n' +
			'<path\n' +
			'   style="fill:currentColor;fill-opacity:1;fill-rule:evenodd;stroke:currentColor;stroke-width:1.56191504;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"\n' +
			'   d="M 225.45617,459.64044 C 159.95942,455.75161 101.25194,421.32661 66.414519,366.38132 49.50561,339.71273 39.455674,310.08211 36.261607,277.48031 c -1.068105,-10.90214 -0.424896,-36.48736 1.168453,-46.47807 5.710872,-35.80867 18.319841,-65.6428 39.552029,-93.58421 6.96466,-9.16545 26.427521,-28.87029 35.157221,-35.59435 35.14026,-27.066782 73.42117,-40.836496 118.73281,-42.708408 l 11.67446,-0.482294 -0.24578,10.272429 c -0.409,17.093648 2.71993,30.893359 10.39069,45.826763 13.54112,26.36174 38.67206,44.8372 68.05574,50.03236 5.56484,0.98388 22.30125,1.48728 29.02027,0.87286 1.48556,-0.13584 2.07236,0.7088 4.53764,6.53154 8.69574,20.5384 23.60243,37.07627 44.20488,49.04205 1.17794,0.68413 1.09956,1.13877 -0.98838,5.7334 -1.24728,2.74468 -3.17286,7.92753 -4.27906,11.51745 -1.87177,6.07432 -2.0108,7.45493 -2.00418,19.90221 0.006,11.40876 0.24301,14.22542 1.61169,19.15893 4.44199,16.01149 14.68824,31.04138 27.77601,40.74375 5.97388,4.42862 5.87527,3.55285 1.62185,14.40346 -9.73316,24.82963 -25.15676,48.31092 -44.44889,67.67011 -16.89264,16.95136 -31.25226,27.4099 -52.02961,37.89472 -20.34103,10.26466 -40.3152,16.54582 -62.17024,19.55032 -10.52153,1.44644 -29.48906,2.36894 -38.14304,1.85511 z m -44.61456,-45.99975 c 13.13238,-3.42055 23.51915,-13.65386 27.45328,-27.04766 1.4587,-4.96619 1.53592,-15.38735 0.15251,-20.58288 -3.42132,-12.84912 -13.95299,-23.44756 -27.15248,-27.32461 -5.13308,-1.50772 -14.78951,-1.52316 -20.51142,-0.0328 -13.26232,3.45439 -24.96226,15.74575 -27.76039,29.16365 -2.78163,13.3388 0.98984,25.92156 10.62775,35.45731 8.32609,8.23784 15.65317,11.3642 26.84842,11.45584 3.28578,0.0269 7.93982,-0.4631 10.34233,-1.08887 z m 128.61014,0.0216 c 14.96464,-3.77142 28.02704,-15.34526 33.37865,-29.57489 9.4766,-25.19776 -3.83558,-53.45507 -29.32146,-62.2396 -6.6123,-2.27914 -19.96016,-2.85229 -26.76937,-1.14946 -8.36958,2.09305 -15.29382,6.09969 -21.96181,12.70798 -6.57439,6.51551 -9.67484,11.37765 -12.32399,19.32649 -2.3167,6.95128 -2.49549,21.53942 -0.34151,27.8648 5.66877,16.64686 18.74318,29.22452 34.28402,32.98142 5.9404,1.43605 17.52178,1.47788 23.05547,0.0833 z m -93.13466,-97.04605 c 18.21741,-3.799 30.51387,-21.467 27.48399,-39.48993 -3.2093,-19.09013 -21.02798,-32.07388 -39.70792,-28.93354 -15.17888,2.55177 -26.59488,14.11462 -28.91902,29.29099 -2.70756,17.68017 9.08981,35.08401 26.31916,38.8268 5.83763,1.26813 9.81523,1.35015 14.82379,0.30568 z m -107.32816,-6.98513 c 14.96159,-3.52755 25.95045,-15.3248 28.54707,-30.64714 4.01401,-23.68611 -18.2437,-45.94382 -41.929804,-41.92981 -16.26022,2.75556 -28.399854,14.8952 -31.155422,31.15542 -4.217708,24.8881 19.824876,47.24827 44.538156,41.42153 z M 320.38766,275.4792 c 11.08907,-2.34987 22.01711,-10.8915 26.67843,-20.85253 7.16438,-15.30992 4.62169,-31.48309 -6.88325,-43.7819 -14.94863,-15.98014 -40.20835,-16.18229 -55.65329,-0.44541 -14.89605,15.17761 -14.7546,39.72051 0.31449,54.56869 9.58985,9.44926 22.51595,13.27184 35.54362,10.51115 z M 193.91822,216.0011 c 21.4835,-5.55564 37.01407,-24.50363 38.3843,-46.83056 1.40478,-22.88978 -12.90284,-44.45059 -34.50988,-52.00451 -4.63929,-1.62192 -7.52684,-2.08839 -14.69097,-2.37327 -10.79905,-0.42942 -16.44695,0.69686 -25.63561,5.11213 -8.44875,4.05974 -18.30904,13.77754 -22.49509,22.17002 -7.46131,14.95895 -7.68382,31.81791 -0.61171,46.34596 9.15116,18.79894 27.89471,30.07096 48.41304,29.11468 3.497,-0.16298 8.51266,-0.85348 11.14592,-1.53445 z"\n' +
			'   id="path844"\n' +
			'   inkscape:connector-curvature="0" /></svg>';

		fingerprintContainer.addEventListener('click', function (e) {
			SgCookieOptin.openCookieOptin();
		});

		// iconSvg.appendChild(iconPath);
		// fingerprintContainer.appendChild(iconSvg);
		document.body.appendChild(fingerprintContainer);

		// Emit event
		var fingerprintCreatedEvent = new CustomEvent('fingerprintCreated', {
			bubbles: true,
			detail: {}
		});
		document.dispatchEvent(fingerprintCreatedEvent);

		return fingerprintContainer;
	}
};

SgCookieOptin.run();
