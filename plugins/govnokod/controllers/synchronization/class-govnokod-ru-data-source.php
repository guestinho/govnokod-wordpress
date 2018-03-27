<?php

require_once dirname(__FILE__) . '/interface-govnokod-data-source.php';

class GovnokodRuDataSource implements IGovnokodDataSource {

    /**
     * @var string target site url
     */
    public $baseUrl = GK_LEGACY_SITE_URL;

    /**
     * @var GovnokodRuLanguageDecoder
     */
    public $languageDecoder;

    /**
     * @var GovnokodRuTextDecoder
     */
    public $textDecoder;

    public function __construct() {
        $this->languageDecoder = new GovnokodRuLanguageDecoder();
        $this->textDecoder = new GovnokodRuTextDecoder();
    }

    private function _postUrl($post_id) {
        return $this->baseUrl . '/' . $post_id;
    }

    /**
     * @param $post_id
     * @return GovnokodRuPostModel|null|WP_Error
     */
    public function loadPost($post_id) {
        $resp = wp_remote_get($this->_postUrl($post_id), array('timeout' => 5));
        if (is_wp_error($resp)) {
            $resp->add('url', $this->_postUrl($post_id));
            return $resp;
        }

        if ($resp['response']['code'] !== 200) {
            return new WP_Error('code' . $resp['response']['code'], $this->_postUrl($post_id) . " responded with code " . $resp['response']['code']);
        }
        $html = $resp['body'];

        require_once GK_PLUGIN_PATH . '/libs/simple_html_dom/simple_html_dom.php';

        $html = str_replace("\r", '', $html);
        $doc = str_get_html($html, $lowercase=true, $forceTagsClosed=true, $target_charset=DEFAULT_TARGET_CHARSET, $stripRN=false, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT);

        $hentry_node = $doc->find('.hentry', 0);
        $post = $this->_parsePost($hentry_node);
        if ($post) {
            $post->has_comments = !empty($post->comments);
        }
        return $post;
    }

    /**
     * @param $hentry_node
     * @return GovnokodRuPostModel|null
     */
    protected function _parsePost($hentry_node) {
        if (!$hentry_node) {
            return null;
        }

        $url_node = $hentry_node->find('h2 .entry-title', 0);
        if (!$url_node) {
            return null;
        }

        $post = new GovnokodRuPostModel();
        $post->url = $url_node->href;
        $post->id = (int) str_replace($this->baseUrl . '/', '', $post->url);

        $language_node = $hentry_node->find('[rel=chapter]', 0);
        if (!$language_node) {
            return null;
        }
        $post->language = $this->languageDecoder->decode($language_node->innertext);

        $code_node = $hentry_node->find('.entry-content code', 0);
        if ($code_node) {
            $post->code = htmlspecialchars_decode($code_node->innertext);
        } else {
            $post->code = ''; // looks like it's PONY-post
        }

        $description_node = $hentry_node->find('.description', 0);
        if (!$description_node) {
            return null;
        }
        $post->description = $this->textDecoder->decode($description_node);

        $post->author = new GovnokodRuAuthorModel();

        $author_node = $hentry_node->find('.author a', 1);
        if (!$author_node) {
            return null;
        }
        $post->author->id = $this->_parseUserId($author_node);
        $post->author->name = $author_node->innertext;

        $avatar_node = $hentry_node->find('.author img', 0);
        if (!$avatar_node) {
            return null;
        }
        $post->author->avatar = $this->_parseGravatar($avatar_node);

        $date_node = $hentry_node->find('.author abbr', 0);
        if (!$date_node) {
            return null;
        }
        $post->date_gmt = $this->_parseDate($date_node);

        $comments_node = $hentry_node->find('.entry-comments', 0);
        if (!$comments_node) {
            return $post;
        }

        $this->_parseComments($post, $comments_node, 0);

        return $post;
    }

    private function _parseGravatar($img_node) {
        if (!preg_match('#avatar\/([^?]+)[?]#', $img_node->src, $m)) {
            return '';
        }
        return $m[1];
    }

    private function _parseUserId($a_node) {
        return (int) str_replace($this->baseUrl . '/user/', '', $a_node->href);
    }

    private function _parseDate($date_node) {
        return gmdate('Y-m-d H:i:s', strtotime($date_node->title));
    }

    /**
     * Parse single comment
     *
     * @param $node
     * @return GovnokodRuCommentModel|null
     */
    protected function _parseComment($node) {
        $comment = new GovnokodRuCommentModel();

        $comment_link_node = $node->find('.comment-link', 0);
        if (!$comment_link_node) {
            return null;
        }
        // example: http://govnokod.ru/23935#comment408704
        if (!preg_match('#/(\d+)\#comment(\d+)#', $comment_link_node->href, $m)) {
            return null;
        }
        $comment->post_id = (int) $m[1];
        $comment->id = (int) $m[2];

        $entry_comment_node = $node->find('.entry-comment', 0);
        if (!$entry_comment_node) {
            return null;
        }

        $text_node = $entry_comment_node->find('.comment-text', 0);
        if (!$text_node) {
            $text_node = $entry_comment_node;
        }
        $comment->text = $this->textDecoder->decode($text_node);

        $comment->author = new GovnokodRuAuthorModel();

        $avatar_node = $node->find('img.avatar', 0);
        if (!$avatar_node) {
            return null;
        }
        $comment->author->avatar = $this->_parseGravatar($avatar_node);

        $author_node = $node->find('.entry-author a', 0);
        if (!$author_node) {
            return null;
        }
        $comment->author->id = $this->_parseUserId($author_node);
        $comment->author->name = $author_node->innertext;

        $date_node = $node->find('abbr.published', 0);
        if (!$date_node) {
            return null;
        }
        $comment->date_gmt = $this->_parseDate($date_node);

        return $comment;
    }

    private function _parseComments($parent, $node, $parent_id) {
        if ($node->class === 'entry-comments') {

            foreach ($node->children() as $ul) {
                foreach ($ul->children() as $li) {
                    if ($li->class === 'hcomment') {
                        $this->_parseComments($parent, $li, $parent_id);
                    }
                }
            }
            return;
        }

        $comment_node = $node->find('> .entry-comment-wrapper', 0);
        if ($comment_node) {
                $comment = $this->_parseComment($comment_node);
            if ($comment) {
                $comment->parent_id = $parent_id;
                $parent->comments[] = $comment;

                foreach ($node->children() as $ul) {
                    foreach ($ul->children() as $li) {
                        if ($li->class === 'hcomment') {
                            $this->_parseComments($comment, $li, $comment->id);
                        }
                    }
                }
            }
        }

    }
}