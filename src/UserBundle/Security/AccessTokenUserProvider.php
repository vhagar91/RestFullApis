<?php


namespace UserBundle\Security;


use Guzzle\Http\Client;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AccessTokenUserProvider implements UserProviderInterface, ContainerAwareInterface
{
    private $container;
    public function getUserForAccessToken($accessToken){
        $client = new Client();
        $myRequest = $client->createRequest('GET', $this->container->getParameter('mycp_oauth2_server_base_url').'/api/user/info');
        $query = $myRequest->getQuery();
        $query['access_token'] = $accessToken;
        $response = $client->send($myRequest);
        $userData = (array)json_decode($response->getBody());
        return $userData['username'];
    }

    public function loadUserByUsername($username)
    {
        return new User(
            $username,
            null,
        array('ROLE_USER')
        );
    }

    public function refreshUser(UserInterface $user)
    {
        // TODO: Implement refreshUser() method.
        return $user;
    }

    public function supportsClass($class)
    {
        return 'Symfony\Component\Security\Core\User\User' === $class;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container=$container;
    }
}