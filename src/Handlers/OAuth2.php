<?php

declare(strict_types=1);

/*
 * This file is part of Dribbble PHP Client.
 *
 * (c) Brian Faust <hello@basecode.sh>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plients\Dribbble\Handlers;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Somoza\OAuth2Middleware\OAuth2Middleware;
use Somoza\OAuth2Middleware\TokenService\Bearer;

/**
 * Class OAuth2.
 */
class OAuth2
{
    /** @var array */
    protected $credentials;

    /**
     * Create a new OAuth2 Handler instance.
     *
     * @param array $credentials
     */
    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return HandlerStack
     */
    public function create(): HandlerStack
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());

        $provider = new GenericProvider($this->getConfiguration());

        $bearerMiddleware = new OAuth2Middleware(
            new Bearer($provider, $this->getAccessToken()),
            [
                $provider->getBaseAuthorizationUrl(),
                $provider->getBaseAccessTokenUrl([]),
            ]
        );

        return tap($stack)->push($bearerMiddleware);
    }

    /**
     * @return array
     */
    protected function getConfiguration()
    {
        return [
            'clientId'                => $this->credentials['client_id'],
            'clientSecret'            => $this->credentials['client_secret'],
            'urlAuthorize'            => $this->getAuthorizeUrl(),
            'urlAccessToken'          => $this->getAccessTokenUrl(),
            'urlResourceOwnerDetails' => $this->getResourceOwnerDetailsUrl(),
        ];
    }

    /**
     * @return AccessToken
     */
    protected function getAccessToken()
    {
        return new AccessToken([
            'access_token' => $this->credentials['access_token'],
        ]);
    }

    /**
     * @return string
     */
    protected function getAuthorizeUrl(): string
    {
        return 'https://dribbble.com/oauth/authorize';
    }

    /**
     * @return string
     */
    protected function getAccessTokenUrl(): string
    {
        return 'https://dribbble.com/oauth/token';
    }

    /**
     * @return string
     */
    protected function getResourceOwnerDetailsUrl(): string
    {
        return 'https://api.dribbble.com/v1/user';
    }
}
