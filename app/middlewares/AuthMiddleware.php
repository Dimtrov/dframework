<?php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * AuthMiddleware
 *
 * This is an example of authentication middlewares. It check if user is authenticate
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $auth = session('auth');
        if (empty($auth) AND !preg_match('#login#', $request->url)) {
            return single_service('response')->withHeader('location', link_to('login'));
        }

        return $handler->handle($request);
    }
}
