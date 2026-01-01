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

use GuzzleHttp\Psr7;
use Psr\Http\Message;

use function preg_match;
use function sprintf;

/**
 * GitHubUrlGenerator.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class GitHubUrlGenerator extends VcsUrlGenerator
{
    protected function isApiUrl(Message\UriInterface $sourceUrl): bool
    {
        return 'api.github.com' === $sourceUrl->getHost();
    }

    protected function extractSourceUrlFromApiUrl(Message\UriInterface $apiUrl): ?Message\UriInterface
    {
        $path = $apiUrl->getPath();

        if (1 !== preg_match('#/repos/(?<user>[^/]+)/(?<repo>[^/]+)/zipball/.+#', $path, $matches)) {
            return null;
        }

        // https://api.github.com/repos/guzzle/psr7/zipball/a70f5c95fb43bc83f07c9c948baa0dc1829bf201 => https://github.com/guzzle/psr7
        return new Psr7\Uri(sprintf('https://github.com/%s/%s', $matches['user'], $matches['repo']));
    }

    protected function getDomains(): array
    {
        return ['github.com'];
    }
}
