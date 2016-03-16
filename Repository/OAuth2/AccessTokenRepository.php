<?php

namespace Eccube\Repository\OAuth2;

use Doctrine\ORM\EntityRepository;
use Eccube\Entity\OAuth2\AccessToken;
use OAuth2\Storage\AccessTokenInterface;


/**
 * AccessTokenRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 *
 * @link http://bshaffer.github.io/oauth2-server-php-docs/cookbook/doctrine2/
 */
class AccessTokenRepository extends EntityRepository implements AccessTokenInterface
{
    public function getAccessToken($oauthToken)
    {
        $token = $this->findOneBy(array('token' => $oauthToken));
        if ($token) {
            $token = $token->toArray();
            $token['expires'] = $token['expires']->getTimestamp();
        }
        return $token;
    }

    public function setAccessToken($oauthToken, $clientIdentifier, $userEmail, $expires, $scope = null)
    {
        $client = $this->_em->getRepository('Eccube\Entity\OAuth2\Client')
                            ->findOneBy(array('client_identifier' => $clientIdentifier));
        $user = $this->_em->getRepository('Eccube\Entity\OAuth2\User')
                            ->findOneBy(array('email' => $userEmail));
        $AccessToken = new \Eccube\Entity\OAuth2\AccessToken();
        $AccessToken->setPropertiesFromArray(array(
            'token'     => $oauthToken,
            'client'    => $client,
            'user'      => $user,
            'expires'   => (new \DateTime())->setTimestamp($expires),
            'scope'     => $scope,
        ));
        $this->_em->persist($AccessToken);
        $this->_em->flush();
    }
}
