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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * GenericOAuth2ResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class GenericOAuth2ResourceOwner extends AbstractResourceOwner
{
    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken)
    {
        $url = $this->normalizeUrl($this->getOption('infos_url'), array(
            'access_token' => $accessToken
        ));

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        try {
            $state = $this->getOption('state');
        } catch (\Exception $e) {
            $state = null;
        }

        $parameters = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->getOption('client_id'),
            'scope'         => $this->getOption('scope'),
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
        ), $extraParameters);

        return $this->normalizeUrl($this->getOption('authorization_url'), $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'code'          => $request->query->get('code'),
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->getOption('client_id'),
            'client_secret' => $this->getOption('client_secret'),
            'redirect_uri'  => $redirectUri,
        ), $extraParameters);

        $response = $this->doGetAccessTokenRequest($this->getOption('access_token_url'), $parameters);
        $response = $this->getResponseContent($response);

        if (isset($response['error'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', isset($response['error']['message']) ? $response['error']['message'] : $response['error']));
        }

        if (!isset($response['access_token'])) {
            throw new AuthenticationException('Not a valid access token.');
        }

        return $response['access_token'];
    }

    /**
     * {@inheritDoc}
     */
    public function handles(Request $request)
    {
        return $request->query->has('code');
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetAccessTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, http_build_query($parameters, '', '&'));
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url);
    }
}
