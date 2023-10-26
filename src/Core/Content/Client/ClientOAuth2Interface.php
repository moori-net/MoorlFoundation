<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use League\OAuth2\Client\Provider\AbstractProvider;

interface ClientOAuth2Interface
{
    public function getProviderInstance(string $redirectUri): AbstractProvider;
}
