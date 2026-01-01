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

/**
 * GenericUrlGenerator.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class GenericUrlGenerator implements UrlGenerator
{
    /**
     * @throws Exception\NoSourceUrlAvailable
     * @throws Exception\UrlIsMalformed
     */
    public function generateSourceUrl(Package\PackageInterface $package): Message\UriInterface
    {
        $sourceUrls = $package->getSourceUrls();

        foreach ($sourceUrls as $sourceUrl) {
            return Helper\UrlHelper::normalizeUrl($sourceUrl);
        }

        throw new Exception\NoSourceUrlAvailable($package->getName());
    }

    /**
     * @throws Exception\UrlIsMalformed
     */
    public function generateHomepageUrl(Package\PackageInterface $package): ?Message\UriInterface
    {
        if (!($package instanceof Package\CompletePackageInterface)) {
            return null;
        }

        if (null !== ($homepageUrl = $package->getHomepage())) {
            return Helper\UrlHelper::normalizeUrl($homepageUrl);
        }

        return null;
    }

    public function supports(Package\PackageInterface $package): bool
    {
        return true;
    }

    public static function getPriority(): int
    {
        return PHP_INT_MIN;
    }
}
