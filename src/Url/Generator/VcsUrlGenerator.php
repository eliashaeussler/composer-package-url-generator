<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/composer-package-url-generator".
 *
 * Copyright (C) 2023-2026 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\ComposerPackageUrlGenerator\Url\Generator;

use Composer\Package;
use EliasHaeussler\ComposerPackageUrlGenerator\Exception;
use EliasHaeussler\ComposerPackageUrlGenerator\Helper;
use Psr\Http\Message;

use function str_contains;

/**
 * VcsUrlGenerator.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
abstract class VcsUrlGenerator implements UrlGenerator
{
    /**
     * @throws Exception\NoSourceUrlAvailable
     * @throws Exception\UrlIsMalformed
     */
    public function generateSourceUrl(Package\PackageInterface $package): Message\UriInterface
    {
        $candidates = [];
        $candidates += $package->getSourceUrls();
        $candidates += $package->getDistUrls();

        foreach ($candidates as $candidate) {
            $sourceUrl = Helper\UrlHelper::normalizeUrl($candidate);

            if ($this->isApiUrl($sourceUrl)) {
                $sourceUrl = $this->extractSourceUrlFromApiUrl($sourceUrl);
            }

            if (null !== $sourceUrl) {
                return $sourceUrl;
            }
        }

        throw new Exception\NoSourceUrlAvailable($package->getName());
    }

    /**
     * @throws Exception\UrlIsMalformed
     */
    public function generateHomepageUrl(Package\PackageInterface $package): ?Message\UriInterface
    {
        if ($package instanceof Package\CompletePackageInterface && null !== $package->getHomepage()) {
            return Helper\UrlHelper::normalizeUrl($package->getHomepage());
        }

        try {
            return $this->generateSourceUrl($package);
        } catch (Exception\NoSourceUrlAvailable) {
            return null;
        }
    }

    public function supports(Package\PackageInterface $package): bool
    {
        $candidates = [];
        $candidates += $package->getSourceUrls();
        $candidates += $package->getDistUrls();

        foreach ($candidates as $candidate) {
            foreach ($this->getDomains() as $domain) {
                if (str_contains($candidate, $domain)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getPriority(): int
    {
        return 100;
    }

    abstract protected function isApiUrl(Message\UriInterface $sourceUrl): bool;

    abstract protected function extractSourceUrlFromApiUrl(Message\UriInterface $apiUrl): ?Message\UriInterface;

    /**
     * @return non-empty-array<string>
     */
    abstract protected function getDomains(): array;
}
