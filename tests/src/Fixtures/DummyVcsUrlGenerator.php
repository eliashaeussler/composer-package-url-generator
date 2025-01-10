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

namespace EliasHaeussler\ComposerPackageUrlGenerator\Tests\Fixtures;

use EliasHaeussler\ComposerPackageUrlGenerator\Url;
use Psr\Http\Message;

/**
 * DummyVcsUrlGenerator.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class DummyVcsUrlGenerator extends Url\Generator\VcsUrlGenerator
{
    public bool $isApiUrl = false;
    public ?Message\UriInterface $extractedApiUrl = null;

    /**
     * @var non-empty-array<string>
     */
    public array $domains = ['example.com'];

    protected function isApiUrl(Message\UriInterface $sourceUrl): bool
    {
        return $this->isApiUrl;
    }

    protected function extractSourceUrlFromApiUrl(Message\UriInterface $apiUrl): ?Message\UriInterface
    {
        return $this->extractedApiUrl;
    }

    protected function getDomains(): array
    {
        return $this->domains;
    }
}
