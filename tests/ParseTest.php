<?php

use PHPUnit\Framework\TestCase;
use Jaybizzle\Shortcodes\Shortcode;
use Jaybizzle\Shortcodes\Shortcodes;

class ParseTest extends TestCase
{
    protected Shortcodes $shortcodes;

    protected function setUp(): void
    {
        $this->shortcodes = new Shortcodes;
    }

    public function test_no_tags_added()
    {
        $this->assertEquals(
            'This is some [foo /] content',
            $this->shortcodes->parse('This is some [foo /] content')
        );
    }

    public function test_we_can_add_a_single_shortcode()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertCount(1, $this->shortcodes->shortcodeTags);
    }

    public function test_we_can_add_multiple_shorcodes()
    {
        $this->shortcodes->add([
            FooShortcode::class,
            BarShortcode::class,
        ]);

        $this->assertCount(2, $this->shortcodes->shortcodeTags);
    }

    public function test_tags_with_closing_slash()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $this->shortcodes->parse('This is some [foo /] content')
        );
    }

    public function test_tags_without_closing_slash()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $this->shortcodes->parse('This is some [foo] content')
        );
    }

    public function test_tags_with_closing_slash_and_attributes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz /] content')
        );
    }

    public function test_tags_without_closing_slash_and_attributes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz] content')
        );
    }

    public function test_tags_with_opening_and_closing_tags()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo>bar baz</foo> content',
            $this->shortcodes->parse('This is some [foo]bar baz[/foo] content')
        );
    }

    public function test_attributes_enclosed_in_double_quotes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar="baz"] content')
        );
    }

    public function test_attributes_enclosed_in_single_quotes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=\'baz\'] content')
        );
    }

    public function test_attributes_multiple_attributes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz qux=foo></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz qux=foo] content')
        );
    }

    public function test_we_can_strip_all_tags()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some content',
            $this->shortcodes->stripShortcodes('This is some [foo bar=baz qux=foo] content')
        );
    }

    public function test_we_can_strip_specified_tag()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some [bar bar=baz qux=foo] content',
            $this->shortcodes->stripShortcode('foo', 'This is some [foo bar=baz qux=foo] [bar bar=baz qux=foo] content')
        );
    }

    public function test_we_can_get_attributes_for_specified_shortcode()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            [],
            $this->shortcodes->getShortcode('foo', 'This is some [bar bar=baz qux=foo] content')
        );
    }

    public function test_we_can_strip_tags_when_no_tags_specified()
    {
        $this->assertEquals(
            'This is some [foo bar=baz qux=foo] content',
            $this->shortcodes->stripShortcodes('This is some [foo bar=baz qux=foo] content')
        );
    }

    public function test_we_can_get_the_attributes_from_the_specified_tag()
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

    public function test_we_can_get_the_attributes_from_all_tags()
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

    public function test_we_can_remove_single_shortcode_tag()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);

        $this->shortcodes->remove('foo');

        $this->assertCount(1, $this->shortcodes->shortcodeTags);
    }

    public function test_we_can_remove_all_shortcode_tags()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);

        $this->shortcodes->removeAll('foo');

        $this->assertCount(0, $this->shortcodes->shortcodeTags);
    }

    public function test_shortcode_tags_can_be_escaped()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some [foo bar=baz] content',
            $this->shortcodes->parse('This is some [[foo bar=baz]] content')
        );
    }

    public function test_content_with_no_shortcodes()
    {
        $this->assertEquals(
            'This is some content',
            $this->shortcodes->parse('This is some content')
        );
    }

    public function test_shortcode_with_hyphens()
    {
        $this->shortcodes->add(BazShortcode::class);

        $this->assertEquals(
            'This is some <foo-bar></foo-bar> content',
            $this->shortcodes->parse('This is some [foo-bar] content')
        );
    }

    public function test_attribute_with_double_quotes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar="baz"] content')
        );
    }

    public function test_attribute_with_single_quotes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=\'baz\'] content')
        );
    }

    public function test_single_positional_attribute()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo 0=123></foo> content',
            $this->shortcodes->parse('This is some [foo 123] content')
        );
    }

    public function test_attribute_with_url()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo url=http%3A%2F%2Fwww.foo.com%2Fbar></foo> content',
            $this->shortcodes->parse('This is some [foo url=http://www.foo.com/bar] content')
        );
    }

    public function test_mixed_attribute_types()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'This is some <foo 0=123 1=http%3A%2F%2Ffoo.com%2F 2=0 3=foo 4=bar></foo> content',
            $this->shortcodes->parse('This is some [foo 123 http://foo.com/ 0 "foo" bar] content')
        );
    }

    public function test_magic_properties_are_gettable()
    {
        $this->shortcodes->add(QuxShortcode::class);

        $this->shortcodes->parse('This is some [qux foo=bar]');
        $this->addToAssertionCount(1);
    }

    public function test_exception_is_thrown_when_accessing_uknown_magic_property()
    {
        $this->shortcodes->add(BazQuxShortcode::class);

        $this->expectException(\Exception::class);

        $this->shortcodes->parse('This is some [qux foo=bar]');
    }

    public function test_parse_returns_empty_string_for_empty_input()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals('', $this->shortcodes->parse(''));
    }

    public function test_strip_shortcodes_returns_empty_string_for_empty_input()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals('', $this->shortcodes->stripShortcodes(''));
    }

    public function test_strip_shortcode_returns_empty_string_for_empty_input()
    {
        $this->assertEquals('', $this->shortcodes->stripShortcode('foo', ''));
    }

    public function test_get_shortcode_returns_empty_array_for_null_input()
    {
        $this->assertEquals([], $this->shortcodes->getShortcode('foo', null));
    }

    public function test_get_shortcode_returns_empty_array_when_no_matches()
    {
        $this->assertEquals(
            [],
            $this->shortcodes->getShortcode('foo', 'This has no shortcodes')
        );
    }

    public function test_get_shortcodes_with_no_matching_tags()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);

        $expected = [
            'foo' => [],
            'bar' => [],
        ];

        $this->assertEquals(
            $expected,
            $this->shortcodes->getShortcodes('This has no shortcodes at all')
        );
    }

    public function test_multiple_instances_of_same_shortcode()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            '<foo></foo> and <foo></foo>',
            $this->shortcodes->parse('[foo] and [foo]')
        );
    }

    public function test_shortcode_with_content_and_attributes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'Some <foo bar=baz>inner content</foo> here',
            $this->shortcodes->parse('Some [foo bar=baz]inner content[/foo] here')
        );
    }

    public function test_nested_shortcodes()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);

        $result = $this->shortcodes->parse('[foo][bar][/bar][/foo]');

        $this->assertStringContainsString('<foo>', $result);
    }

    public function test_shortcode_at_start_of_string()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            '<foo></foo> content',
            $this->shortcodes->parse('[foo] content')
        );
    }

    public function test_shortcode_at_end_of_string()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            'content <foo></foo>',
            $this->shortcodes->parse('content [foo]')
        );
    }

    public function test_shortcode_is_entire_string()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            '<foo></foo>',
            $this->shortcodes->parse('[foo /]')
        );
    }

    public function test_adjacent_shortcodes()
    {
        $this->shortcodes->add(FooShortcode::class);

        $result = $this->shortcodes->parse('[foo /][foo /]');

        $this->assertStringContainsString('<foo></foo>', $result);
    }

    public function test_shortcode_with_multiline_content()
    {
        $this->shortcodes->add(FooShortcode::class);

        $this->assertEquals(
            "Some <foo>line one\nline two</foo> here",
            $this->shortcodes->parse("Some [foo]line one\nline two[/foo] here")
        );
    }

    public function test_removed_shortcode_is_no_longer_parsed()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);
        $this->shortcodes->remove('foo');

        $this->assertEquals(
            'This is some [foo /] content',
            $this->shortcodes->parse('This is some [foo /] content')
        );
    }

    public function test_remove_all_then_parse_returns_content_unchanged()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);
        $this->shortcodes->removeAll();

        $this->assertEquals(
            'Some [foo /] and [bar /] content',
            $this->shortcodes->parse('Some [foo /] and [bar /] content')
        );
    }

    public function test_strip_shortcode_leaves_other_tags_intact()
    {
        $this->shortcodes->add(FooShortcode::class);
        $this->shortcodes->add(BarShortcode::class);

        $result = $this->shortcodes->stripShortcode('foo', '[foo bar=1] and [bar baz=2]');

        $this->assertStringContainsString('[bar baz=2]', $result);
        $this->assertStringNotContainsString('[foo', $result);
    }

    public function test_get_shortcode_returns_multiple_matches()
    {
        $expected = [
            ['id' => '1'],
            ['id' => '2'],
        ];

        $this->assertEquals(
            $expected,
            $this->shortcodes->getShortcode('foo', '[foo id=1] and [foo id=2]')
        );
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
