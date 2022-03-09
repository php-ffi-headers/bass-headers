<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Bass\Tests\BinaryCompatibilityTestCase;

class DownloaderResult
{
    /**
     * @var \PharData
     */
    private \PharData $phar;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->phar = new \PharData($url);
    }

    /**
     * @param string $file
     * @param callable|null $then
     * @param callable|null $otherwise
     * @return $this
     */
    public function whenExists(string $file, callable $then = null, callable $otherwise = null): self
    {
        if (isset($this->phar[$file])) {
            $then && $then($this);
        } else {
            $otherwise && $otherwise($this);
        }

        return $this;
    }

    /**
     * @param string $file
     * @param string $target
     * @return $this
     */
    public function extract(string $file, string $target): self
    {
        if (!isset($this->phar[$file])) {
            throw new \InvalidArgumentException('File [' . $file  .'] not found');
        }

        /** @var \PharFileInfo $info */
        $info = $this->phar[$file];

        \file_put_contents($target, $info->getContent());

        return $this;
    }
}
