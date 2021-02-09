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

define(['jquery', 'TYPO3/CMS/SgCookieOptin/Backend/Chart.js/Chart.min'], function($, Chart) {
		'use strict';
		var Statistics = {

			/**
			 * @var Contains the state of the current search
			 */
			params: {
				from_date: document.getElementById('from_date').value,
				to_date: document.getElementById('to_date').value,
				user_hash: document.getElementById('user_hash').value.trim(),
				item_identifier: document.getElementById('item_identifier').value,
				page: 1,
				pid: 0,
				per_page: 100
			},

			/**
			 * @var How many pages to show in the pagination
			 */
			maxPages: 10,

			/**
			 * Initialize the history search
			 */
			init: function() {
				this.setDefaultValues();
				this.setEventListeners();
				var params = this.getParameterValuesFromForm();
				var url = new URL(document.location);
				params.pid = parseInt(url.searchParams.get('id'));
				this.setParams(params);
				this.performSearch(this.getParams());
			},

			/**
			 * Re-run the search with the new selected parameters and update the grid
			 */
			refreshSearch: function() {
				this.setParams(this.getParameterValuesFromForm());
				this.performSearch(this.getParams());
			},

			/**
			 * Reads the current filter form values
			 *
			 * @returns {{per_page: number, from_date, to_date, user_hash, pid: number, page: number, item_identifier}}
			 */
			getParameterValuesFromForm: function() {
				return {
					from_date: document.getElementById('from_date').value,
					to_date: document.getElementById('to_date').value,
					user_hash: document.getElementById('user_hash').value.trim(),
					item_identifier: document.getElementById('item_identifier').value,
					page: 1,
					per_page: this.params.per_page,
					pid: this.params.pid
				};
			},

			/**
			 * Returns the current parameters state
			 *
			 * @returns {{per_page: number, from_date: *, to_date: *, user_hash: *, pid: number, page: number, item_identifier: *}}
			 */
			getParams: function() {
				return this.params;
			},

			/**
			 * Sets the current parameters state
			 *
			 * @param {Object} params
			 */
			setParams: function(params) {
				this.params = params;
			},

			/**
			 * Sets the default event listeners
			 */
			setEventListeners: function() {
				$('#consent-statistics-submit').on({
					click: this.refreshSearch.bind(this)
				});

				document.getElementById('consent-statistics-form').addEventListener('submit', function(event) {
					event.preventDefault();
					this.refreshSearch();
					return false;
				}.bind(this), false);
			},

			/**
			 * Sets the initial default values to the filter form elements
			 */
			setDefaultValues: function() {
				const today = new Date();
				document.getElementById('to_date').value = today.getFullYear().toString() + '-'
					+ (today.getMonth() + 1).toString().padStart(2, 0)
					+ '-' + today.getDate().toString().padStart(2, 0);
				const prevMonth = new Date();
				prevMonth.setMonth(prevMonth.getMonth() - 1);
				document.getElementById('from_date').value = prevMonth.getFullYear().toString() + '-'
					+ (prevMonth.getMonth() + 1).toString().padStart(2, 0)
					+ '-' + prevMonth.getDate().toString().padStart(2, 0);
			},

			/**
			 * Search with the given params and update the grid
			 *
			 * @param {Object} params
			 */
			performSearch: function(params) {
				var request = new XMLHttpRequest();
				request.open('POST', TYPO3.settings.ajaxUrls['sg_cookie_optin::searchUserPreferenceHistory'], true);
				var formData = new FormData();
				formData.append('params', JSON.stringify(params));
				request.that = this;

				request.onload = function() {
					if (this.status >= 200 && this.status < 400) {
						// Success!
						const data = JSON.parse(this.response);
						this.that.updateTable(data);
					} else {
						// We reached our target server, but it returned an error
						this.onSearchError();
					}
				};

				request.onerror = this.onSearchError;
				request.send(formData);
			},

			/**
			 * Handles errors in the search
			 *
			 * @param {Object} error
			 */
			onSearchError: function(error) {
				console.log(error);
			},

			/**
			 * Updates the results grid
			 *
			 * @param {Array} data
			 */
			updateTable: function(data) {
				var tableBody = document.querySelector('#consent-statistics-grid tbody');
				while (tableBody.firstChild) {
					tableBody.firstChild.remove();
				}

				for (var index = 0; index < data.data.length; ++index) {
					this.addTableRow(tableBody, data.data[index]);
				}
				this.addPagination(tableBody, data.count);
			},

			/**
			 * Adds a row to the results grid
			 *
			 * @param {HTMLElement} tableBody
			 * @param {Object} dataRow
			 */
			addTableRow: function(tableBody, dataRow) {
				var tr = document.createElement('TR');
				var td = document.createElement('TD');
				td.innerText = dataRow.tstamp;
				tr.append(td);

				td = document.createElement('TD');
				td.innerText = dataRow.user_hash;
				tr.append(td);

				td = document.createElement('TD');
				td.innerText = dataRow.item_identifier;
				tr.append(td);

				td = document.createElement('TD');
				var i = document.createElement('I');

				if (dataRow.is_accepted) {
					i.className = 'fa fa-check-circle consent-green';
				} else {
					i.className = 'fa fa-times-circle consent-red';
				}

				td.append(i);
				tr.append(td);

				tableBody.append(tr);
			},

			/**
			 * Creates a pagination item
			 *
			 * @param {int} number
			 * @param {boolean} isActive
			 * @returns {HTMLElement}
			 */
			createPaginationItem: function(number, isActive) {
				var li = document.createElement('LI');
				li.className = 'page-item' + (isActive ? ' active' : '');
				var a = document.createElement('A');
				a.className = 'page-link';
				a.href = '#';
				a.innerText = number;
				li.append(a);
				return li;
			},

			/**
			 * Adds the pagination to the grid
			 *
			 * @param {HTMLElement} tableBody
			 * @param {int} count
			 */
			addPagination: function(tableBody, count) {
				var params = this.getParams();
				var pageCount = Math.ceil(count / this.getParams().per_page);
				var paginationDiv = document.getElementById('consent-statistics-grid-page-select');
				while (paginationDiv.firstChild) {
					paginationDiv.firstChild.remove();
				}

				var lowerLimit, upperLimit;
				lowerLimit = upperLimit = Math.min(params.page, pageCount);

				for (var buttonIndex = 1; buttonIndex < this.maxPages && buttonIndex < pageCount;) {
					if (lowerLimit > 1) {
						lowerLimit--;
						buttonIndex++;
					}
					if (buttonIndex < this.maxPages && upperLimit < pageCount) {
						upperLimit++;
						buttonIndex++;
					}
				}

				if (lowerLimit > 1) { // always show the first page
					paginationDiv.append(this.createPaginationItem(1, false));
					paginationDiv.append(this.createPaginationSpacer(true));
				}

				for (var i = lowerLimit; i <= upperLimit; i++) {
					//creating the page items
					paginationDiv.append(this.createPaginationItem(i, (i === params.page)));
				}

				if (upperLimit < pageCount) { // always show the last page
					paginationDiv.append(this.createPaginationSpacer());
					paginationDiv.append(this.createPaginationItem(pageCount, false));
				}

				$('.page-link').click({data: this}, function() {
					$("#consent-statistics-grid-page-select li").removeClass("active");
					$(event.target.parentElement).addClass('active');
					this.showPage(parseInt($(event.target).text())).bind(this);
				}.bind(this));
			},

			/**
			 * Creates a pagination spacer
			 *
			 * @param {boolean} isLeft
			 * @returns {HTMLElement}
			 */
			createPaginationSpacer: function(isLeft = false) {
				var li = document.createElement('LI');
				li.className = 'page-item';
				var a = document.createElement('A');
				a.href = '#';

				if (isLeft) {
					a.innerHTML = '&laquo;';
				} else {
					a.innerHTML = '&raquo;';
				}

				a.className = 'page-link disabled';
				li.append(a);
				return li;
			},

			/**
			 * Makes a new search request for the given page number
			 *
			 * @param {int} num
			 */
			showPage: function(num) {
				var params = this.getParams();
				params.page = num;
				this.setParams(params);
				this.performSearch(params);
			},
		};

		Statistics.init();
		return Statistics;
	}
);
