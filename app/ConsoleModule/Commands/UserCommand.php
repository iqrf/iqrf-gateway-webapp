<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace App\ConsoleModule\Commands;

use App\Exceptions\InvalidUserRoleException;
use App\Models\Database\Entities\User;
use App\Models\Database\EntityManager;
use App\Models\Database\Enums\UserLanguage;
use App\Models\Database\Enums\UserRole;
use App\Models\Database\Repositories\UserRepository;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * User base command
 */
abstract class UserCommand extends EntityManagerCommand {

	/**
	 * @var UserRepository User database repository
	 */
	protected readonly UserRepository $repository;

	/**
	 * Constructor
	 * @param EntityManager $entityManager Entity manager
	 */
	public function __construct(
		EntityManager $entityManager,
	) {
		parent::__construct($entityManager);
		$this->repository = $entityManager->getUserRepository();
	}

	/**
	 * Asks for the username
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @return User Information about the user
	 * @throws RuntimeException Question helper not found
	 */
	protected function findUserByName(InputInterface $input, OutputInterface $output): User {
		$username = $input->getOption('username');
		$user = null;
		if ($username !== null) {
			$user = $this->repository->findOneByUserName($username);
		}
		while (!($user instanceof User)) {
			$helper = $this->getQuestionHelper();
			$userNames = $this->repository->listUserNames();
			$question = new ChoiceQuestion('Please enter the username: ', $userNames);
			$username = $helper->ask($input, $output, $question);
			$user = $this->repository->findOneByUserName($username);
		}
		return $user;
	}

	/**
	 * Asks for the user's language
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @param UserLanguage|null $default Default user's language
	 * @return UserLanguage User's language
	 * @throws RuntimeException Question helper not found
	 */
	protected function askLanguage(InputInterface $input, OutputInterface $output, ?UserLanguage $default): UserLanguage {
		$language = $input->getOption('language');
		$language = $language !== null ? UserLanguage::tryFrom($language) : null;
		$languages = array_column(UserLanguage::cases(), 'value');
		while ($language === null) {
			$helper = $this->getQuestionHelper();
			$question = new ChoiceQuestion('Please enter the user\'s language: ', $languages, $default->value);
			$language = UserLanguage::tryFrom($helper->ask($input, $output, $question));
		}
		return $language;
	}

	/**
	 * Asks for the user's role
	 * @param InputInterface $input Command input
	 * @param OutputInterface $output Command output
	 * @param UserRole|null $default Default user's role
	 * @return UserRole User's role
	 * @throws InvalidUserRoleException Role does not exist
	 * @throws RuntimeException Question helper not found
	 */
	protected function askRole(InputInterface $input, OutputInterface $output, ?UserRole $default): UserRole {
		$role = $input->getOption('role');
		if ($role !== null) {
			return UserRole::fromString($role);
		}
		$roles = array_column(UserRole::cases(), 'value');
		while ($role === null) {
			$helper = $this->getQuestionHelper();
			$question = new ChoiceQuestion('Please enter the user\'s role: ', $roles, $default?->value);
			$role = UserRole::tryFrom($helper->ask($input, $output, $question));
		}
		return $role;
	}

}
