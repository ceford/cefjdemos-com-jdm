<?php

/**
 * @package     Jdocmanual
 * @subpackage  Administrator
 *
 * @copyright   (C) 2023 Clifford E Ford. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Cefjdemos\Component\Jdocmanual\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

class InthispageHelper
{
    public static function doToc($html)
    {
        // Remove Markdown generated content (or rebuild all articles)
        $pattern = '/<ul class="table-of-contents">.*?<!-- /s';
        $html = preg_replace($pattern, '<!-- ', $html);

        // Get a list of headings in the article.
        $pattern = '/<h([1-6])>(.*?)<\/h[1-6]>/s';
        //$pattern = '/<h([1-6])[^>]*>(.*?)<a.*><\/h[1-6]>/i';
        preg_match_all($pattern, $html, $headings2, PREG_SET_ORDER);

        $menu = [];
        // The text of the article needs to have anchors (id values) added to headings
        foreach ($headings2 as $i => $heading) {
            $old = '<h' . $heading[1] . '>' . $heading[2] . '</h' . $heading[1] . '>';
            $menu[] = array($heading[1], $i, $heading[2]);
            $new = '<h' . $heading[1] . ' id="' . $i . '">' . $heading[2] . '</h' . $heading[1] . '>';
            $html = str_replace($heading[0], $new, $html);
        }

        $in_this_page = '<h2 class="toc fs-4">' . Text::_('COM_JDOCMANUAL_MANUAL_TOC_IN_THIS_ARTICLE') . '</h2>' . "\n";
        $in_this_page .= '<ul>' . "\n";
        foreach ($menu as $i => $item) {
            $in_this_page .= '<li class="fs-' . $item[0] + 2 . ' jdm-indent-' . $item[0] - 2 . '">' . "\n";
            $in_this_page .= '<a href="#' . $i .'" class="link-underline-light">' . $item[2] . '</a>' . "\n";
            $in_this_page .= '</li>'. "\n";
        }
        $in_this_page .= '</ul>' . "\n";
        return array($in_this_page, $html);
    }

    public static function doTocOld($html)
    {
        // The commonmark menu position feature does not work.
        // So extract it here from the 'top' position.
        $dom = new \DOMDocument('1.0', 'utf-8');

        // DOMDocument::loadHTML will treat your string as being in ISO-8859-1.
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // It can go wrong!
        if (empty($dom)) {
            return ['Problem', $html];
        }
        // If there are headings there will be a table of contents
        $xpath = new \DOMXPath($dom);
        $uls = $xpath->query("//ul[@class='table-of-contents']");
        $in_this_page = '';
        if (count($uls) > 0) {
            $in_this_page = $dom->saveHTML($uls->item(0));
            $test = $uls[0]->parentNode->removeChild($uls[0]);
        }
        $content = $dom->saveHTML();

        // Seems to be a bug that inserts </source></source> in picture tags.
        $content = str_replace('</source></source>', '', $content);

        // Remove the xml statement added above.
        $content = str_replace('<?xml encoding="utf-8" ?>', '', $content);

        $in_this_page = '<h2 class="toc">' . Text::_('COM_JDOCMANUAL_MANUAL_TOC_IN_THIS_ARTICLE') .
            '</h2>' . "\n" . $in_this_page;
        return array($in_this_page, $content);
    }

    public static function getPreviousNext($previous, $next)
    {
        // Code for the feedback button triggers
        $dislike = '
        data-bs-toggle="modal"
	    data-bs-target="#jdmFeedback"
	    data-bs-id="dislike"';

        $like = '
        data-bs-toggle="modal"
	    data-bs-target="#jdmFeedback"
	    data-bs-id="like"';

        $comment = '
        data-bs-toggle="modal"
	    data-bs-target="#jdmFeedback"
	    data-bs-id="comment"';

        // Are feedback thumbs and comments enabled?
        $params = ComponentHelper::getParams('com_jdocmanual');
        $enable_likeordislike = $params->get('enable_likeordislike');
        $enable_comments = $params->get('enable_comments');

        // Add the next and previous links to $content
        $tmpl = '
        <div class="container text-center">
            <div class="row">
                <div class="col">
                    ' . $previous . '
                </div>
                ';
        if ($enable_likeordislike) {
            $tmpl .= '
                <div class="col">
                    <button type="button" class="btn btn-outline-secondary" aria-label="This article was not helpful"' . $dislike . '><i class="fa-solid fa-thumbs-down"></i></button>
                    ';
            if ($enable_comments) {
                $tmpl .= '
                    <button type="button" class="btn btn-outline-secondary" aria-label="Invitation to comment on this article"' . $comment . '><i class="fa-solid fa-question"></i></button>
                    ';
            }
                $tmpl .= '
                    <button type="button" class="btn btn-outline-secondary" aria-label="This article was helpful"' . $like . '><i class="fa-solid fa-thumbs-up"></i></button>
                </div>
                ';
        }
        $tmpl .= '
                <div class="col">
                    ' . $next . '
                </div>
            </div>
        </div>';
        return $tmpl;
    }

    public static function trim2review($html)
    {
        // Ensure UTF-8 input
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true); // suppress warnings for malformed HTML
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Find the first <h2> element with class="original"
        $h2 = $xpath->query('//h2[contains(concat(" ", normalize-space(@class), " "), " original ")]')->item(0);

        if ($h2) {
            // Remove everything after this h2
            while ($h2->nextSibling) {
                $h2->parentNode->removeChild($h2->nextSibling);
            }
            // Optionally remove the h2 itself (comment out if you want to keep it)
            $h2->parentNode->removeChild($h2);
        }

        // Get the resulting trimmed HTML
        return $dom->saveHTML();
    }
}
