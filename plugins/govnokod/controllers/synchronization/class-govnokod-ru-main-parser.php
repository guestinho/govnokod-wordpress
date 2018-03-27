<?php

require_once dirname(__FILE__) . '/class-govnokod-ru-data-source.php';

class GovnokodRuMainParser extends GovnokodRuDataSource {

    /**
     * @return GovnokodRuPostModel[]|WP_Error
     */
    public function loadPosts() {
        $resp = wp_remote_get($this->baseUrl);
        if (is_wp_error($resp)) {
            return $resp;
        }

        if ($resp['response']['code'] !== 200) {
            return new WP_Error('bad_response', $this->baseUrl . " responded with code " . $resp['response']['code']);
        }
        $html = $resp['body'];

        require_once GK_PLUGIN_PATH . '/libs/simple_html_dom/simple_html_dom.php';

        $html = str_replace("\r", '', $html);
        $doc = str_get_html($html, $lowercase=true, $forceTagsClosed=true, $target_charset=DEFAULT_TARGET_CHARSET, $stripRN=false, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT);

        $result = array();
        foreach ($doc->find('.hentry') as $hentry_node) {
            $post = $this->_parsePost($hentry_node);
            if ($post) {
                $comments_count_node = $hentry_node->find('.entry-comments-count', 0);
                $post->has_comments = $comments_count_node && $comments_count_node->innertext !== '(0)';
                $result[] = $post;
            }
        }
        return $result;
    }
}