<?php

namespace Dflydev\Stack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Authentication implements HttpKernelInterface
{
    private $app;
    private $challenge;
    private $authenticate;
    private $anonymous;

    public function __construct(HttpKernelInterface $app, array $options = [])
    {
        $this->app = $app;

        if (!isset($options['challenge'])) {
            $options['challenge'] = function (Response $response) {
                return $response;
            };
        }

        if (!isset($options['authenticate'])) {
            throw new \InvalidArgumentException("The 'authenticate' configuration is required");
        }

        $this->challenge = $options['challenge'];
        $this->authenticate = $options['authenticate'];
        $this->anonymous = $options['anonymous'];
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if ($request->attributes->has('stack.authn.token')) {
            // If the request already has a Stack authentication token we
            // should wrap the application so that it has the option to
            // challenge if we get a 401 WWW-Authenticate: Stack response.
            //
            // Delegate immediately.
            return (new WwwAuthenticateStackChallenge($this->app, $this->challenge))
                ->handle($request, $type, $catch);
        }

        if ($request->headers->has('authorization')) {
            // If we have an authorization header we should try and authenticate
            // the request.
            return call_user_func($this->authenticate, $this->app, $this->anonymous);
        }

        if ($this->anonymous) {
            // If anonymous requests are allowed we should wrap the application
            // so that it has the option to challenge if we get a 401
            // WWW-Authenticate: Stack response.
            //
            // Delegate immediately.
            return (new WwwAuthenticateStackChallenge($this->app, $this->challenge))
                ->handle($request, $type, $catch);
        }

        // Since we do not allow anonymous requests we should challenge
        // immediately.
        return call_user_func($this->challenge, (new Response)->setStatusCode(401));
    }
}
