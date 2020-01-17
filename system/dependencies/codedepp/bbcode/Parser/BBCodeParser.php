<?php

namespace dFramework\dependencies\codedepp\bbcode\Parser;

final class BBCodeParser extends Parser
{
    protected $parsers = [
        'namedquote' => [
            'pattern' => "/\[quote=\"([^\"]+)\"\](.*?)\[\/quote\]/s",
            'replace' => '<blockquote><au>$1</au><br/>$2</blockquote>',
            'content' => '$2'
        ],
        'size' => [
            'pattern' => "/\[size=(\d+)\](.*?)\[\/size\]/s",
            'replace' => '<span style="font-size:$1%;">$2</span>',
            'content' => '$2'
        ],
        'color' => [
            'pattern' => "/\[color=([#a-z0-9]+)\](.*?)\[\/color\]/s",
            'replace' => '<span style="color:$1;">$2</span>',
            'content' => '$2'
        ],
        'center' => [
            'pattern' => "/\[center\](.*?)\[\/center\]/s",
            'replace' => '<span style="display:inline-block;width:auto;text-align:center;">$1</span>',
            'content' => '$1'
        ],
        'email' => [
            'pattern' => "/\[email\](.*?)\[\/email\]/s",
            'replace' => '<a href="mailto:$1">$1</a>',
            'content' => '$1'
        ],
        'namedemail' => [
            'pattern' => "/\[email=(.*?)\](.*?)\[\/email\]/s",
            'replace' => '<a href="mailto:$1">$2</a>',
            'content' => '$2'
        ],
        'breakline' => [
            'pattern' => '/\[br](.*)/s',
            'replace' => '<br/>$1',
            'content' => '$1'
        ],


        'h1' => [
            'pattern' => '/\[h1\](.*?)\[\/h1\]/s',
            'replace' => '<h1>$1</h1>',
            'content' => '$1'
        ],
        'h2' => [
            'pattern' => '/\[h2\](.*?)\[\/h2\]/s',
            'replace' => '<h2>$1</h2>',
            'content' => '$1'
        ],
        'h3' => [
            'pattern' => '/\[h3\](.*?)\[\/h3\]/s',
            'replace' => '<h3>$1</h3>',
            'content' => '$1'
        ],
        'h4' => [
            'pattern' => '/\[h4\](.*?)\[\/h4\]/s',
            'replace' => '<h4>$1</h4>',
            'content' => '$1'
        ],
        'h5' => [
            'pattern' => '/\[h5\](.*?)\[\/h5\]/s',
            'replace' => '<h5>$1</h5>',
            'content' => '$1'
        ],
        'h6' => [
            'pattern' => '/\[h6\](.*?)\[\/h6\]/s',
            'replace' => '<h6>$1</h6>',
            'content' => '$1'
        ],
        'bold' => [
            'pattern' => '/\[b\](.*?)\[\/b\]/s',
            'replace' => '<b>$1</b>',
            'content' => '$1'
        ],
        'italic' => [
            'pattern' => '/\[i\](.*?)\[\/i\]/s',
            'replace' => '<i>$1</i>',
            'content' => '$1'
        ],
        'underline' => [
            'pattern' => '/\[u\](.*?)\[\/u\]/s',
            'replace' => '<u>$1</u>',
            'content' => '$1'
        ],
        'strikethrough' => [
            'pattern' => '/\[s\](.*?)\[\/s\]/s',
            'replace' => '<s>$1</s>',
            'content' => '$1'
        ],
        'quote' => [
            'pattern' => '/\[quote\](.*?)\[\/quote\]/s',
            'replace' => '<blockquote>$1</blockquote>',
            'content' => '$1'
        ],
        'link' => [
            'pattern' => '/\[url\](.*?)\[\/url\]/s',
            'replace' => '<a href="$1">$1</a>',
            'content' => '$1'
        ],
        'namedlink' => [
            'pattern' => '/\[url\=(.*?)\](.*?)\[\/url\]/s',
            'replace' => '<a href="$1">$2</a>',
            'content' => '$2'
        ],
        'image' => [
            'pattern' => '/\[img\](.*?)\[\/img\]/s',
            'replace' => '<img src="$1">',
            'content' => '$1'
        ],
        'orderedlistnumerical' => [
            'pattern' => '/\[list=1\](.*?)\[\/list\]/s',
            'replace' => '<ol>$1</ol>',
            'content' => '$1'
        ],
        'orderedlistalpha' => [
            'pattern' => '/\[list=a\](.*?)\[\/list\]/s',
            'replace' => '<ol type="a">$1</ol>',
            'content' => '$1'
        ],
        'unorderedlist' => [
            'pattern' => '/\[list\](.*?)\[\/list\]/s',
            'replace' => '<ul>$1</ul>',
            'content' => '$1'
        ],
        'listitem' => [
            'pattern' => '/\[\*\](.*)/',
            'replace' => '<li>$1</li>',
            'content' => '$1'
        ],
        'code' => [
            'pattern' => '/\[code\](.*?)\[\/code\]/s',
            'replace' => '<pre><code>$1</code></pre>',
            'content' => '$1'
        ],
        'youtube' => [
            'pattern' => "/\[youtube\](?:https?:\/\/)?(?:www\.)?youtu(?:\.be\/|be\.com\/watch\?v=)([A-Z0-9\-_]+)(?:&(.*?))?\[\/youtube\]/s",
            'replace' => '<iframe width="640" height="385" class="youtube-player" src="//www.youtube-nocookie.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
            'content' => '$1'
        ],
        'sub' => [
            'pattern' => '/\[sub\](.*?)\[\/sub\]/s',
            'replace' => '<sub>$1</sub>',
            'content' => '$1'
        ],
        'sup' => [
            'pattern' => '/\[sup\](.*?)\[\/sup\]/s',
            'replace' => '<sup>$1</sup>',
            'content' => '$1'
        ],
        'small' => [
            'pattern' => '/\[small\](.*?)\[\/small\]/s',
            'replace' => '<small>$1</small>',
            'content' => '$1'
        ],
        'table' => [
            'pattern' => '/\[table\](.*?)\[\/table\]/s',
            'replace' => '<table>$1</table>',
            'content' => '$1',
        ],
        'table-row' => [
            'pattern' => '/\[tr\](.*?)\[\/tr\]/s',
            'replace' => '<tr>$1</tr>',
            'content' => '$1',
        ],
        'table-data' => [
            'pattern' => '/\[td\](.*?)\[\/td\]/s',
            'replace' => '<td>$1</td>',
            'content' => '$1',
        ]
    ];

    public function stripTags(string $source): string
    {
        foreach ($this->parsers as $name => $parser) {
            $source = $this->searchAndReplace($parser['pattern'] . 'i', $parser['content'], $source);
        }

        return $source;
    }

    public function parse(string $source, $caseInsensitive = null): string
    {
        $caseInsensitive = $caseInsensitive === self::CASE_INSENSITIVE ? true : false;

        foreach ($this->parsers as $name => $parser) {
            $pattern = ($caseInsensitive) ? $parser['pattern'] . 'i' : $parser['pattern'];

            $source = $this->searchAndReplace($pattern, $parser['replace'], $source);
        }

        return $source;
    }
}
