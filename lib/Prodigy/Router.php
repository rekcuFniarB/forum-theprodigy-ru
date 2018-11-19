<?php
namespace Prodigy;

class Router extends \Klein\Klein {
    public function __construct(
        \Klein\ServiceProvider $service = null,
        $app = null,
        \Klein\RouteCollection $routes = null,
        \Klein\AbstractRouteFactory $route_factory = null)
    {
        if (is_null($service)) {
            // This instantiates our extended service class pased on Klein\ServiceProvider.
            $service = new Service();
        }
        parent::__construct($service, $app, $routes, $route_factory);
    }
    
    // This was for testing purposes.
    //public static function __callStatic($method, $args) {
        ////var_dump($args);
        //$app = $args[3];
        //$app->errors->log("__DEBUG__: STATIC CALL $method");
        //$loadclass = "Respond\\$method";
        //$respond = new $loadclass($app);
        //return $respond("STATIC '$method' CALL MAIN!");
    //}
    
    /**
     * Register lazy services
     * @param array $services array of services in following format: [['serviceName', 'className'], ['serviceName', 'className'], ...]
     * @returns object
     */
    public function registerServices($services) {
        $router = $this;
        $app = $this->app();
        foreach ($services as $service){
            $app->register($service[0], function() use ($router, $service) {
                // Lazy class init
                return new $service[1]($router);
            });
        }
    }
    
     /**
     * Extending respond method allowing callbacks in format "servicename->method".
     * @param string|array $method    HTTP Method to match
     * @param string $path            Route URI path to match
     * @param callable $callback      Callable callback method to execute on route match
     * @return Route
     */
    public function respond($method, $path = '*', $callback = null) {
        // Get the arguments in a very loose format (this was taken from parent class method)
        extract($this->parseLooseArgumentOrder(func_get_args()), EXTR_OVERWRITE);
        
        error_log("__ROUTE__: $path");
        
        if (is_string($callback)) {
            $callbackMethod = explode('->', $callback);
            // run servicename->method() if supplied callback is in format "servicename->method".
            if (count($callbackMethod) > 1) {
                return parent::respond($method, $path, function($request, $response, $service, $app) use ($callbackMethod) {
                    return $app->{$callbackMethod[0]}->{$callbackMethod[1]}($request, $response, $service, $app);
                });
            }
        }
        return parent::respond($method, $path, $callback);
    }
    
    /**
     * testing
     */
    //public function respond($method, $path = '*', $callback = null) {
        //// Get the arguments in a very loose format
        //extract($this->parseLooseArgumentOrder(func_get_args()), EXTR_OVERWRITE);
        //return parent::respond($method, $path, $callback);
    //}
}

?>
