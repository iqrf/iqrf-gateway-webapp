<?php

/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace App\Models\Database\Entities;

use App\Models\Database\Attributes\TId;
use App\Models\Database\Enums\ThemePreference;
use App\Models\Database\Enums\TimeFormat;
use App\Models\Database\Repositories\UserPreferencesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * User preferences
 */
#[ORM\Entity(repositoryClass: UserPreferencesRepository::class)]
#[ORM\Table(name: 'user_preferences')]
#[ORM\HasLifecycleCallbacks]
class UserPreferences implements JsonSerializable {

	use TId;

	/**
	 * Constructor
	 * @param User $user User
	 * @param TimeFormat $timeFormat Time format
	 * @param ThemePreference $themePreference Theme preference
	 */
	public function __construct(
		#[ORM\OneToOne(inversedBy: 'preferences', targetEntity: User::class)]
		#[ORM\JoinColumn(name: 'user', nullable: false, onDelete: 'CASCADE')]
		private readonly User $user,
		#[ORM\Column(type: Types::INTEGER, enumType: TimeFormat::class, options: ['default' => TimeFormat::Auto])]
		private TimeFormat $timeFormat = TimeFormat::Auto,
		#[ORM\Column(type: Types::INTEGER, enumType: ThemePreference::class, options: ['default' => ThemePreference::Auto])]
		private ThemePreference $themePreference = ThemePreference::Auto,
	) {
	}

	/**
	 * Return time format
	 * @return TimeFormat Time format
	 */
	public function getTimeFormat(): TimeFormat {
		return $this->timeFormat;
	}

	/**
	 * Set time format
	 * @param TimeFormat $timeFormat Time format
	 */
	public function setTimeFormat(TimeFormat $timeFormat): void {
		$this->timeFormat = $timeFormat;
	}

	/**
	 * Returns theme preference
	 * @return ThemePreference Theme preference
	 */
	public function getThemePreference(): ThemePreference {
		return $this->themePreference;
	}

	/**
	 * Sets theme preference
	 * @param ThemePreference $themePreference Theme preference
	 */
	public function setThemePreference(ThemePreference $themePreference): void {
		$this->themePreference = $themePreference;
	}

	/**
	 * Serialize user preferences into JSON
	 * @return array{timeFormat: TimeFormat, theme: ThemePreference} JSON-serialized preferences
	 */
	public function jsonSerialize(): array {
		return [
			'timeFormat' => $this->timeFormat,
			'theme' => $this->themePreference,
		];
	}

}
