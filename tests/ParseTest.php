<?php

use Jaybizzle\Shortcodes\Shortcode;
use Jaybizzle\Shortcodes\Shortcodes;

class ParseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->shortcodes = new Shortcodes;
    }

    /** @test */
    public function tags_with_closing_slash()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals('This is some bar content', $this->shortcodes->parse('This is some [foo /] content'));
    }
}


class FooShortcode extends Shortcode
{
    public function parse()
    {
        return 'bar';
    }
}