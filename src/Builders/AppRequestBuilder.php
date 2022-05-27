<?php

namespace Traction\Laracord\Builders;

use Traction\Laracord\Models\DiscordCall;

class AppRequestBuilder extends RequestBuilder {

    public function __construct()
    {
        $this->token = config( 'discord.token' );
        
    }

}