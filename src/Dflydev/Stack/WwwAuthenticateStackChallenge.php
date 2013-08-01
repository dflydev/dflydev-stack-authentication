<?php

namespace Dflydev\Stack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class WwwAuthenticateStackChallenge implements HttpKernelInterface
{
    private $app;
    private $challenge;

    public function __construct(HttpKernelInterface $app, $challenge = null)
    {
        $this->app = $app;
        $this->challenge = $challenge ?: function (Response $response) {
            return (new Response('Authentication not possible', 403));
        };
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $response = $this->app->handle($request, $type, $catch);

        if ($response->getStatusCode()==401 && $response->headers->get('WWW-Authenticate') === 'Stack') {
            // By convention, we look for 401 response that has a WWW-Authenticate with field value of
            // Stack. In that case, we should pass the response to the delegatee's challenge callback.
            $response = call_user_func($this->challenge, $response);
        }

        return $response;
    }
}
