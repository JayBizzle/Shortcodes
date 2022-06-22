<?php

use Jaybizzle\Shortcodes\Shortcode;
use Jaybizzle\Shortcodes\Shortcodes;
use PHPUnit\Framework\TestCase;

class ParseTest extends TestCase
{
    /** @test */
    public function no_tags_added()
    {
        $shortcodes = new Shortcodes();

        $this->assertEquals(
            'This is some [foo /] content',
            $shortcodes->parse('This is some [foo /] content')
        );
    }

    /** @test */
    public function we_can_add_a_single_shortcode()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertCount(1, $shortcodes->shortcodeTags);
    }

    /** @test */
    public function we_can_add_multiple_shorcodes()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add([
            FooShortcode::class,
            BarShortcode::class,
        ]);

        $this->assertCount(2, $shortcodes->shortcodeTags);
    }

    /** @test */
    public function tags_with_closing_slash()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $shortcodes->parse('This is some [foo /] content')
        );
    }

    /** @test */
    public function tags_without_closing_slash()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $shortcodes->parse('This is some [foo] content')
        );
    }

    /** @test */
    public function tags_with_closing_slash_and_attributes()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $shortcodes->parse('This is some [foo bar=baz /] content')
        );
    }

    /** @test */
    public function tags_without_closing_slash_and_attributes()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $shortcodes->parse('This is some [foo bar=baz] content')
        );
    }

    /** @test */
    public function tags_with_opening_and_closing_tags()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo>bar baz</foo> content',
            $shortcodes->parse('This is some [foo]bar baz[/foo] content')
        );
    }

    /** @test */
    public function attributes_enclosed_in_double_quotes()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $shortcodes->parse('This is some [foo bar="baz"] content')
        );
    }

    /** @test */
    public function attributes_enclosed_in_single_quotes()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $shortcodes->parse('This is some [foo bar=\'baz\'] content')
        );
    }

    /** @test */
    public function attributes_multiple_attributes()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz qux=foo></foo> content',
            $shortcodes->parse('This is some [foo bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_strip_all_tags()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some content',
            $shortcodes->stripShortcodes('This is some [foo bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_strip_specified_tag()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some [bar bar=baz qux=foo] content',
            $shortcodes->stripShortcode('foo', 'This is some [foo bar=baz qux=foo] [bar bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_get_attributes_for_specified_shortcode()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            [],
            $shortcodes->getShortcode('foo', 'This is some [bar bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_strip_tags_when_no_tags_specified()
    {
        $shortcodes = new Shortcodes();

        $this->assertEquals(
            'This is some [foo bar=baz qux=foo] content',
            $shortcodes->stripShortcodes('This is some [foo bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_get_the_attributes_from_the_specified_tag()
    {
        $shortcodes = new Shortcodes();

        $expected = [[
            'bar' => 'baz',
            'qux' => 'foo',
        ]];

        $this->assertEquals(
            $expected,
            $shortcodes->getShortcode('foo', 'This is some [foo bar=baz qux=foo] [bar bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function we_can_get_the_attributes_from_all_tags()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);
        $shortcodes->add(BarShortcode::class);

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

        $shortcodes = new Shortcodes();

        $this->assertEquals(
            $expected,
            $shortcodes->getShortcodes('This is some [foo bar=baz qux=foo] [foo bar=baz qux=foo] [bar bar=baz qux=foo] content')
        );
    }

    /** @test */
    public function test_we_can_remove_single_shortcode_tag()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);
        $shortcodes->add(BarShortcode::class);

        $shortcodes->remove('foo');

        $this->assertCount(1, $shortcodes->shortcodeTags);
    }

    /** @test */
    public function test_we_can_remove_all_shortcode_tags()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);
        $shortcodes->add(BarShortcode::class);

        $shortcodes->removeAll('foo');

        $this->assertCount(0, $shortcodes->shortcodeTags);
    }

    /** @test */
    public function shortcode_tags_can_be_escaped()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some [foo bar=baz] content',
            $shortcodes->parse('This is some [[foo bar=baz]] content')
        );
    }

    /** @test */
    public function content_with_no_shortcodes()
    {
        $shortcodes = new Shortcodes();

        $this->assertEquals(
            'This is some content',
            $shortcodes->parse('This is some content')
        );
    }

    /** @test */
    public function shortcode_with_hyphens()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(BazShortcode::class);

        $this->assertEquals(
            'This is some <foo-bar></foo-bar> content',
            $shortcodes->parse('This is some [foo-bar] content')
        );
    }

    /** @test */
    public function attribute_with_double_quotes()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $shortcodes->parse('This is some [foo bar="baz"] content')
        );
    }

    /** @test */
    public function attribute_with_single_quotes()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $shortcodes->parse('This is some [foo bar=\'baz\'] content')
        );
    }

    /** @test */
    public function single_positional_attribute()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo 0=123></foo> content',
            $shortcodes->parse('This is some [foo 123] content')
        );
    }

    /** @test */
    public function attribute_with_url()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo url=http%3A%2F%2Fwww.foo.com%2Fbar></foo> content',
            $shortcodes->parse('This is some [foo url=http://www.foo.com/bar] content')
        );
    }

    /** @test */
    public function mixed_attribute_types()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo 0=123 1=http%3A%2F%2Ffoo.com%2F 2=0 3=foo 4=bar></foo> content',
            $shortcodes->parse('This is some [foo 123 http://foo.com/ 0 "foo" bar] content')
        );
    }

    /** @test */
    public function magic_properties_are_gettable()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(QuxShortcode::class);

        $shortcodes->parse('This is some [qux foo=bar]');
        $this->addToAssertionCount(1);
    }

    /** @test */
    public function exception_is_thrown_when_accessing_uknown_magic_property()
    {
        $shortcodes = new Shortcodes();

        $shortcodes->add(BazQuxShortcode::class);

        if (version_compare(PHP_VERSION, '7.0', '>=')) {
            $this->expectException(\Exception::class);
        } else {
            $this->setExpectedException(\Exception::class);
        }

        $shortcodes->parse('This is some [qux foo=bar]');
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
