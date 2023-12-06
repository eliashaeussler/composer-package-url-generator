<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/composer-package-url-generator".
 *
 * Copyright (C) 2023 Elias Häußler <elias@haeussler.dev>
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

use function preg_replace;
use function str_starts_with;
use function substr;

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
    public static function convertSshUrl(string $url): Message\UriInterface
    {
        $normalizedUrl = preg_replace('/\.git\/?$/', '', $url);

        if (null === $normalizedUrl) {
            throw new Exception\UrlIsMalformed($url);
        }

        if (str_starts_with($normalizedUrl, 'ssh://')) {
            $httpUri = new Uri('https://'.substr($normalizedUrl, 6));

            return $httpUri->withUserInfo('');
        }

        $normalizedUrl = preg_replace('/^.+@([^:]+):/', 'https://$1/', $normalizedUrl);

        if (null === $normalizedUrl) {
            throw new Exception\UrlIsMalformed($url);
        }

        return new Uri($normalizedUrl);
    }
}
