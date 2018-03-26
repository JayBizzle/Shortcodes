<?php

namespace Jaybizzle\Shortcodes;

use Jaybizzle\Shortcodes\ShortcodeContract;

abstract class Shortcode implements ShortcodeContract
{
    /**
     * The shortcode attributes.
     *
     * @var array
     */
    public $attributes;

    /**
     * The content of the shortcode.
     *
     * @var string
     */
    public $content;

    /**
     * The shortcode tag.
     *
     * @var string
     */
    public static $shortcode;

    public function __construct($attributes, $content)
    {
        $this->content = $content;
        $this->attributes = $attributes;
    }

    public function __get($name)
    {
        if ($name == 'shortcode') {
            return static::$shortcode;
        } elseif (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
    }
}
