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

namespace EliasHaeussler\ComposerPackageUrlGenerator\Tests\Fixtures;

use Composer\Package;
use EliasHaeussler\ComposerPackageUrlGenerator\Exception;
use EliasHaeussler\ComposerPackageUrlGenerator\Url;
use Psr\Http\Message;

/**
 * DummyUrlGenerator.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @internal
 */
class DummyUrlGenerator implements Url\Generator\UrlGenerator
{
    public ?Message\UriInterface $sourceUrl = null;
    public ?Message\UriInterface $homepageUrl = null;
    public bool $supported = true;

    public function generateSourceUrl(Package\PackageInterface $package): Message\UriInterface
    {
        return $this->sourceUrl ?? throw new Exception\NoSourceUrlAvailable($package->getName());
    }

    public function generateHomepageUrl(Package\PackageInterface $package): ?Message\UriInterface
    {
        return $this->homepageUrl;
    }

    public function supports(Package\PackageInterface $package): bool
    {
        return $this->supported;
    }

    public static function getPriority(): int
    {
        return 0;
    }
}
