<?php

namespace Pharaonic\Laravel\Uploader\Classes;

class Router
{
    /**
     * The options list.
     *
     * @var array<mixed>
     */
    protected array $options;

    /**
     * Create a new Router instance.
     *
     * @param  array<mixed>  $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get the route action (the controller class).
     *
     * @return string
     */
    public function action()
    {
        $controller = $this->options['controller'] ?? null;

        return is_string($controller) ? $controller : \Pharaonic\Laravel\Uploader\Http\Controllers\UploadController::class;
    }

    /**
     * Get the route URI.
     *
     * @return string
     */
    public function uri()
    {
        $uri = $this->options['uri'] ?? null;

        return (is_string($uri) ? $uri : '/file').'/{hash}';
    }
}
