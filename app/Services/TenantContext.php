<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Organization;
use App\Models\Workspace;

/**
 * TenantContext — Request-Scoped Multi-Tenant Context Manager.
 *
 * Tracks the actively resolved Workspace and Organization for the current execution cycle.
 */
class TenantContext
{
    private static ?Workspace $workspace = null;

    private static ?Organization $organization = null;

    private static bool $bypass = false;

    /**
     * Set the active workspace for the current context.
     */
    public static function setWorkspace(?Workspace $workspace): void
    {
        self::$workspace = $workspace;

        if ($workspace && $workspace->organization) {
            self::$organization = $workspace->organization;
        }
    }

    /**
     * Retrieve the active workspace.
     */
    public static function getWorkspace(): ?Workspace
    {
        return self::$workspace;
    }

    /**
     * Retrieve the active workspace ID.
     */
    public static function getWorkspaceId(): ?int
    {
        return self::$workspace?->id;
    }

    /**
     * Determine if an active workspace is resolved.
     */
    public static function hasWorkspace(): bool
    {
        return self::$workspace !== null && ! self::$bypass;
    }

    /**
     * Set the active organization.
     */
    public static function setOrganization(?Organization $organization): void
    {
        self::$organization = $organization;
    }

    /**
     * Retrieve the active organization.
     */
    public static function getOrganization(): ?Organization
    {
        return self::$organization;
    }

    /**
     * Retrieve the active organization ID.
     */
    public static function getOrganizationId(): ?int
    {
        return self::$organization?->id;
    }

    /**
     * Determine if an active organization is resolved.
     */
    public static function hasOrganization(): bool
    {
        return self::$organization !== null && ! self::$bypass;
    }

    /**
     * Check if tenancy scoping is bypassed.
     */
    public static function isBypassed(): bool
    {
        return self::$bypass;
    }

    /**
     * Execute a callback without any tenant boundary scoping.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function withoutTenancy(callable $callback): mixed
    {
        $previousState = self::$bypass;
        self::$bypass = true;

        try {
            return $callback();
        } finally {
            self::$bypass = $previousState;
        }
    }

    /**
     * Reset the tenant context state.
     */
    public static function clear(): void
    {
        self::$workspace = null;
        self::$organization = null;
        self::$bypass = false;
    }
}
