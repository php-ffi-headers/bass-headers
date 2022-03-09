<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Bass;

enum Version: string implements VersionInterface
{
    case V2_0 = '2.0';
    case V2_1 = '2.1';
    case V2_2 = '2.2';
    case V2_3 = '2.3';
    case V2_4 = '2.4';

    public const LATEST = self::V2_4;

    /**
     * @param Platform $platform
     * @return bool
     */
    public function supportedOn(Platform $platform): bool
    {
        return $platform->supportedBy($this);
    }

    /**
     * @param non-empty-string $version
     * @return VersionInterface
     */
    public static function create(string $version): VersionInterface
    {
        /** @var array<non-empty-string, VersionInterface> $versions */
        static $versions = [];

        return self::tryFrom($version)
            ?? $versions[$version]
            ??= new class($version) implements VersionInterface {
                /**
                 * @param non-empty-string $version
                 */
                public function __construct(
                    private string $version,
                ) {
                }

                /**
                 * {@inheritDoc}
                 */
                public function toString(): string
                {
                    return $this->version;
                }
            };
    }

    /**
     * {@inheritDoc}
     */
    public function toString(): string
    {
        return $this->value;
    }
}
