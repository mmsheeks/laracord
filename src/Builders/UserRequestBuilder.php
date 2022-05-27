<?php

namespace Traction\Laracord\Builders;

use Traction\Laracord\Models\DiscordCall;

class UserRequestBuilder extends RequestBuilder {

    public function __construct( $_token )
    {
        $this->token = $_token;
    }

}