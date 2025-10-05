<?php
class MoeRouter {

    /**
     * Main Router
     * @param route: route string
     * @param func: call a function
     */
    public function R ( $route, $func, $middleware_options = null ) {

        $reqRoute = str_replace("/", "\/", $route);
        if ( preg_match("/^$reqRoute$/", $_SERVER['REQUEST_URI']) || 
            preg_match("/^$reqRoute\?(.*)$/", $_SERVER['REQUEST_URI']) )
        {

            // if middleware_option was set
            if ( gettype($middleware_options) === "array" ) {
                $this->middleware($reqRoute, $middleware_options);
            }

            // call controller function
            if( strpos($func, '@') > -1 ) { //If function in Class
                $funcArr = explode('@', $func);
                $func = array(new $funcArr[0], $funcArr[1]);
            }
            if ( !is_callable( $func ) ) // is controller exist?
                MoeApps::abort(500, '', 'Controller Not Exist!');
            else
                call_user_func( $func );
                
            exit(0);
        }
    }

    /**
     * Host based Selector
     * @param hosts: hostname array
     * @param func: call group based selector function
     */
    public function H ( $hosts, $func ) 
    {
        // searching hostname in array one by one
        $accessHost = $_SERVER["HTTP_HOST"];
        $hostMatched = false;
        foreach($hosts as $host) {
            if ($accessHost === $host) {
                $hostMatched = true;
                break;
            }
        }
        // if hostname matched call group function
        if ($hostMatched) {
            return call_user_func( $func, $this );
        }
    }

    
    /**
     * Middleware Filter
     * @param reqRoute: requested route
     * @param options: middleware filter options: cidr_whitelist
     */
    private function middleware($reqRoute, $options) {
        foreach ($options as $k => $v) {
            switch ($k) {

                // CIDR Whitelist Filter
                case "cidr_whitelist":
                    $srcIpAddr = $_SERVER["REMOTE_ADDR"];
                    $cidrMatched = false;
                    foreach($v as $network) {
                        if (MoeNET::cidr_match($srcIpAddr, $network)) {
                            $cidrMatched = true;
                            break;
                        }
                    }
                    if (!$cidrMatched) {
                        MoeApps::abort(403, ''
                        ,"IP Address: $srcIpAddr is not ALLOWED, \nPlease contact IT administrator of company!");
                    }
                    break;

                // Host Filter
                case "hosts_whitelist":
                    $accessHost = $_SERVER["HTTP_HOST"];
                    $hostMatched = false;
                    foreach($v as $host) {
                        if ($accessHost === $host) {
                            $hostMatched = true;
                            break;
                        }
                    }
                    if (!$hostMatched) {
                        MoeApps::abort(404, '', 'Route in specified host was Not Found!');
                    }
                    break;

            }
        }
    }

}
?>