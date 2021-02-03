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

use http\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SGalinski\SgCookieOptin\Service\LicenceCheckService;
use SGalinski\SgCookieOptin\Service\OptinHistoryService;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Http\Response;

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

	public function searchUserHistory(
		ServerRequestInterface $request,
		ResponseInterface $response = NULL
	) {
		if ($response === NULL) {
			$response = new Response();
		}

		$params = [
			'from_date' => '2021-01-31',
			'to_date' => '2021-01-01',
			'user_hash' => '',
			'page' => 1,
			'per_page' => 10
		];

		try {
			$result = OptinHistoryService::searchUserHistory($params);
			$response->getBody()->write(json_encode($result));
		} catch (RuntimeException $exception) {
			$response->withStatus(500, $exception->getMessage());
		}

		return $response;
	}

	public function searchUserHistoryChart(
		ServerRequestInterface $request,
		ResponseInterface $response = NULL
	) {
		if ($response === NULL) {
			$response = new Response();
		}

		if (!isset($request->getParsedBody()['params'])) {
			//TODO: error
			throw new RuntimeException('Bad');
		}

		$params = json_decode($request->getParsedBody()['params'], TRUE);
		try {
			$result = OptinHistoryService::searchUserHistory($params);
			$response->getBody()->write(json_encode($result));
		} catch (RuntimeException $exception) {
			$response->withStatus(500, $exception->getMessage());
		}

		return $response;
	}
}
