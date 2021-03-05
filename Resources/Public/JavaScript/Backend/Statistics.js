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

define(['jquery', 'TYPO3/CMS/SgCookieOptin/Backend/Chart.js/Chart.min'], function($, Chart, Formatter) {
		'use strict';
		var Statistics = {

			chart: null,

			/**
			 * @var Contains the state of the current search
			 */
			params: {
				from_date: document.getElementById('from_date').value,
				to_date: document.getElementById('to_date').value,
				version: document.getElementById('version').value,
				pid: 0,
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
			 */
			getParameterValuesFromForm: function() {
				return {
					from_date: document.getElementById('from_date').value,
					to_date: document.getElementById('to_date').value,
					version: document.getElementById('version').value.trim(),
					pid: this.params.pid
				};
			},

			/**
			 * Returns the current parameters state
			 */
			getParams: function() {
				return this.params;
			},

			/**
			 * Sets the current parameters state
			 *
			 * @param params
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
			 * Removes the old charts and draws new ones
			 *
			 * @param {Object} data
			 */
			updateCharts: function(data) {
				this.removeCharts();
				var chartsContainer = document.getElementById('consent-statistics-charts-container');
				for (var identifier in data) {
					if (!data.hasOwnProperty(identifier)) {
						continue;
					}
					this.addChart(chartsContainer, data[identifier], identifier);
				}
			},

			/**
			 * Renders a chart in the container
			 *
			 * @param {HTMLElement} container
			 * @param {Object} dataEntry
			 * @param {String} identifier
			 */
			addChart: function(container, dataEntry, identifier) {
				const chartDivContainer = document.createElement('DIV');
				chartDivContainer.className = 'consent-statistics-chart-div-container';
				const chartContainer = document.createElement('CANVAS');
				chartDivContainer.append(chartContainer);
				container.append(chartDivContainer);
				chartContainer.setAttribute('width', 200);
				chartContainer.setAttribute('height', 100);

				var labels = [];
				var datasets = [];
				var colors = [];
				for (var label in dataEntry) {
					if (label !== 'length' && dataEntry.hasOwnProperty(label)) {
						labels.push(label);
						datasets.push(dataEntry[label].value);
						colors.push(dataEntry[label].color);
					}
				}

				var chartData = {
					datasets: [
						{
							data: datasets,
							backgroundColor: colors,
						}
					],
					labels: labels,
				}

				this.chart = new Chart(chartContainer, {
					type: 'pie',
					data: chartData,
					options: {
						title: {
							text: identifier,
							display: true,
							position: 'top'
						},
						tooltips: {
							enabled: true,
							callbacks: {
								label: function(tooltipItem, data) {
									var label = data.labels[tooltipItem.index] || '';

									if (label) {
										label += ': ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
									}

									var total = data.datasets[tooltipItem.datasetIndex].data[0] + data.datasets[tooltipItem.datasetIndex].data[1];

									if (total > 0) {
										label += ' (' + Math.round(data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] / total * 100) + '%)';
									}
									return label;
								}
							}
						},
						responsive: true
					}
				});
			},

			/**
			 * Removes all the currently rendered charts
			 */
			removeCharts: function() {
				if (this.chart) {
					this.chart.destroy();
				}

				var chartsContainer = document.getElementById('consent-statistics-charts-container');
				while (chartsContainer.firstChild) {
					chartsContainer.firstChild.remove();
				}
			},

			/**
			 * Search with the given params and update the grid
			 *
			 * @param params
			 */
			performSearch: function(params) {
				var request = new XMLHttpRequest();
				request.open('POST', TYPO3.settings.ajaxUrls['sg_cookie_optin::searchUserPreferenceHistoryChart'], true);
				var formData = new FormData();
				formData.append('params', JSON.stringify(params));
				request.that = this;

				request.onload = function() {
					if (this.status >= 200 && this.status < 400) {
						// Success!
						const data = JSON.parse(this.response);
						if (Object.keys(data).length > 0) {
							document.getElementById('statistics-no-data-found').style.display = 'none';
							setTimeout(function() {this.that.updateCharts(data)}.bind(this), 100);
						} else {
							this.that.removeCharts();
							document.getElementById('statistics-no-data-found').style.display = 'block';
						}
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
			 * @param error
			 */
			onSearchError: function(error) {
				console.log(error);
			},

		};

		Statistics.init();
		return Statistics;
	}
);
