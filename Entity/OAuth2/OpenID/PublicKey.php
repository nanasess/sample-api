<?php

namespace Plugin\SampleApi\Entity\OAuth2\OpenID;

use Doctrine\ORM\Mapping as ORM;

/**
 * PublicKey
 */
class PublicKey extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $public_key;

    /**
     * @var string
     */
    private $private_key;

    /**
     * @var string
     */
    private $encryption_algorithm;

    /**
     * @var \Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfo
     */
    private $UserInfo;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set public_key
     *
     * @param string $publicKey
     * @return PublicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->public_key = $publicKey;

        return $this;
    }

    /**
     * Get public_key
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->public_key;
    }

    /**
     * Set private_key
     *
     * @param string $privateKey
     * @return PublicKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->private_key = $privateKey;

        return $this;
    }

    /**
     * Get private_key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->private_key;
    }

    /**
     * Set encryption_algorithm
     *
     * @param string $encryptionAlgorithm
     * @return PublicKey
     */
    public function setEncryptionAlgorithm($encryptionAlgorithm)
    {
        $this->encryption_algorithm = $encryptionAlgorithm;

        return $this;
    }

    /**
     * Get encryption_algorithm
     *
     * @return string
     */
    public function getEncryptionAlgorithm()
    {
        return $this->encryption_algorithm;
    }

    /**
     * Set UserInfo
     *
     * @param \Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfo $userInfo
     * @return PublicKey
     */
    public function setUserInfo(\Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfo $userInfo = null)
    {
        $this->UserInfo = $userInfo;

        return $this;
    }

    /**
     * Get UserInfo
     *
     * @return \Plugin\SampleApi\Entity\OAuth2\OpenID\UserInfo
     */
    public function getUserInfo()
    {
        return $this->UserInfo;
    }
}
