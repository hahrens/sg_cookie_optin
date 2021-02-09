<?php

return [
	'sg_cookie_optin::checkLicense' => [
		'path' => '/sg_cookie_optin/checkLicense',
		'target' => SGalinski\SgCookieOptin\Backend\Ajax::class . '::checkLicense',
	],
	'sg_cookie_optin::searchUserPreferenceHistory' => [
		'path' => '/sg_cookie_optin/searchUserHistory',
		'target' => SGalinski\SgCookieOptin\Backend\Ajax::class . '::searchUserPreferenceHistory',
	],
	'sg_cookie_optin::searchUserPreferenceHistoryChart' => [
		'path' => '/sg_cookie_optin/searchUserHistoryChart',
		'target' => SGalinski\SgCookieOptin\Backend\Ajax::class . '::searchUserPreferenceHistoryChart',
	],
];
