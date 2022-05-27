<?php

namespace Traction\Laracord\Models;

class DiscordUser extends DiscordObject {

    public function __construct( string $snowflake )
    {
        $this->id = $snowflake;
        $this->hydrate();
    }

    private function hydrate()
    {
        $this->attributes = $this->app_builder()->guild()->member()->execute();
    }
}