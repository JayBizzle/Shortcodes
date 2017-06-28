<?php

namespace Jaybizzle\Shortcodes;

class Shortcodes
{
    /**
     * Container for storing shortcode tags and their hook to call for the shortcode.
     * 
     * @var array
     */
    public $shortcodeTags = [];

    /**
     * Add shortcode hooks.
     * 
     * @param string $tag
     * @param string $class
     */
    public function add($tag, $class)
    {
        $this->shortcodeTags[$tag] = $class;
    }

    /**
     * Remove shortcode tag from shortcode container.
     * 
     * @param string $tag
     */
    public function remove($tag)
    {
        unset($this->shortcodeTags[$tag]);
    }

    /**
     * Remove all shortcode tags from the shortcode container.
     */
    public function removeAll()
    {
        $this->shortcodeTags = [];
    }

    /**
     * Search content for shortcodes and filter shortcodes through their hooks.
     * 
     * @param  string $content
     * @return string
     */
    public function parse($content)
    {
        if (empty($this->shortcodeTags) || ! is_array($this->shortcodeTags)) {
            return $content;
        }

        $pattern = $this->getShortcodeRegex();

        return preg_replace_callback('/'.$pattern.'/s', [$this, 'doShortcodeTag'], $content);
    }

    /**
     * Retrieve the shortcode regular expression for searching.
     *   
     * @return string
     */
    public function getShortcodeRegex()
    {
        $tagnames = array_keys($this->shortcodeTags);
        $tagregexp = implode('|', array_map('preg_quote', $tagnames));

        // WARNING! Do not change this regex without changing doShortcodeTag() and stripShortcodes()
        return '(.?)\[('.$tagregexp.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)';
    }

    /**
     * Regular Expression callable for do_shortcode() for calling shortcode hook.
     * 
     * @param  array $matches
     * @return string
     */
    public function doShortcodeTag($matches)
    {
        // allow [[foo]] syntax for escaping a tag
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }

        $tag = $matches[2];
        $attr = $this->shortcodeParseAtts($matches[3]);

        $className = $this->shortcodeTags[$tag];

        if (isset($matches[5])) {
            // enclosing tag - extra parameter
            $parsed = (new $className($attr, $matches[5], $tag))->parse();

            return $matches[1].$parsed.$matches[6];
        } else {
            // self-closing tag
            $parsed = (new $className($attr, null, $tag))->parse();

            return $matches[1].$parsed.$matches[6];
        }
    }

    /**
     * Retrieve all attributes from the shortcodes tag.
     * 
     * @param  string $text
     * @return array
     */
    public function shortcodeParseAtts($text)
    {
        $atts = [];
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (! empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (! empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (! empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) and strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                }
            }
        } else {
            $atts = ltrim($text);
        }

        return $atts;
    }

    /**
     * Remove all shortcode tags from the given content.
     *
     * @param  string $content
     * @return string
     */
    public function stripShortcodes($content)
    {
        if (empty($this->shortcodeTags) || ! is_array($this->shortcodeTags)) {
            return $content;
        }

        $pattern = $this->getShortcodeRegex();

        return preg_replace('/'.$pattern.'/s', '$1$6', $content);
    }
}
