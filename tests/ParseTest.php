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

        $this->assertEquals('This is some <foo></foo> content', $this->shortcodes->parse('This is some [foo /] content'));
    }

    /** @test */
    public function tags_without_closing_slash()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals('This is some <foo></foo> content', $this->shortcodes->parse('This is some [foo] content'));
    }

    /** @test */
    public function tags_with_closing_slash_and_attributes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals('This is some <foo bar=baz></foo> content', $this->shortcodes->parse('This is some [foo bar=baz /] content'));
    }

    /** @test */
    public function tags_without_closing_slash_and_attributes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals('This is some <foo bar=baz></foo> content', $this->shortcodes->parse('This is some [foo bar=baz] content'));
    }

    /** @test */
    public function tags_with_opening_and_closing_tags()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals('This is some <foo>bar baz</foo> content', $this->shortcodes->parse('This is some [foo]bar baz[/foo] content'));
    }

    /** @test */
    public function attributes_enclosed_in_double_quotes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals('This is some <foo bar=baz></foo> content', $this->shortcodes->parse('This is some [foo bar="baz"] content'));
    }

    /** @test */
    public function attributes_enclosed_in_single_quotes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals('This is some <foo bar=baz></foo> content', $this->shortcodes->parse('This is some [foo bar=\'baz\'] content'));
    }

    /** @test */
    public function attributes_multiple_attributes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals('This is some <foo bar=baz qux=foo></foo> content', $this->shortcodes->parse('This is some [foo bar=baz qux=foo] content'));
    }
}

class FooShortcode extends Shortcode
{
    public function parse()
    {
        $attr = '';

        if (! empty($this->attr)) {
            $attr = ' '.http_build_query($this->attr, '', ' ');
        }

        if (! empty($this->content)) {
            return "<foo{$attr}>{$this->content}</foo>";
        }

        return "<foo{$attr}></foo>";
    }
}