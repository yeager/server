<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);


namespace OCA\Sharing\Migration;

use Closure;
use Doctrine\DBAL\Schema\SchemaException;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use Override;

class Version1000Date20250929161325 extends SimpleMigrationStep {
	/**
	 * @param Closure():ISchemaWrapper $schemaClosure
	 * @throws SchemaException
	 */
	#[Override]
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		$schema = $schemaClosure();

		$shareTable = $schema->createTable('sharing_share');
		$shareTable->addColumn('id', Types::BIGINT);
		$shareTable->addColumn('owner', Types::TEXT);
		$shareTable->addColumn('owner_display_name', Types::TEXT, ['notnull' => false]);
		$shareTable->addColumn('last_updated', Types::BIGINT);

		$sourcesTable = $schema->createTable('sharing_share_sources');
		$sourcesTable->addColumn('id', Types::BIGINT);
		$sourcesTable->addColumn('source_type', Types::TEXT);
		$sourcesTable->addColumn('source_value', Types::TEXT);
		$sourcesTable->addColumn('source_display_name', Types::TEXT, ['notnull' => false]);

		$recipientsTable = $schema->createTable('sharing_share_recipients');
		$recipientsTable->addColumn('id', Types::BIGINT);
		$recipientsTable->addColumn('recipient_type', Types::TEXT);
		$recipientsTable->addColumn('recipient_value', Types::TEXT);
		$recipientsTable->addColumn('recipient_display_name', Types::TEXT, ['notnull' => false]);

		$propertiesTable = $schema->createTable('sharing_share_properties');
		$propertiesTable->addColumn('id', Types::BIGINT);
		$propertiesTable->addColumn('feature', Types::TEXT);
		$propertiesTable->addColumn('feature_key', Types::TEXT);
		$propertiesTable->addColumn('feature_value', Types::TEXT);

		// TODO: Add primary keys, unique constraints and indices
		// TODO: Handle unique constraint exceptions

		return $schema;
	}
}
