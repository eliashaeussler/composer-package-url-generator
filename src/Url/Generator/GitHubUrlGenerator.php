<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/composer-package-url-generator".
 *
 * Copyright (C) 2023-2025 Elias Häußler <elias@haeussler.dev>
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
use GuzzleHttp\Psr7;
use Psr\Http\Message;

use function preg_match;
use function sprintf;
use function str_contains;

/**
 * GitHubUrlGenerator.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class GitHubUrlGenerator implements UrlGenerator
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

            if ('api.github.com' === $sourceUrl->getHost()) {
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
            if (str_contains($candidate, 'github.com')) {
                return true;
            }
        }

        return false;
    }

    public static function getPriority(): int
    {
        return 100;
    }

    private function extractSourceUrlFromApiUrl(Message\UriInterface $apiUrl): ?Message\UriInterface
    {
        $path = $apiUrl->getPath();

        if (1 !== preg_match('#/repos/(?<user>[^/]+)/(?<repo>[^/]+)/zipball/.+#', $path, $matches)) {
            return null;
        }

        // https://api.github.com/repos/guzzle/psr7/zipball/a70f5c95fb43bc83f07c9c948baa0dc1829bf201 => https://github.com/guzzle/psr7
        return new Psr7\Uri(sprintf('https://github.com/%s/%s', $matches['user'], $matches['repo']));
    }
}
