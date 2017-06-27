<?php

namespace Jaybizzle\Shortcodes;

use Jaybizzle\Shortcodes\ShortcodeContract;

abstract class Shortcode implements ShortcodeContract
{
	public $attr;

    public $content;

    public $tag;

    public function __construct($attr, $content, $tag)
    {
        $this->tag = $tag;
        $this->attr = $attr;
        $this->content = $content;
    }
}