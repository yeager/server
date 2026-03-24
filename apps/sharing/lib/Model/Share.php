<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Sharing\Model;

use OCA\Sharing\Exception\ShareInvalidException;
use OCA\Sharing\Exception\ShareInvalidPropertiesException;
use OCA\Sharing\Registry;
use OCA\Sharing\ResponseDefinitions;
use OCP\Server;

/**
 * @psalm-import-type SharingShare from ResponseDefinitions
 */
readonly class Share {
	public function __construct(
		/** @var non-empty-string $id */
		public string $id,
		public ShareOwner $owner,
		/** @var non-empty-list<ShareSource> $sources */
		public array $sources,
		/** @var non-empty-list<ShareRecipient> $recipients */
		public array $recipients,
		/** @var array<class-string<IShareFeature>, array<string, non-empty-list<string>>> */
		public array $properties,
		/** @var non-negative-int $lastUpdated Unix time in milliseconds */
		public int $lastUpdated,
	) {
		// TODO: Some of these might need to be skipped when loading existing shares from the DB

		/** @psalm-suppress DocblockTypeContradiction */
		if ($id === '') {
			throw new ShareInvalidException('The id is empty.');
		}

		/** {@see \OC\Snowflake\Decoder::decode} */
		if (!ctype_digit($id)) {
			throw new ShareInvalidException('The ID is not a valid Snowflake ID.');
		}

		$registry = Server::get(Registry::class);

		/** @psalm-suppress DocblockTypeContradiction */
		if ($sources === []) {
			throw new ShareInvalidException('The sources are missing.');
		}

		if (!array_is_list($sources)) {
			throw new ShareInvalidException('The sources are not a list.');
		}

		/** @var array<class-string<IShareSourceType>, bool> $shareSourceTypes */
		$shareSourceTypes = [];
		$sourceTypes = $registry->getSourceTypes();
		foreach ($sources as $source) {
			if (!isset($sourceTypes[$source->type])) {
				throw new ShareInvalidException('The source type is not registered: ' . $source->type);
			}

			if (!$sourceTypes[$source->type]->validateSource($this->owner->getUser(), $source->value)) {
				throw new ShareInvalidException('The source ' . $source->value . ' for ' . $source->type . ' is not valid.');
			}

			$shareSourceTypes[$source->type] = true;
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if ($recipients === []) {
			throw new ShareInvalidException('The recipients are missing.');
		}

		if (!array_is_list($recipients)) {
			throw new ShareInvalidException('The recipients are not a list.');
		}

		/** @var array<class-string<IShareRecipientType>, bool> $shareRecipientTypes */
		$shareRecipientTypes = [];
		$recipientTypes = $registry->getRecipientTypes();
		foreach ($recipients as $recipient) {
			if (!isset($recipientTypes[$recipient->type])) {
				throw new ShareInvalidException('The recipient type is not registered: ' . $recipient->type);
			}

			if (!$recipientTypes[$recipient->type]->validateRecipient($recipient->value)) {
				throw new ShareInvalidException('The recipient ' . $recipient->value . ' for ' . $recipient->type . ' is not valid.');
			}

			$shareRecipientTypes[$recipient->type] = true;
		}

		$features = $registry->getFeatures();
		foreach ($properties as $featureClass => $featureProperties) {
			/** @psalm-suppress DocblockTypeContradiction */
			if (!is_string($featureClass)) {
				throw new ShareInvalidException('The feature is not a string: ' . var_export($featureClass, true));
			}

			if (!isset($features[$featureClass])) {
				throw new ShareInvalidException('The feature is not registered: ' . var_export($featureClass, true));
			}

			foreach ($features[$featureClass]->getCompatibles() as $compatible) {
				if (!isset($shareSourceTypes[$compatible['source_type']], $shareRecipientTypes[$compatible['recipient_type']])) {
					throw new ShareInvalidException('The feature is not compatible with the source types and/or recipient types of the share: ' . var_export($featureClass, true));
				}
			}

			/** @psalm-suppress DocblockTypeContradiction */
			if (!is_array($featureProperties)) {
				throw new ShareInvalidException('The feature properties are not an array: ' . var_export($featureProperties, true));
			}

			foreach ($featureProperties as $key => $values) {
				/** @psalm-suppress DocblockTypeContradiction */
				if (!is_string($key)) {
					throw new ShareInvalidException('The feature property key is not a string: ' . var_export($key, true));
				}

				if (!array_is_list($values)) {
					throw new ShareInvalidException('The feature property values are not an array: ' . var_export($values, true));
				}

				foreach ($values as $value) {
					/** @psalm-suppress DocblockTypeContradiction */
					if (!is_string($value)) {
						throw new ShareInvalidException('The feature property value is not a string: ' . var_export($value, true));
					}
				}
			}

			if (!$features[$featureClass]->validateProperties($featureProperties)) {
				throw new ShareInvalidPropertiesException($featureClass);
			}
		}
	}

	/**
	 * @param SharingShare $share
	 */
	public static function fromArray(array $share): self {
		return new self(
			$share['id'],
			new ShareOwner($share['owner']['user_id'], $share['owner']['display_name'] ?? null),
			array_map(ShareSource::fromArray(...), $share['sources']),
			array_map(ShareRecipient::fromArray(...), $share['recipients']),
			$share['properties'],
			$share['last_updated'],
		);
	}

	/**
	 * @return SharingShare
	 */
	public function toArray(): array {
		$owner = [
			'user_id' => $this->owner->userId,
		];

		if ($this->owner->displayName !== null) {
			$owner['display_name'] = $this->owner->displayName;
		}

		return [
			'id' => $this->id,
			'owner' => $owner,
			'last_updated' => $this->lastUpdated,
			'sources' => array_map(static fn (ShareSource $source): array => $source->toArray(), $this->sources),
			'recipients' => array_map(static fn (ShareRecipient $recipient): array => $recipient->toArray(), $this->recipients),
			'properties' => $this->properties,
		];
	}
}
