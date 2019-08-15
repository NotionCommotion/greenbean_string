<?php

namespace Greenbean\Concrete5\GreenbeanDataIntegrator;

use Concrete\Core\Http\Middleware\DelegateInterface;
use Concrete\Core\Http\Middleware\MiddlewareInterface;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Error\ErrorList\ErrorList;

class ValidGbUserMiddleware implements MiddlewareInterface
{

    private $gbUser;
    public function __construct($gbUser)
    {
        $this->gbUser = $gbUser;
    }

    /**
    * Process the request and return a PSR7 error response if needed
    *
    * @param \Symfony\Component\HttpFoundation\Request $request
    * @param DelegateInterface $frame
    * @return \Psr\Http\Message\ResponseInterface
    */
    public function process(Request $request, DelegateInterface $frame)
    {
        if($this->gbUser) {
            return $frame->next($request);
        }
        else {
            $errors = new ErrorList;
            $errors->add("Unauthorized");
            return $errors->createResponse(401);
        }
    }
}
