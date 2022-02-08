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

use SGalinski\SgCookieOptin\Service\OptinHistoryService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Bootstrap;

class DeleteUsageHistoryCommand extends Command {

	/**
	 * Configure the command by defining the name, options and arguments
	 */
	protected function configure() {
		$this->setHelp(
			'Deletes the user preferences history.' . LF . 'If you want to get more detailed information, use the --verbose option.'
		);
		$this->setDescription('Delete the user preferences history that is older than X days.')
			->addArgument(
				'olderThan',
				InputArgument::REQUIRED,
				'older than X days'
			)
			->addArgument(
				'pid',
				InputArgument::REQUIRED,
				'PID (0 = will delete for all PIDs)'
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

			$olderThan = (int) $input->getArgument('olderThan');
			$pid = (int) $input->getArgument('pid');
			OptinHistoryService::deleteOlderThan($olderThan, $pid);
		} catch (\Exception $exception) {
			$this->io->writeln('Error!');
			$this->io->writeln($exception->getMessage());
			return Command::FAILURE;
		}

		$this->io->writeln('All entries older than ' . $olderThan . ' days have been deleted');
		return 0;
	}
}
