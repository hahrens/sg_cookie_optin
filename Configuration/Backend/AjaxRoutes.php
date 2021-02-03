<?php

return [
	'sg_cookie_optin::checkLicense' => [
		'path' => '/sg_cookie_optin/checkLicense',
		'target' => SGalinski\SgCookieOptin\Backend\Ajax::class . '::checkLicense',
	],
	'sg_cookie_optin::searchUserHistory' => [
		'path' => '/sg_cookie_optin/searchUserHistory',
		'target' => SGalinski\SgCookieOptin\Backend\Ajax::class . '::searchUserHistory',
	],
];
