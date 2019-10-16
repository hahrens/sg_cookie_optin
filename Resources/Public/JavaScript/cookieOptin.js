/***************************************************************
 *  Copyright notice
 *
 *  (c) sgalinski Internet Services (https://www.sgalinski.de)
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

define([
	'jquery',
	'TYPO3/CMS/Backend/ModuleMenu',
	'TYPO3/CMS/Backend/Viewport'
], function($, ModuleMenu, Viewport) {
	'use strict';

	var SgRoutes = {
		init: function() {
			$('.btn-delete-all').on('click', SgRoutes.deleteAllListener);
			$.get(TYPO3.settings.ajaxUrls['sg_routes::ajaxPing']);
			$('.sg-routes_pageswitch').on('click', function(event) {
				event.preventDefault();
				SgRoutes.goTo('web_SgRoutesRoute', event.target.dataset.page);
			});
		},
		/**
		 * Deletes all routes
		 *
		 * @param _event
		 */
		deleteAllListener: function(_event) {
			_event.preventDefault();

			var confirm = TYPO3.lang['backend.delete_all'];
			if (window.confirm(confirm)) {
				window.location = $(_event.currentTarget).attr('href');
			}
		},
		/**
		 * opens the selected category edit form
		 *
		 * @return {boolean}
		 */
		editSelectedCategory: function() {
			var selected = $('#filter-categories').val();
			if (selected) {
				var editLink = $('#sg-routes-categoryeditlinks-' + selected[0]);
				if (editLink.length > 0) {
					window.location.href = editLink.data('url') + '&returnUrl=' + T3_THIS_LOCATION;
				}
			}
		},
		goTo: function(module, id) {
			var pageTreeNodes = Viewport.NavigationContainer.PageTree.instance.nodes;
			for (var nodeIndex in pageTreeNodes) {
				if (pageTreeNodes.hasOwnProperty(nodeIndex) && pageTreeNodes[nodeIndex].identifier === parseInt(id)) {
					Viewport.NavigationContainer.PageTree.selectNode(pageTreeNodes[nodeIndex]);
					break;
				}
			}
			ModuleMenu.App.showModule(module, 'id=' + id);
		}
	};

	TYPO3.SgRoutes = SgRoutes;

	SgRoutes.init();

	return SgRoutes;
});
