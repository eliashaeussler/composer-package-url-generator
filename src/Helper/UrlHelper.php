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

namespace EliasHaeussler\ComposerPackageUrlGenerator\Helper;

use EliasHaeussler\ComposerPackageUrlGenerator\Exception;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message;

use function parse_url;
use function preg_replace;
use function str_contains;

/**
 * UriHelper.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class UrlHelper
{
    /**
     * @throws Exception\UrlIsMalformed
     */
    public static function normalizeUrl(string $url): Message\UriInterface
    {
        $normalizedUrl = preg_replace('/\.git\/?$/', '', $url);

        if (null === $normalizedUrl) {
            throw new Exception\UrlIsMalformed($url);
        }

        // Convert Git-style syntax to HTTPS syntax
        // Example: git@gitlab.example.com:foo/bar => https://gitlab.example.com/foo/bar
        if (!str_contains($normalizedUrl, '://')) {
            $normalizedUrl = preg_replace('/^.+@([^:]+):/', 'https://$1/', $normalizedUrl);
        }

        if (null === $normalizedUrl) {
            throw new Exception\UrlIsMalformed($url);
        }

        if (false === parse_url($normalizedUrl)) {
            throw new Exception\UrlIsMalformed($url);
        }

        $uri = new Uri($normalizedUrl);

        // Enforce HTTPS scheme for SSH urls and missing url schemes
        if ('' === $uri->getScheme() || 'ssh' === $uri->getScheme()) {
            $uri = $uri->withScheme('https');
        }

        // Drop user info and port
        return $uri->withUserInfo('')->withPort(null);
    }
}
