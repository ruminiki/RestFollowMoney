<?php

namespace App\Auth;

class TokenAuth extends \Slim\Middleware\TokenAuthentication {
 
    /**
     * Deny Access
     *
     */
    public function deny_access() {
        $res = $this->app->response();
        $res->status(401);
    }
 
    /**
     * Check against the DB if the token is valid
     * 
     * @param string $token
     * @return bool
     */
    public function authenticate($token) {
        //return \Subscriber\Controller\User::validateToken($token);

        return User::where('token', $token)->first();

    }
 
    /**
     * This function will compare the provided url against the whitelist and
     * return wether the $url is public or not
     * 
     * @param string $url
     * @return bool
     */
    public function isPublicUrl($url) {
        $patterns_flattened = implode('|', $this->whiteList);
        $matches = null;
        preg_match('/' . $patterns_flattened . '/', $url, $matches);
        return (count($matches) > 0);
    }
 
    /**
     * Call
     * 
     * @todo beautify this method ASAP!
     *
     */
    public function call() {
        //Get the token sent from jquery
        $tokenAuth = $this->app->request->headers->get('Authorization');
        //We can check if the url requested is public or protected
        if ($this->isPublicUrl($this->app->request->getPathInfo())) {
            //if public, then we just call the next middleware and continue execution normally
            $this->next->call();
        } else {
            //If protected url, we check if our token is valid
            if ($this->authenticate($tokenAuth)) {
                //Get the user and make it available for the controller
                $user = User::where('token', $token)->first();
                $this->app->auth_user = $user;
                //Update token's expiration
                //\Subscriber\Controller\User::keepTokenAlive($tokenAuth);
                //Continue with execution
                $this->next->call();
            } else {
                $this->deny_access();
            }
        }
    }
 
}