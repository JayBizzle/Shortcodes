<?php

use PHPUnit\Framework\TestCase;
use Jaybizzle\Shortcodes\Shortcode;
use Jaybizzle\Shortcodes\Shortcodes;

class ParseTest extends TestCase
{
    public function setUp()
    {
        $this->shortcodes = new Shortcodes;
    }

    /** @test */
    public function no_tags_added()
    {
        $this->assertEquals(
            'This is some [foo /] content',
            $this->shortcodes->parse('This is some [foo /] content')
        );
    }

    /** @test */
    public function tags_with_closing_slash()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $this->shortcodes->parse('This is some [foo /] content')
        );
    }

    /** @test */
    public function tags_without_closing_slash()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $this->shortcodes->parse('This is some [foo] content')
        );
    }

    /** @test */
    public function tags_with_closing_slash_and_attributes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz /] content')
        );
    }

    /** @test */
    public function tags_without_closing_slash_and_attributes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz] content')
        );
    }

    /** @test */
    public function tags_with_opening_and_closing_tags()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo>bar baz</foo> content',
            $this->shortcodes->parse('This is some [foo]bar baz[/foo] content')
        );
    }

    /** @test */
    public function attributes_enclosed_in_double_quotes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar="baz"] content')
        );
    }

    /** @test */
    public function attributes_enclosed_in_single_quotes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=\'baz\'] content')
        );
    }

    /** @test */
    public function attributes_multiple_attributes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz qux=foo></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_strip_all_tags()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some content',
            $this->shortcodes->stripShortcodes('This is some [foo bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_strip_specified_tag()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some [bar bar=baz qux=foo] content',
            $this->shortcodes->stripShortcode('foo', 'This is some [foo bar=baz qux=foo] [bar bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_get_attributes_for_specified_shortcode()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            [],
            $this->shortcodes->getShortcode('foo', 'This is some [bar bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_strip_tags_when_no_tags_specified()
    {
        $this->assertEquals(
            'This is some [foo bar=baz qux=foo] content',
            $this->shortcodes->stripShortcodes('This is some [foo bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_get_the_attributes_from_the_specified_tag()
    {
        $expected = [[
            'bar' => 'baz',
            'qux' => 'foo',
        ]];

        $this->assertEquals(
            $expected,
            $this->shortcodes->getShortcode('foo', 'This is some [foo bar=baz qux=foo] [bar bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_get_the_attributes_from_all_tags()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);

        $expected = [
            'foo' => [[
                'bar' => 'baz',
                'qux' => 'foo',
            ], [
                'bar' => 'baz',
                'qux' => 'foo',
            ]],
            'bar' => [[
                'bar' => 'baz',
                'qux' => 'foo',
            ]],
        ];

        $this->assertEquals(
            $expected,
            $this->shortcodes->getShortcodes('This is some [foo bar=baz qux=foo] [foo bar=baz qux=foo] [bar bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function test_we_can_remove_single_shortcode_tag()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);

        $this->shortcodes->remove('foo');

        $this->assertCount(1, $this->shortcodes->shortcodeTags);
    }

    /** @test */
    public function test_we_can_remove_all_shortcode_tags()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);

        $this->shortcodes->removeAll('foo');

        $this->assertCount(0, $this->shortcodes->shortcodeTags);
    }

    /** @test */
    public function shortcode_tags_can_be_escaped()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some [foo bar=baz] content',
            $this->shortcodes->parse('This is some [[foo bar=baz]] content')
        );
    }

    /** @test */
    public function content_with_no_shortcodes()
    {
        $this->assertEquals(
            'This is some content',
            $this->shortcodes->parse('This is some content')
        );
    }

    /** @test */
    public function shortcode_with_hyphens()
    {
        $this->shortcodes->add(BazShortcode::class);

        $this->assertEquals(
            'This is some <foo-bar></foo-bar> content',
            $this->shortcodes->parse('This is some [foo-bar] content')
        );
    }

    /** @test */
    public function attribute_with_double_quotes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar="baz"] content')
        );
    }

    /** @test */
    public function attribute_with_single_quotes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=\'baz\'] content')
        );
    }

    /** @test */
    public function single_positional_attribute()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo 0=123></foo> content',
            $this->shortcodes->parse('This is some [foo 123] content')
        );
    }

    /** @test */
    public function attribute_with_url()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo url=http%3A%2F%2Fwww.foo.com%2Fbar></foo> content',
            $this->shortcodes->parse('This is some [foo url=http://www.foo.com/bar] content')
        );
    }

    /** @test */
    public function mixed_attribute_types()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo 0=123 1=http%3A%2F%2Ffoo.com%2F 2=0 3=foo 4=bar></foo> content',
            $this->shortcodes->parse('This is some [foo 123 http://foo.com/ 0 "foo" bar] content')
        );
    }

    /** @test */
    public function magic_properties_are_gettable()
    {
        $this->shortcodes->add(QuxShortcode::class);

        $this->shortcodes->parse('This is some [qux foo=bar]');
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function exception_is_thrown_when_accessing_uknown_magic_property()
    {
        $this->shortcodes->add(BazQuxShortcode::class);

        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            $this->expectException(\Exception::class);
        } else {
            $this->setExpectException(\Exception::class);
        }

        $this->shortcodes->parse('This is some [qux foo=bar]');
    }
}

class FooShortcode extends Shortcode
{
    public static $shortcode = 'foo';

    public function parse()
    {
        $attributes = '';

        if (! empty($this->attributes)) {
            $attributes = ' '.http_build_query($this->attributes, '', ' ');
        }

        if (! empty($this->content)) {
            return "<{$this->shortcode}{$attributes}>{$this->content}</{$this->shortcode}>";
        }

        return "<{$this->shortcode}{$attributes}></{$this->shortcode}>";
    }
}

class BarShortcode extends Shortcode
{
    public static $shortcode = 'bar';

    public function parse()
    {
        //
    }
}

class BazShortcode extends FooShortcode
{
    public static $shortcode = 'foo-bar';
}

class QuxShortcode extends Shortcode
{
    public static $shortcode = 'qux';

    public function parse()
    {
        $this->foo;
    }
}

class BazQuxShortcode extends Shortcode
{
    public static $shortcode = 'qux';

    public function parse()
    {
        $this->doesntExist;
    }
}
