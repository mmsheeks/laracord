<?php

namespace Traction\Laracord\Builders;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RequestBuilder {

    public const DISCORD_BASE_URI = 'https://discord.com/api/v2';

    private $uri;
    private $path = [];
    private $body;
    private $method;
    private $guild = null;
    private $user = null;
    
    protected $token;
    protected $response;

    /**
     * user - sets the API call member ID to be substituted for the @user placeholder in the final URI
     * 
     * @param string|null $user_id - the Discord user ID of the user, uses 
     */
    public function user( $user_id = false )
    {
        if( $user_id === false && Auth::check() ) {
            $user = Auth::user();
            $user_id = $user->discord_id;
        }
        $this->user = $user_id;
        
        return $this;
    }

    /**
     * guild - sets the API call guild ID to be substituted for the @guild placeholder in the final URI
     * 
     * @param string|null $guild_id - the ID of the guild
     * 
     * @return UserRequestBuilder|AppRequestBuilder|RequestBuilder $this
     */
    public function guild( $guild_id = false )
    {
        $this->guild = $guild_id !== false ? $guild_id : ( config( 'discord.guild_id' ) ?? false );
        return $this;
    }

    /**
     * result - get the output of the API call
     * 
     * @return StdClass $result
     */
    public function result()
    {
        if( empty( $this->response ) || $this->response->status() !== 200 ) {
            $this->error( "Attempted to get result set on bad API call.");
        }
        return json_dcode( $this->response->body() );
    }

    /**
     * set_uri - sets a specific URI to call and clears the path chain
     * 
     * @param string $uri - a valid Discord API path
     * 
     * @return UserRequestBuilder|AppRequestBuilder|RequestBuilder $this
     */
    protected function set_uri( $uri )
    {
        $this->path = [];
        $this->uri = $uri;
        return $this;
    }

    /**
     * get_uri - get the current request chain URI
     * 
     * @return string $uri
     */
    protected function get_uri()
    {
        if( empty( $this->uri ) ) {
            $this->build_uri();
        }
        return $this->uri;
    }

    /**
     * set_method - sets the request method
     * 
     * @param string $method - the method to use. Must be get|post|put
     * 
     * @return UserRequestBuilder|AppRequestBuilder|RequestBuilder $this
     */
    protected function set_method( $method )
    {
        $allowed_methods = [ 'get', 'post', 'put' ];
        if( ! in_array( $method, $allowed_methods ) ) {
            $this->error( "Attempted to set unallowed API Method: $method");
        }
        $this->method = $method;
        return $this;
    }

    /**
     * get_method - gets the currently set request method
     * 
     * @return string $method
     */
    protected function get_method()
    {
        return $this->method ?? 'get';
    }

    /**
     * execute - call the discord API endpoint required for the current request chain
     * 
     * @return UserRequestBuilder|AppRequestBuilder|RequestBuilder $this
     */
    protected function execute()
    {
        $uri = $this->get_uri();
        $method = $this->get_method();

        try {
            $response = Http::withToken( $this->token )
                ->accept( 'application/json' )
                ->$method( $uri );
            if( $response->status() !== 200 ) {
                $this->error( "API Returned non-OK status.", $response->body() );
            }
        } catch( \Exception $e ) {
            $this->error( "Fatal execution error.", $e );
        }

        $this->response = $response;

        return $this;
    }

    /**
     * __call - calls the specified method if it exists, otherwise assumes method should be a path element and appends it.
     * 
     * @param string $method - method name called
     * @param array $args - arguments provided to call
     * 
     * @return UserRequestBuilder|AppRequestBuilder|RequestBuilder $this
     */
    protected function __call( $method, $args )
    {
        if( method_exists( $this, $method ) ) {
            return $this->$method( ...$args );
        }

        $parts = preg_split( '/?=[A-Z]/', $method );
        foreach( $parts as $path_part ) {
            $this->path[] = $path_part;
        }
        return $this;
    }

    /**
     * error - log an error, then abort execution
     * 
     * @param string $message - the message to be logged
     * @param mixed $args - any additional values to be logged
     * 
     * @return void
     */
    protected function error( $message, ...$args )
    {
        if( !empty( $args ) ) {
            foreach( $args as $arg ) {
                $message .= "\n\t" . var_export( $arg, true );
            }
        }
        Log::error( $message );
        abort(500);
    }

    /**
     * build_uri - Assemble the set path parts, then appended them to the Discord API base URI
     * 
     * @return void
     */
    private function build_uri()
    {
        $uri = sprintf( "%s/%s", self::DISCORD_BASE_URI, implode( "/", $this->path ) );

        // add user ID if needed
        $user = $this->user;
        if( $user !== null ) {
            if( $user === false ) {
                $this->error( "API Call requested user parameter, but no user was logged in or provided.");
            }
            if( str_contains( $this->uri, '@user' ) === false ) {
                $this->error( "API Call attempted to set User ID, but requested path does not require a user.");
            }
            $uri = str_replace( '@user', $user, $uri );
        }

        // add the guild if needed
        $guild = $this->guild;
        if( $guild !== null ) {
            if( $guild === false ) {
                $this->error( "API Call requested guild parameter, but no guild was provided or configured.");
            }
            if( str_contains( $this->uri, '@guild' ) === false ) {
                $this->error( "API Call attempted to set Guild ID, but requested path does not require a guild.");
            }
            $uri = str_replace( '@guild', $guild, $uri );
        }

        $this->uri = $uri;
    }

    

}