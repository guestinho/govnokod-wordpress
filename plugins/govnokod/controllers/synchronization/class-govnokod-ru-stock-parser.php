<?php

require_once dirname(__FILE__) . '/class-govnokod-ru-data-source.php';

class GovnokodRuStockParser extends GovnokodRuDataSource {

    /**
     * @var string target site url
     */
    public $baseUrl = GK_LEGACY_SITE_URL . '/comments';

    /**
     * @return GovnokodRuCommentModel[]|WP_Error
     */
    public function loadComments() {
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

        $comments = array();

        foreach ($doc->find('.entry-comment-wrapper') as $hentry_node) {
            $comment = $this->_parseComment($hentry_node);
            if ($comment) {
                $comments[] = $comment;
            }
        }

        return $comments;
    }
}