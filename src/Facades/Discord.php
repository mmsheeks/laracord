<?php

namespace Traction\Laracord\Facades;

use Illuminate\Support\Facades\Session;
use Traction\Laracord\Builders\UserRequestBuilder;
use Traction\Laracord\Builders\AppRequestBuilder;

class Discord {

    public static function asUser( $_token = null )
    {
        if( $_token === null && Session::has( '_laracord_oauth_token') === false ) {
            throw new \Exception("Cannot access Discord API as user without an authenticated session.");
        } else {
            $_token = $_token === null ?? Session::get( '_laracord_oauth_token' );
        }

        return new UserRequestBuilder( $_token );
    }

    public static function asApp()
    {
        return new AppRequestBuilder();
    }

}