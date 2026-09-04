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
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
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
