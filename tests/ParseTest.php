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

    public function testNoTagsAdded()
    {
        $this->assertEquals(
            'This is some [foo /] content',
            $this->shortcodes->parse('This is some [foo /] content')
        );
    }

    public function testTagsWithClosingSlash()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $this->shortcodes->parse('This is some [foo /] content')
        );
    }

    public function testTagsWithoutClosingSlash()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $this->shortcodes->parse('This is some [foo] content')
        );
    }

    public function testTagsWithClosingSlashAndAttributes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz /] content')
        );
    }

    public function testTagsWithoutClosingSlashAndAttributes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz] content')
        );
    }

    public function testTagsWithOpeningAndClosingTags()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo>bar baz</foo> content',
            $this->shortcodes->parse('This is some [foo]bar baz[/foo] content')
        );
    }

    public function testAttributesEnclosedInDoubleQuotes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar="baz"] content')
        );
    }

    public function testAttributesEnclosedInSingleQuotes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=\'baz\'] content')
        );
    }

    public function testAttributesMultipleAttributes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz qux=foo></foo> content',
            $this->shortcodes->parse('This is some [foo bar=baz qux=foo] content')
        );
    }

    public function testWeCanStripAllTags()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some content',
            $this->shortcodes->stripShortcodes('This is some [foo bar=baz qux=foo] content')
        );
    }

    public function testWeCanStripSpecifiedTag()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some [bar bar=baz qux=foo] content',
            $this->shortcodes->stripShortcode('foo', 'This is some [foo bar=baz qux=foo] [bar bar=baz qux=foo] content')
        );
    }

    public function testWeCanGetAttributesForSpecifiedShortcode()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            [],
            $this->shortcodes->getShortcode('foo', 'This is some [bar bar=baz qux=foo] content')
        );
    }

    public function testWeCanStripTagsWhenNoTagsSpecified()
    {
        $this->assertEquals(
            'This is some [foo bar=baz qux=foo] content',
            $this->shortcodes->stripShortcodes('This is some [foo bar=baz qux=foo] content')
        );
    }

    public function testWeCanGetTheAttributesFromTheSpecifiedTag()
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

    public function testWeCanGetTheAttributesFromAllTags()
    {
        $this->shortcodes->add('foo', FooShortcode::class);
        $this->shortcodes->add('bar', FooShortcode::class);

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

    public function testWeCanRemoveSingleShortcodeTag()
    {
        $this->shortcodes->add('foo', FooShortcode::class);
        $this->shortcodes->add('bar', FooShortcode::class);

        $this->shortcodes->remove('foo');

        $this->assertCount(1, $this->shortcodes->shortcodeTags);
    }

    public function testWeCanRemoveAllShortcodeTags()
    {
        $this->shortcodes->add('foo', FooShortcode::class);
        $this->shortcodes->add('bar', FooShortcode::class);

        $this->shortcodes->removeAll('foo');

        $this->assertCount(0, $this->shortcodes->shortcodeTags);
    }

    public function testShortcodeTagsCanBeEscaped()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some [foo bar=baz] content',
            $this->shortcodes->parse('This is some [[foo bar=baz]] content')
        );
    }

    public function testContentWithNoShortcodes()
    {
        $this->assertEquals(
            'This is some content',
            $this->shortcodes->parse('This is some content')
        );
    }

    public function testShortcodeWithHyphens()
    {
        $this->shortcodes->add('foo-bar', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo></foo> content',
            $this->shortcodes->parse('This is some [foo-bar] content')
        );
    }

    public function testAtributeWithDoubleQuotes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar="baz"] content')
        );
    }

    public function testAttributeWithSingleQuotes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo bar=baz></foo> content',
            $this->shortcodes->parse('This is some [foo bar=\'baz\'] content')
        );
    }

    public function testSinglePositionalAttribute()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo 0=123></foo> content',
            $this->shortcodes->parse('This is some [foo 123] content')
        );
    }

    public function testAttributeWithUrl()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo url=http%3A%2F%2Fwww.foo.com%2Fbar></foo> content',
            $this->shortcodes->parse('This is some [foo url=http://www.foo.com/bar] content')
        );
    }

    public function testMixedAttributeTypes()
    {
        $this->shortcodes->add('foo', FooShortcode::class);

        $this->assertEquals(
            'This is some <foo 0=123 1=http%3A%2F%2Ffoo.com%2F 2=0 3=foo 4=bar></foo> content',
            $this->shortcodes->parse('This is some [foo 123 http://foo.com/ 0 "foo" bar] content')
        );
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
