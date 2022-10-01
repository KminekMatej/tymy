<?php

namespace Tymy\Module\Core\Service;

/**
 * Description of BbService
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 18. 9. 2020
 */
class BbService
{
    private static array $find = [];
    private static array $replace = [];

    private static function add(string $pattern, string $replace): void
    {
        self::$find[] = $pattern;
        self::$replace[] = $replace;
    }

    private static function init(): void
    {
        if (!empty(self::$find)) {
            return;
        }

        self::add('~\[b\](.*?)\[/b\]~s', '<strong>$1</strong>');
        self::add('~\[i\](.*?)\[/i\]~s', '<em>$1</em>');
        self::add('~\[u\](.*?)\[/u\]~s', '<u>$1</u>');
        self::add('~\[s\](.*?)\[/s\]~s', '<s>$1</s>');
        self::add('~\[quote\]([^"><]*?)\[/quote\]~s', '<blockquote>$1</blockquote>');
        self::add('~\[code\]([^"><]*?)\[/code\]~s', '<pre>$1</pre>');
        self::add('~\[pre\]([^"><]*?)\[/pre\]~s', '<pre>$1</pre>');
        self::add('~\[size=([^"><]*?)\](.*?)\[/size\]~s', '<span style="font-size:$1;">$2</span>');
        self::add('~\[color=([^"><]*?)\](.*?)\[/color\]~s', '<span style="color:$1;">$2</span>');
        self::add('~\[url\](:ftp|https?://[-a-zA-Z0-9+&amp;@#/%?=\~_|!:,.;]*[-a-zA-Z0-9+&amp;@#/%=\~_|])\[/url\]~s', '<a href="$1" rel="nofollow">$1</a>');
        self::add('~\[url=(:ftp|https?://[-a-zA-Z0-9+&amp;@#/%?=\~_|!:,.;]*[-a-zA-Z0-9+&amp;@#/%=\~_|])\]\[/url\]~s', '<a href="$1" rel="nofollow">$1</a>');
        self::add('~\[url=(:ftp|https?://[-a-zA-Z0-9+&amp;@#/%?=\~_|!:,.;]*[-a-zA-Z0-9+&amp;@#/%=\~_|])\](.*?)\[/url\]~s', '<a href="$1" rel="nofollow">$2</a>');
        self::add('~\[url\]mailto\:([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\[/url\]~s', '<a href="mailto:$1" rel="nofollow">$1</a>');
        self::add('~\[url=mailto\:([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\]\[/url\]~s', '<a href="mailto:$1" rel="nofollow">$1</a>');
        self::add('~\[url=mailto\:([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\](.*?)\[/url\]~s', '<a href="mailto:$1" rel="nofollow">$2</a>');
        self::add('~\[img\](https?://[^"><]*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s', '<img src="$1" alt="" />');
        self::add('~\[hr\]~s', '<hr />');
        self::add('~\[left\](.*?)\[/left\]~s', '<div style="text-align: left;">$1</div>');
        self::add('~\[center\](.*?)\[/center\]~s', '<div style="text-align: center;">$1</div>');
        self::add('~\[right\](.*?)\[/right\]~s', '<div style="text-align: right;">$1</div>');
        self::add('~\[h1\](.*?)\[/h1\]~s', '<h1>$1</h1>');
        self::add('~\[h2\](.*?)\[/h2\]~s', '<h2>$1</h2>');
        self::add('~\[h3\](.*?)\[/h3\]~s', '<h3>$1</h3>');
        self::add('~\[h4\](.*?)\[/h4\]~s', '<h4>$1</h4>');
        self::add('~\[h5\](.*?)\[/h5\]~s', '<h5>$1</h5>');
        self::add('~\[h6\](.*?)\[/h6\]~s', '<h6>$1</h6>');
        self::add('~\[table\](.*?)\[/table\]~s', '<table border="1" cellpadding="3" cellspacing="1">$1</table>');
        self::add('~\[tr\](.*?)\[/tr\]~s', '<tr>$1</tr>');
        self::add('~\[th\](.*?)\[/th\]~s', '<th>$1</th>');
        self::add('~\[td\](.*?)\[/td\]~s', '<td>$1</td>');
        self::add('~\[\*\]([^\[]*)$~m', '<li>$1</li>');
        self::add('~\[list=([^"><]*?)\](.*?)\[/list\]~s', '<ol type="$1">$2</ol>');
        self::add('~\[list\](.*?)\[/list\]~s', '<ul>$1</ul>');
        self::add('~\[email\]([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\[/email\]~s', '<a href="mailto:$1" rel="nofollow">$1</a>');
        self::add('~LINK:(:ftp|https?://[-a-zA-Z0-9+&amp;@#/%?=\~_|!:,.;]*[-a-zA-Z0-9+&amp;@#/%=\~_|])\|(.*)~s', '<a href="$1" rel="nofollow">$2</a>');
        self::add('~LINK:(:ftp|https?://[-a-zA-Z0-9+&amp;@#/%?=\~_|!:,.;]*[-a-zA-Z0-9+&amp;@#/%=\~_|])~s', '<a href="$1" rel="nofollow">$1</a>');
        self::add('~(?<![href=\"|IMG\:|nofollow\"\>])(:ftp|https?://[-a-zA-Z0-9+&amp;@#/%?=\~_|!:,.;]*[-a-zA-Z0-9+&amp;@#/%=\~_|])~s', '<a href="$1" rel="nofollow">$1</a>');
        self::add('~IMG:(https?://[^"><]*?\.(?:jpg|jpeg|gif|png|bmp))~s', '<img src="$1" alt="" />');
        self::add('~MAIL:([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})~s', '<a href="mailto:$1" rel="nofollow">$1</a>');
    }

    /**
     * Transform BB codes into appropriate HTML tags
     *
     * @param string $text written in BB code
     * @return string|null Text with html tags
     * @todo Finish all neccessary tags
     */
    public static function bb2Html(string $text): ?string
    {
        self::init();
        $text = preg_replace(self::$find, self::$replace, strip_tags($text));
        $text = nl2br($text);
        return preg_replace("/\n|\r/", "", $text);
    }
}
