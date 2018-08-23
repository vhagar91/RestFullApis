<?php
namespace UserBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\User\User;


class AccessTokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (!$userProvider instanceof AccessTokenUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of AccesTokenUserProvider (%s was
                      given).',
                    get_class($userProvider)
                )
            );
        }
        $accessToken = $token->getCredentials();
        $username = $userProvider->getUserForAccessToken($accessToken);
        $user = $token->getUser();
        if ($user instanceof User) {
            return new PreAuthenticatedToken(
                $user,
                $accessToken,
                $providerKey,
                $user->getRoles()
            );
        }

        if (!$username) {
            throw new AuthenticationException(
                sprintf('Access token "%s" does not exist.', $accessToken)
            );
        }
        $user = $userProvider->loadUserByUsername($username);
        return new PreAuthenticatedToken(
            $user,
            $accessToken,
            $providerKey,
            $user->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() ===$providerKey;
    }

    public function createToken(Request $request, $providerKey)
    {
        $accessToken = $request->query->get('access_token')!=null?$request->query->get('access_token'):$request->headers->get('access_token');
        if (!$accessToken) {
            throw new BadCredentialsException('No Access Token found');

        }
        return new PreAuthenticatedToken(
            'anon.',
            $accessToken,
            $providerKey
        );
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response("Authentication Failed.", 403);
    }
}