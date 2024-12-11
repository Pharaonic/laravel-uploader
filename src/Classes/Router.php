<?php

namespace Pharaonic\Laravel\Uploader\Classes;

/**
 * @method array action()
 * @method string uri()
 */
class Router
{
    /**
     * The options list.
     *
     * @var array
     */
    protected array $options;

    /**
     * Create a new Router instance.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get route action.
     *
     * @return array
     */
    public function action()
    {
        return [
            $this->options['controller'],
            'file'
        ];
    }

    /**
     * Get route URI.
     *
     * @return string
     */
    public function uri()
    {
        return $this->options['uri'] . '/{hash}';
    }
}
