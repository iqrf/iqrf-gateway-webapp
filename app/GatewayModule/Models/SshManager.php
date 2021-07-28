<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\GatewayModule\Models;

use App\CoreModule\Models\CommandManager;
use App\CoreModule\Models\FeatureManager;
use App\CoreModule\Models\PrivilegedFileManager;
use App\GatewayModule\Exceptions\SshDirectoryException;
use App\GatewayModule\Exceptions\SshInvalidKeyException;
use App\GatewayModule\Exceptions\SshKeyNotFoundException;
use App\GatewayModule\Exceptions\SshUtilityException;
use App\Models\Database\Entities\SshKey;
use App\Models\Database\EntityManager;
use App\Models\Database\Repositories\SshKeyRepository;

/**
 * SSH manager
 */
class SshManager {

	/**
	 * SSH authorized keys file
	 */
	private const KEYS_FILE = 'authorized_keys';

	/**
	 * @var CommandManager Command manager
	 */
	private $commandManager;

	/**
	 * @var string Path to SSH directory
	 */
	private $directory;

	/**
	 * @var EntityManager Entity manager
	 */
	private $entityManager;

	/**
	 * @var PrivilegedFileManager Privileged file manager
	 */
	private $fileManager;

	/**
	 * @var SshKeyRepository SSH key repository
	 */
	private $sshKeyRepository;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param EntityManager $entityManager Entity manager
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(CommandManager $commandManager, EntityManager $entityManager, FeatureManager $featureManager) {
		$this->commandManager = $commandManager;
		$feature = $featureManager->get('gatewayPass');
		$this->directory = posix_getpwnam($feature['user'])['dir'] . '/.ssh';
		$this->entityManager = $entityManager;
		$this->fileManager = new PrivilegedFileManager($this->directory, $commandManager);
		$this->sshKeyRepository = $entityManager->getSshKeyRepository();
	}

	/**
	 * Returns array of supported key types
	 * @return array<int, string> Array of supported key types
	 */
	public function listKeyTypes(): array {
		$command = $this->commandManager->run('ssh -Q key', true);
		if ($command->getExitCode() !== 0) {
			throw new SshUtilityException($command->getStderr());
		}
		return explode(PHP_EOL, $command->getStdout());
	}

	/**
	 * Returns array of existing SSH public keys
	 * @return array<int, array<string, int|string|null>> List of existing SSH public keys
	 */
	public function listKeys(): array {
		$this->updateKeysFile();
		$array = [];
		$keys = $this->sshKeyRepository->findAll();
		foreach ($keys as $key) {
			assert($key instanceof SshKey);
			$array[] = $key->jsonSerialize();
		}
		return $array;
	}

	/**
	 * Returns SSH public key
	 * @param int $id SSH public key ID
	 * @return SshKey SSH public key entity
	 */
	public function getKey(int $id): SshKey {
		$this->updateKeysFile();
		$key = $this->sshKeyRepository->find($id);
		if ($key === null) {
			throw new SshKeyNotFoundException('SSH key entry with ID ' . strval($id) . ' not found.');
		}
		assert($key instanceof SshKey);
		return $key;
	}

	/**
	 * Adds SSH public keys to authorized keys
	 * @param array<int, string> $keys SSH public keys
	 */
	public function addKeys(array $keys): void {
		$entities = [];
		foreach ($keys as $key) {
			$entities[] = $this->createKeyEntity($key);
		}
		foreach ($entities as $entity) {
			$this->entityManager->persist($entity);
		}
		$this->entityManager->flush();
		$this->updateKeysFile();
	}

	/**
	 * Validates SSH key and returns key entity
	 * @param string $key SSH key string
	 * @return SshKey SSH key entity
	 */
	private function createKeyEntity(string $key): SshKey {
		$command = $this->commandManager->run('ssh-keygen -l -E sha256 -f /dev/stdin', true, 60, $key);
		if ($command->getExitCode() !== 0) {
			throw new SshInvalidKeyException('Submitted key is not a valid SSH public key.');
		}
		$tokens = explode(' ', $key);
		$hash = explode(' ', $command->getStdout())[1];
		$description = count($tokens) === 3 ? $tokens[2] : null;
		return new SshKey($tokens[0], $tokens[1], $hash, $description);
	}

	/**
	 * Removes SSH public key from database
	 * @param int $id SSH public key ID
	 */
	public function deleteKey(int $id): void {
		$key = $this->sshKeyRepository->find($id);
		if ($key === null) {
			throw new SshKeyNotFoundException('SSH key entry with ID ' . strval($id) . ' not found.');
		}
		$this->entityManager->remove($key);
		$this->entityManager->flush();
		$this->updateKeysFile();
	}

	/**
	 * Updates the authorized keys file based on the contents of the database
	 */
	private function updateKeysFile(): void {
		$this->checkSshDirectory();
		$keys = $this->sshKeyRepository->findAll();
		$content = '';
		foreach ($keys as $key) {
			assert($key instanceof SshKey);
			$content .= $key->toString() . PHP_EOL;
		}
		$this->fileManager->write(self::KEYS_FILE, $content);
	}

	/**
	 * Checks if SSH directory exists
	 */
	private function checkSshDirectory(): void {
		$path = $this->directory . '/' . self::KEYS_FILE;
		if (!file_exists($this->directory)) {
			$command = $this->commandManager->run('install -m 755 -d ' . $this->directory, true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
		}
		if (!file_exists($path)) {
			$command = $this->commandManager->run('touch ' . $path, true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
			$command = $this->commandManager->run('chmod 644 ' . $path, true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
		}
	}

}
