<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Bass;

enum Platform
{
    case WINDOWS;
    case LINUX;
    case DARWIN;

    /**
     * @param VersionInterface $version
     * @return bool
     */
    public function supportedBy(VersionInterface $version): bool
    {
        return match ($this) {
            self::WINDOWS => true,
            self::LINUX => \version_compare($version->toString(), '2.4', '>='),
            self::DARWIN => \version_compare($version->toString(), '2.2', '>='),
        };
    }
}
