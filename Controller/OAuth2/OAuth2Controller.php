<?php

namespace Plugin\SampleApi\Controller\OAuth2;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use OAuth2\HttpFoundationBridge\Response as BridgeResponse;
use OAuth2\Encryption\FirebaseJwt as Jwt;

class OAuth2Controller
{

    /**
     * @link http://bshaffer.github.io/oauth2-server-php-docs/grant-types/authorization-code/
     */
    public function authorize(Application $app, Request $request)
    {
        $entityManager = $app['orm.em'];
        $clientStorage  = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\Client');
        $userStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfo');
        $authorizationCodeStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\AuthorizationCode');
        $refreshTokenStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\RefreshToken');
        $accessTokenStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\AccessToken');
        $keyStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\OpenID\PublicKey');
        $scopeStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\Scope');

        $grantTypes = array(
            'authorization_code' => new \OAuth2\OpenID\GrantType\AuthorizationCode($authorizationCodeStorage),
            'refresh_token' => new \OAuth2\GrantType\RefreshToken(
                $refreshTokenStorage,
                array(
                    'always_issue_new_refresh_token' => true,
                    // 'unset_refresh_token_after_use' => false
                )
            )
        );

        $authorizationCodeResponse = new \OAuth2\OpenID\ResponseType\AuthorizationCode(
            $authorizationCodeStorage,
            array('enforce_redirect' => true)
        );
        $accessToken = new \OAuth2\ResponseType\AccessToken($accessTokenStorage, $refreshTokenStorage);
        $idToken = new \OAuth2\OpenID\ResponseType\IdToken($userStorage, $keyStorage, array('issuer' => $app->url('homepage')));
        $idTokenToken = new \OAuth2\OpenID\ResponseType\IdTokenToken($accessToken, $idToken);
        $codeIdToken =  new \OAuth2\OpenID\ResponseType\CodeIdToken($authorizationCodeResponse, $idToken);

        $responseTypes = array(
            'token' => $accessToken,
            'code' => $authorizationCodeResponse,
            'id_token' => $idToken,
            'id_token token' => $idTokenToken,
            'code id_token' => $codeIdToken
        );

        // Pass the doctrine storage objects to the OAuth2 server class
        $server = new \OAuth2\Server(array(
            'client_credentials' => $clientStorage,
            'authorization_code' => $authorizationCodeStorage,
            'user_claims' => $userStorage,
            'access_token'       => $accessTokenStorage,
            'refresh_token' => $refreshTokenStorage,
            'scope' => $scopeStorage,
        ), array(
            'enforce_state' => true,
            'allow_implicit' => true,
            'use_openid_connect' => true,
            'issuer' => $app->url('homepage'),
        ), $grantTypes, $responseTypes);

        // create storage
        $server->addStorage($keyStorage, 'public_key');

        // TODO validation
        $client_id = $request->get('client_id');
        $redirect_uri = $request->get('redirect_uri');
        $response_type = $request->get('response_type');
        $state = $request->get('state');
        $scope = $request->get('scope');
        $nonce = $request->get('nonce');
        $is_authorized = (boolean)$request->get('authorized');

        $Request = \OAuth2\HttpFoundationBridge\Request::createFromGlobals();
        $Response = new BridgeResponse();
        // TODO grant authorization
        if ('POST' === $request->getMethod()) {
            if (!$server->validateAuthorizeRequest($Request, $Response)) {
                return $Response;
            }

            $Client = $clientStorage->findOneBy(array('client_identifier' => $client_id));
            if ($app->user() instanceof \Eccube\Entity\Member && $Client->hasMember() && $Client->checkScope($scope)) {
                $Member = $Client->getMember();
                if ($Member->getId() !== $app->user()->getId()) {
                    $is_authorized = false;
                }
                $UserInfo = $userStorage->findOneBy(array('Member' => $Member));
            } elseif ($app->user() instanceof \Eccube\Entity\Customer && $Client->hasCustomer() && $Client->checkScope($scope)) {
                $Customer = $Client->getCustomer();
                if ($Customer->getId() !== $app->user()->getId()) {
                    $is_authorized = false;
                }
                $UserInfo = $userStorage->findOneBy(array('Customer' => $Customer));
            } else {
                // user unknown
                return $server->handleAuthorizeRequest($Request, $Response, false);
            }

            $user_id = null;
            if ($UserInfo) {
                $user_id = $UserInfo->getSub();
            }

            // handle the request
            return $server->handleAuthorizeRequest($Request, $Response, $is_authorized, $user_id);
        }

        return $app->render(
            'OAuth2/authorization.twig',
            array(
                'client_id' => $client_id,
                'redirect_uri' => $redirect_uri,
                'response_type' => $response_type,
                'state' => $state,
                'scope' => $scope,
                'nonce' => $nonce
            )
        );
    }

    public function token(Application $app, Request $request)
    {
        $entityManager = $app['orm.em'];
        $clientStorage  = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\Client');
        $userStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfo');
        $accessTokenStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\AccessToken');
        $refreshTokenStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\RefreshToken');
        $authorizationCodeStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\AuthorizationCode');
        $keyStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\OpenID\PublicKey');
        $scopeStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\Scope');

        $grantTypes = array(
            'authorization_code' => new \OAuth2\OpenID\GrantType\AuthorizationCode($authorizationCodeStorage),
            'refresh_token' => new \OAuth2\GrantType\RefreshToken(
                $refreshTokenStorage,
                array(
                    'always_issue_new_refresh_token' => true,
                    // 'unset_refresh_token_after_use' => false
                )
            )
        );

        $authorizationCodeResponse = new \OAuth2\OpenID\ResponseType\AuthorizationCode(
            $authorizationCodeStorage,
            array('enforce_redirect' => true)
        );
        $accessToken = new \OAuth2\ResponseType\AccessToken($accessTokenStorage, $refreshTokenStorage);
        $idToken = new \OAuth2\OpenID\ResponseType\IdToken($userStorage, $keyStorage, array('issuer' => $app->url('homepage')));
        $idTokenToken = new \OAuth2\OpenID\ResponseType\IdTokenToken($accessToken, $idToken);
        $codeIdToken =  new \OAuth2\OpenID\ResponseType\CodeIdToken($authorizationCodeResponse, $idToken);

        $responseTypes = array(
            'token' => $accessToken,
            'id_token' => $idToken,
            'id_token token' => $idTokenToken,
        );

        // Pass the doctrine storage objects to the OAuth2 server class
        $server = new \OAuth2\Server(array(
            'client_credentials' => $clientStorage,
            'user_claims' => $userStorage,
            // 'user_credentials'   => $userStorage,
            'access_token'       => $accessTokenStorage,
            'refresh_token' => $refreshTokenStorage,
            'authorization_code' => $authorizationCodeStorage,
            'scope' => $scopeStorage,
        ), array(
            'enforce_state' => true,
            'allow_implicit' => true,
            'use_openid_connect' => true,
            'issuer' => $app->url('homepage'),
        ), $grantTypes, $responseTypes);
        // will be able to handle token requests when "grant_type=client_credentials".
        // $server->addGrantType(new \OAuth2\GrantType\ClientCredentials($clientStorage));

        // will be able to handle token requests when "grant_type=password".
        // $server->addGrantType(new \OAuth2\GrantType\UserCredentials($userStorage));

        $server->addStorage($keyStorage, 'public_key');

        // handle the request
        return $server->handleTokenRequest(\OAuth2\HttpFoundationBridge\Request::createFromGlobals(), new BridgeResponse());
    }

    public function tokenInfo(Application $app, Request $request)
    {
        $entityManager = $app['orm.em'];
        $authorizationCodeStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\AuthorizationCode');
        $userStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfo');
        $keyStorage = $entityManager->getRepository('Plugin\SampleApi\Entity\OAuth2\OpenID\PublicKey');

        // TODO validation
        $id_token = $request->get('id_token');
        $AuthorizationCode = $authorizationCodeStorage->findOneBy(array('id_token' => $id_token));
        $ErrorResponse = $app->json(
            array(
                'error' => 'invalid_token',
                'error_description' => 'Invalid Value'
            ), 400);

        if (!$AuthorizationCode) {
            return $ErrorResponse;
        }

        $Client = $AuthorizationCode->getClient();
        $public_key = $keyStorage->getPublicKeyByClientId($Client->getId());
        $jwt = new Jwt();
        $payload = $jwt->decode($id_token, $public_key);
        if (!$payload) {
            return $ErrorResponse;
        }
        $result = array(
            'issuer' => $payload['iss'],
            'issued_to' => $Client->getClientIdentifier(),
            'audience' => $payload['aud'],
            'user_id' => $payload['sub'],
            'expires_in' => $payload['exp'],
            'issued_at' => $payload['auth_time']
        );
        if (array_key_exists('nonce', $payload)) {
            $result['nonce'] = $payload['nonce'];
        }
        return $app->json($result, 200);
    }
}
