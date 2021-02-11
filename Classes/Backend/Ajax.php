<?php

namespace SGalinski\SgCookieOptin\Backend;

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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SGalinski\SgCookieOptin\Exception\SearchOptinHistoryException;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use SGalinski\SgCookieOptin\Service\OptinHistoryService;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class Ajax
 *
 * @package SGalinski\SgAccount\Backend
 */
class Ajax {
	/**
	 * Checks whether the license is valid
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface $response
	 * @return ResponseInterface
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	public function checkLicense(
		ServerRequestInterface $request,
		ResponseInterface $response = NULL
	) {
		if ($response === NULL) {
			$response = new Response();
		}

		LicenceCheckService::setLastAjaxNotificationCheckTimestamp();
		$responseData = LicenceCheckService::getLicenseCheckResponseData(TRUE);
		$response->getBody()->write(json_encode($responseData));
		return $response;
	}

	/**
	 * Searches the user preferences history
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface|null $response
	 * @return ResponseInterface|Response|null
	 */
	public function searchUserPreferenceHistory(
		ServerRequestInterface $request,
		ResponseInterface $response = NULL
	) {
		if ($response === NULL) {
			$response = new Response();
		}

		try {
			if (!isset($request->getParsedBody()['params'])) {
				throw new SearchOptinHistoryException('No parameters sent to the server.');
			}

			$params = json_decode($request->getParsedBody()['params'], TRUE);

			$data = OptinHistoryService::searchUserHistory($params)->execute()->fetchAllAssociative();
			$count = OptinHistoryService::searchUserHistory($params, TRUE)->execute()->fetchAllAssociative();
			$result = [
				'data' => $data,
				'count' => end($count[0])
			];
			$response->getBody()->write(json_encode($result));
		} catch (SearchOptinHistoryException $exception) {
			$response->withStatus(500, $exception->getMessage());
		}

		return $response;
	}

	/**
	 * Generates the chart data objects of the user preference search
	 *
	 * @param ServerRequestInterface $request
	 * @param ResponseInterface|null $response
	 * @return ResponseInterface|Response|null
	 */
	public function searchUserPreferenceHistoryChart(
		ServerRequestInterface $request,
		ResponseInterface $response = NULL
	) {
		if ($response === NULL) {
			$response = new Response();
		}

		try {
			if (!isset($request->getParsedBody()['params'])) {
				throw new SearchOptinHistoryException('No parameters sent to the server.');
			}

			$params = json_decode($request->getParsedBody()['params'], TRUE);

			$data = [];
			$identifiers = OptinHistoryService::getItemIdentifiers(['pid' => $params['pid']]);
			$params['groupBy'] = ['item_type', 'item_identifier', 'is_accepted'];

			foreach ($identifiers as $identifier) {
				if ($identifier === 'essential') {
					continue;
				}

				$params['item_type'] = OptinHistoryService::TYPE_GROUP;
				$params['item_identifier'] = $identifier;
				$params['countField'] = 'item_identifier';

				$acceptedKey = LocalizationUtility::translate('backend.statistics.accepted', 'sg_cookie_optin');
				$rejectedKey = LocalizationUtility::translate('backend.statistics.rejected', 'sg_cookie_optin');

				$data[$identifier][$acceptedKey] = [
					'value' => 0,
					'color' => '#009146'
				];
				$data[$identifier][$rejectedKey] = [
					'value' => 0,
					'color' => '#C41700'
				];

				$identifierData = OptinHistoryService::searchUserHistory($params, TRUE)->execute()->fetchAllAssociative();
				foreach ($identifierData as $values) {
					if ($values['is_accepted']) {
						$data[$identifier][$acceptedKey]['value'] = $values['count_' . $params['countField']];
					} else {
						$data[$identifier][$rejectedKey]['value'] = $values['count_' . $params['countField']];
					}
				}
			}

			$response->getBody()->write(json_encode($data));
		} catch (SearchOptinHistoryException $exception) {
			$response->withStatus(500, $exception->getMessage());
		}

		return $response;
	}
}
