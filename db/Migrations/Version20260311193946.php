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

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fix default mapping pins for IQD-GW04 and KONA-RASP-04
 */
final class Version20260311193946 extends AbstractMigration {

	/**
	 * Returns the migration description
	 * @return string Migration description
	 */
    public function getDescription(): string {
        return 'Fix default mapping pins for IQD-GW04 and KONA-RASP-04';
    }

	/**
	 * Applies the migration
	 * @param Schema $schema Database schema
	 */
    public function up(Schema $schema): void {
		$this->addSql(<<<'SQL'
			UPDATE mappings
			SET i2c_enable_gpio_pin = -1
			WHERE name IN ('IQD-GW04', 'KONA-RASP-04') AND i2c_enable_gpio_pin = 18
		SQL);
    }

	/**
	 * Reverts the migration
	 * @param Schema $schema Database schema
	 */
    public function down(Schema $schema): void {
		$this->addSql(<<<'SQL'
			UPDATE mappings
			SET i2c_enable_gpio_pin = 18
			WHERE name IN ('IQD-GW04', 'KONA-RASP-04') AND i2c_enable_gpio_pin = -1
		SQL);
    }

}
