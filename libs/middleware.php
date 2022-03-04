<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class Authenticate
{
    /**
     * Example middleware invokable class
     *
     * @param  ServerRequest  $request PSR-7 request
     * @param  RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $res =array();   
        $ignore = array();
        $response = new Response();

        try{
            $ignore = array(
                MAIN_DIR.'api/',
            );

            $method = $request->getMethod();
            $routeContext = $request->getUri();
            $route = $routeContext->getPath();

            if ($method != 'OPTIONS') {
                $routeContext = $request->getUri();
                $route = $routeContext->getPath();

                if (!in_array($route, $ignore)) {
                    $headers = $request->getheaders();
                    
                    $auth = (isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : null));
                    if($auth == null){
                        //throw new Exception('Invalid token', 401);
                    }
                }
            }

            $response = $handler->handle($request);       
            $existingContent = (string) $response->getBody();

            $response = new Response(); 
            $response->getBody()->write($existingContent);
        } catch(Exception $ex) {
            $code = 500;
            $response->getBody()->write(json_encode(['error' => $ex->getMessage().' '.$ex->getTraceAsString()]));
            if($ex->getCode() == 401 ||  $ex->getCode() == 403){
                $code = $ex->getCode();
            }
            return $response->withStatus($code);  
        }
        return $response;
    }
}
