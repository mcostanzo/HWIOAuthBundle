<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Buzz\Message\RequestInterface as HttpRequestInterface;

/**
 * LinkedinResourceOwner
 */
class LinkedinResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'authorization_url'   => 'https://www.linkedin.com/uas/oauth2/authorization',
        'access_token_url'    => 'https://api.linkedin.com/uas/oauth2/accessToken',
        'infos_url'           => 'https://api.linkedin.com/v1/people/~:(id,formatted-name,email-address,picture-url)?format=json',
        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedPathUserResponse',
        'realm'               => 'http://api.linkedin.com',
        'scope'               => null,
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'formattedName',
        'realname'   => 'formattedName',
        'email'          => 'emailAddress',
        'profilepicture' => 'pictureUrl',
    );

    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($this->normalizeUrl($url, $parameters), null, array(), HttpRequestInterface::METHOD_POST);
    }

    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        // LinkedIn uses different variable as they still support OAuth1.0a
        return parent::doGetUserInformationRequest(str_replace('access_token', 'oauth2_access_token', $url), $parameters);
    }
}
