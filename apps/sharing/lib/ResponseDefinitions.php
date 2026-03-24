<?php

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\Sharing;

use OCA\Sharing\Model\IShareFeature;
use OCA\Sharing\Model\IShareRecipientType;
use OCA\Sharing\Model\IShareSourceType;

/**
 * @psalm-type SharingShareSource = array{
 *     type: class-string<IShareSourceType>,
 *     value: non-empty-string,
 *     // Will be set by the server automatically
 *     display_name?: non-empty-string,
 * }
 *
 * @psalm-type SharingShareRecipient = array{
 *     type: class-string<IShareRecipientType>,
 *     value: non-empty-string,
 *     // Will be set by the server automatically
 *     display_name?: non-empty-string,
 * }
 *
 * @psalm-type SharingShareOwner = array{
 *     user_id: non-empty-string,
 *     // Will be set by the server automatically
 *     display_name?: non-empty-string,
 * }
 *
 * @psalm-type SharingPartialShare = array{
 *     owner: SharingShareOwner,
 *     sources: non-empty-list<SharingShareSource>,
 *     recipients: non-empty-list<SharingShareRecipient>,
 *     properties: array<class-string<IShareFeature>, array<string, non-empty-list<string>>>,
 * }
 *
 * @psalm-type SharingShare = SharingPartialShare&array{
 *     id: non-empty-string,
 *     // Unix time in milliseconds
 *     last_updated: non-negative-int,
 * }
 *
 * @psalm-type SharingCompatible = array{
 *     source_type: class-string<IShareSourceType>,
 *     recipient_type: class-string<IShareRecipientType>,
 * }
 *
 * @psalm-type SharingFeature = array{
 *     compatibles: non-empty-list<SharingCompatible>,
 * }
 *
 * @psalm-type SharingRecipientSearchResult = array{
 *     type: class-string<IShareRecipientType>,
 *     value: non-empty-string,
 *     display_name: non-empty-string,
 *     // If multiple search results with the same display_name are returned, also show this display_name_unique.
 *     display_name_unique?: non-empty-string,
 *     // Absolute URL
 *     icon_url_light?: non-empty-string,
 *     // Absolute URL
 *     icon_url_dark?: non-empty-string,
 * }
 */
class ResponseDefinitions {
}
