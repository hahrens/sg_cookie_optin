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

define(['jquery', 'TYPO3/CMS/SgCookieOptin/Backend/Chart.js/Chart.min'], function ($, Chart) {
		'use strict';
		var Statistics = {
			init: function() {
				this.setDefaultValues();
				this.setEventListeners();
				// this.initChart();
				this.performSearch(this.getParams());
			},

			refreshSearch: function() {
				var params = this.getParams();
				this.performSearch(params);
			},

			getParams: function() {
				return {
					from_date: document.getElementById('from_date').value,
					to_date: document.getElementById('to_date').value,
					user_hash: document.getElementById('user_hash').value,
					page: 1,
					per_page: 10
				};
			},

			setEventListeners: function() {
				$('#consent-statistics-submit').on({
					click: this.refreshSearch.bind(this)
				});
			},

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

			initChart: function() {
				const ctx = document.getElementById('myChart');
				const myChart = new Chart(ctx, {
					type: 'bar',
					data: {
						labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
						datasets: [{
							label: '# of Votes',
							data: [12, 19, 3, 5, 2, 3],
							backgroundColor: [
								'rgba(255, 99, 132, 0.2)',
								'rgba(54, 162, 235, 0.2)',
								'rgba(255, 206, 86, 0.2)',
								'rgba(75, 192, 192, 0.2)',
								'rgba(153, 102, 255, 0.2)',
								'rgba(255, 159, 64, 0.2)'
							],
							borderColor: [
								'rgba(255, 99, 132, 1)',
								'rgba(54, 162, 235, 1)',
								'rgba(255, 206, 86, 1)',
								'rgba(75, 192, 192, 1)',
								'rgba(153, 102, 255, 1)',
								'rgba(255, 159, 64, 1)'
							],
							borderWidth: 1
						}]
					},
					options: {
						scales: {
							yAxes: [{
								ticks: {
									beginAtZero: true
								}
							}]
						},
						responsive: true
					}
				});
			},

			performSearch: function(params) {
				console.log(params);
				let request = new XMLHttpRequest();
				request.open('POST', TYPO3.settings.ajaxUrls['sg_cookie_optin::searchUserHistory'], true);
				let formData = new FormData();
				formData.append('params', JSON.stringify(params));

				request.send(formData);

				request.onload = function() {
				  if (this.status >= 200 && this.status < 400) {
				    // Success!
					  const data = JSON.parse(this.response);
					  this.updateTable(data);
				  } else {
				    // We reached our target server, but it returned an error

				  }
				};

				request.onerror = this.onSearchError;

				request.send();
			},

			onSearchError: function(error) {
				console.log(error);
			},

			updateTable: function(data) {
				debugger;
			},
		};

		Statistics.init();
		return Statistics;
	}
);
