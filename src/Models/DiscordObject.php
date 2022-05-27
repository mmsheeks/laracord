<?php

namespace Traction\Laracord\Models;

use Traction\Laracord\Facades\Discord;

class DiscordObject {

    protected $apiResponse;

    protected function app_builder()
    {
        return Discord::asApp();
    }

    protected function user_builder( $token = null )
    {
        return Discord::asUser( $token );
    }
    
}