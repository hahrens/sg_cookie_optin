<?php

namespace SGalinski\SgCookieOptin\Command;

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

use SGalinski\SgCookieOptin\Service\StaticFileGenerationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GenerateStaticFilesCommand extends Command {
	/** @var SymfonyStyle */
	private $io;

	/**
	 * Configure the command by defining the name, options and arguments
	 */
	protected function configure() {
		$this->setHelp('Generates the necessary JavaScript, JSON and CSS files.' . LF . 'If you want to get more detailed information, use the --verbose option.');
		$this->setDescription('Generates the necessary JavaScript, JSON and CSS files.')
			->addArgument(
				'siteRootId',
				InputArgument::REQUIRED,
				'The site root ID'
			);
	}

	/**
	 * Executes the command for showing sys_log entries
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int error code
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		try {
			$this->io = new SymfonyStyle($input, $output);
			$this->io->title($this->getDescription());
			Bootstrap::initializeBackendAuthentication();

			$siteRootId = (int) $input->getArgument('siteRootId');
			$originalRecord = $this->getOriginalRecord($siteRootId);

			$service = GeneralUtility::makeInstance(StaticFileGenerationService::class);
			$service->generateFiles($siteRootId, $originalRecord);
		} catch (\Exception $exception) {
			$this->io->writeln('Error!');
			$this->io->writeln($exception->getMessage());
			return 1;
		}

		$this->io->writeln('Your files have been generated successfully');
		return 0;
	}

	/**
	 * Fetches the original record
	 *
	 * @param int $siteRootId
	 * @return array
	 */
	protected function getOriginalRecord(int $siteRootId): array {
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(
			StaticFileGenerationService::TABLE_NAME
		);

		$result = $queryBuilder->select('uid')
			->from(StaticFileGenerationService::TABLE_NAME)
			->where($queryBuilder->expr()->eq('pid', $siteRootId))
			->andWhere($queryBuilder->expr()->eq('l10n_parent', 0))
			->setMaxResults(1)
			->execute();

		if (is_callable([$result, 'fetchOne'])) {
			$uid = $result->fetchOne();
		} else {
			$row = $result->fetch();
			$uid = $row['uid'];
		}

		if (!$uid) {
			throw new RuntimeException('Unable to generate files. There is no configuration for this site root. #' . $siteRootId);
		}

		$originalRecord = BackendUtility::getRecord(StaticFileGenerationService::TABLE_NAME, $uid);
		if (isset($originalRecord['l10n_parent']) && (int) $originalRecord['l10n_parent'] > 0) {
			$originalRecord = BackendUtility::getRecord(StaticFileGenerationService::TABLE_NAME, (int) $originalRecord['l10n_parent']);
		}

		return $originalRecord;
	}
}
