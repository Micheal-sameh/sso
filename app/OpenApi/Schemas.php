<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Pure attribute holders for reusable OpenAPI component schemas.
 * Referenced from controllers as `#[OA\JsonContent(ref: '#/components/schemas/User')]`.
 */
#[OA\Schema(
    schema: 'User',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'membership_code', type: 'string', example: 'AV-00123'),
        new OA\Property(property: 'name', type: 'string', example: 'Micheal Samir'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
        new OA\Property(property: 'date_of_birth', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'avatar', type: 'string', nullable: true),
        new OA\Property(property: 'role', type: 'string'),
        new OA\Property(property: 'is_active', type: 'boolean'),
        new OA\Property(property: 'requires_data_update', type: 'boolean'),
        new OA\Property(property: 'is_admin', type: 'boolean'),
        new OA\Property(property: 'mfa_enabled', type: 'boolean'),
        new OA\Property(property: 'mfa_enabled_at', type: 'string', nullable: true),
        new OA\Property(property: 'mfa_recovery_codes_remaining', type: 'integer', nullable: true),
        new OA\Property(property: 'mfa_recovery_codes_total', type: 'integer'),
        new OA\Property(property: 'can_manage_admin_access', type: 'boolean'),
        new OA\Property(property: 'can_manage_cmc_posts', type: 'boolean'),
        new OA\Property(
            property: 'stage',
            type: 'object',
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'code', type: 'string'),
            ],
        ),
        new OA\Property(property: 'email_verified_at', type: 'string', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', nullable: true),
        new OA\Property(
            property: 'groups',
            type: 'array',
            items: new OA\Items(
                type: 'object',
                properties: [
                    new OA\Property(property: 'id', type: 'integer'),
                    new OA\Property(property: 'name', type: 'string'),
                ],
            ),
        ),
    ],
)]
#[OA\Schema(
    schema: 'CmcPost',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'text', type: 'string', nullable: true),
        new OA\Property(property: 'image_url', type: 'string', nullable: true),
        new OA\Property(property: 'url', type: 'string', nullable: true),
        new OA\Property(property: 'url_title', type: 'string', nullable: true),
        new OA\Property(property: 'url_description', type: 'string', nullable: true),
        new OA\Property(property: 'url_image', type: 'string', nullable: true),
        new OA\Property(
            property: 'creator',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
            ],
        ),
        new OA\Property(property: 'created_at', type: 'string', example: '21 Aug 2026, 14:05'),
    ],
)]
#[OA\Schema(
    schema: 'YoutubeVideo',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'video_id', type: 'string'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'thumbnail_url', type: 'string', nullable: true),
        new OA\Property(property: 'published_at', type: 'string', nullable: true, example: '21 Aug 2026'),
        new OA\Property(property: 'is_pinned', type: 'boolean'),
        new OA\Property(property: 'watch_url', type: 'string'),
    ],
)]
#[OA\Schema(
    schema: 'Notification',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'image_url', type: 'string', nullable: true),
        new OA\Property(property: 'is_read', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', example: '2 hours ago'),
        new OA\Property(property: 'cmc_post_id', type: 'integer', nullable: true),
    ],
)]
#[OA\Schema(
    schema: 'CopticDate',
    type: 'object',
    properties: [
        new OA\Property(property: 'date', type: 'string', nullable: true, example: '14 Mesori 1742'),
        new OA\Property(
            property: 'commemorations',
            type: 'array',
            items: new OA\Items(type: 'string'),
        ),
    ],
)]
#[OA\Schema(
    schema: 'PaginatedResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
        new OA\Property(property: 'last_page', type: 'integer'),
        new OA\Property(property: 'per_page', type: 'integer'),
        new OA\Property(property: 'total', type: 'integer'),
    ],
)]
#[OA\Schema(
    schema: 'Error',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
    ],
)]
final class Schemas
{
    //
}
