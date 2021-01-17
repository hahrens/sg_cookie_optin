/*
 *
 * Copyright notice
 *
 * (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

define(['jquery', 'TYPO3/CMS/Backend/Notification'], function ($, Notification) {
		'use strict';
		var LicenseCheck = {
			init: function() {
				$.ajax({
					url: TYPO3.settings.ajaxUrls['sg_cookie_optin::checkLicense'],
					dataType: 'text',
					success: function(result) {
						var data = JSON.parse(result);
						switch (data.error) {
							case 1:
								Notification.error(data.title, data.message);
								break;
							case 2:
								Notification.warning(data.title, data.message);
						}
					}
				});
			}
		};

		return LicenseCheck.init();
	}
);
