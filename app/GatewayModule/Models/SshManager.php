<?php

/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
use App\GatewayModule\Exceptions\SshKeyExistsException;
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
	 * @var string|null Path to SSH directory
	 */
	private readonly ?string $directory;

	/**
	 * @var PrivilegedFileManager|null Privileged file manager
	 */
	private readonly ?PrivilegedFileManager $fileManager;

	/**
	 * @var SshKeyRepository SSH key repository
	 */
	private readonly SshKeyRepository $sshKeyRepository;

	/**
	 * Constructor
	 * @param CommandManager $commandManager Command manager
	 * @param EntityManager $entityManager Entity manager
	 * @param FeatureManager $featureManager Feature manager
	 */
	public function __construct(
		private readonly CommandManager $commandManager,
		private readonly EntityManager $entityManager,
		FeatureManager $featureManager,
	) {
		$feature = $featureManager->get('gatewayPass');
		$userInfo = posix_getpwnam($feature['user']);
		if ($userInfo !== false) {
			$this->directory = $userInfo['dir'] . '/.ssh';
			$this->fileManager = new PrivilegedFileManager($this->directory, $commandManager);
		} else {
			$this->directory = null;
			$this->fileManager = null;
		}
		$this->sshKeyRepository = $entityManager->getSshKeyRepository();
	}

	/**
	 * Returns array of supported key types
	 * @return array<int, string> Array of supported key types
	 * @throws SshUtilityException
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
	 * @return array<int, SshKey> List of existing SSH public keys
	 * @throws SshDirectoryException
	 */
	public function listKeys(): array {
		$this->updateKeysFile();
		return $this->sshKeyRepository->findAll();
	}

	/**
	 * Returns SSH public key
	 * @param int $id SSH public key ID
	 * @return SshKey SSH public key entity
	 * @throws SshDirectoryException
	 * @throws SshKeyNotFoundException
	 */
	public function getKey(int $id): SshKey {
		$this->updateKeysFile();
		$key = $this->sshKeyRepository->find($id);
		if (!($key instanceof SshKey)) {
			throw new SshKeyNotFoundException('SSH key entry with ID ' . $id . ' not found.');
		}
		return $key;
	}

	/**
	 * Adds SSH public keys to authorized keys
	 * @param array<int, array<string, string>> $items SSH public keys
	 * @return array<int, string> Array of keys that could not be created
	 * @throws SshDirectoryException
	 * @throws SshInvalidKeyException
	 * @throws SshKeyExistsException
	 */
	public function addKeys(array $items): array {
		$failedKeys = [];
		foreach ($items as $item) {
			$entity = $this->createKeyEntity($item);
			$dbEntity = $this->sshKeyRepository->findByHash($entity->getHash());
			if ($dbEntity !== null) {
				$failedKeys[] = $item['description'];
				continue;
			}
			$this->entityManager->persist($entity);
			$this->entityManager->flush();
		}
		if (count($failedKeys) === count($items)) {
			throw new SshKeyExistsException('Duplicate SSH key(s).');
		}
		$this->updateKeysFile();
		return $failedKeys;
	}

	/**
	 * Removes SSH public key from database
	 * @param int $id SSH public key ID
	 * @throws SshDirectoryException
	 * @throws SshKeyNotFoundException
	 */
	public function deleteKey(int $id): void {
		$key = $this->sshKeyRepository->find($id);
		if ($key === null) {
			throw new SshKeyNotFoundException('SSH key entry with ID ' . $id . ' not found.');
		}
		$this->entityManager->remove($key);
		$this->entityManager->flush();
		$this->updateKeysFile();
	}

	/**
	 * Updates the authorized keys file based on the contents of the database
	 * @throws SshDirectoryException
	 */
	public function updateKeysFile(): void {
		$this->checkSshDirectory();
		$keys = $this->sshKeyRepository->findAll();
		$content = implode(PHP_EOL, array_map(static fn (SshKey $key): string => $key->toString(), $keys));
		$this->fileManager->write(self::KEYS_FILE, $content);
	}

	/**
	 * Checks if SSH directory exists
	 * @throws SshDirectoryException
	 */
	private function checkSshDirectory(): void {
		$path = $this->directory . '/' . self::KEYS_FILE;
		if (!file_exists($this->directory)) {
			$command = $this->commandManager->run('install -m 755 -d ' . escapeshellarg($this->directory), true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
		}
		if (!file_exists($path)) {
			$command = $this->commandManager->run('touch ' . escapeshellarg($path), true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
			$command = $this->commandManager->run('chmod 644 ' . escapeshellarg($path), true);
			if ($command->getExitCode() !== 0) {
				throw new SshDirectoryException($command->getStderr());
			}
		}
	}

	/**
	 * Validates SSH key and returns key entity
	 * @param array<string, string> $item SSH key string
	 * @return SshKey SSH key entity
	 * @throws SshInvalidKeyException
	 */
	private function createKeyEntity(array $item): SshKey {
		$tokens = explode(' ', $item['key'], 3);
		if ($this->sshKeyRepository->findByKey($tokens[1]) !== null) {
			throw new SshKeyExistsException('SSH key already exists.');
		}
		$command = $this->commandManager->run('ssh-keygen -l -E sha256 -f /dev/stdin', true, 60, $item['key']);
		if ($command->getExitCode() !== 0) {
			throw new SshInvalidKeyException('Submitted key is not a valid SSH public key.');
		}
		$hash = explode(' ', $command->getStdout())[1];
		if ($this->sshKeyRepository->findByHash($hash) !== null) {
			throw new SshKeyExistsException('SSH key already exists.');
		}
		$description = $item['description'];
		return new SshKey($tokens[0], $tokens[1], $hash, $description);
	}

}
