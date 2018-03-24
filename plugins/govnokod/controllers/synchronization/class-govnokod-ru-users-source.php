<?php

require_once dirname(__FILE__) . '/interface-govnokod-users-source.php';

class GovnokodRuUsersSource implements IGovnokodUsersSource {

    /**
     * @var string target site url
     */
    public $baseUrl = GK_LEGACY_SITE_URL . '/user/';

    private function _userUrl($user_id) {
        return $this->baseUrl . $user_id;
    }

    public function loadUser($user_id) {
        $resp = wp_remote_get($this->_userUrl($user_id), array('timeout' => 5));
        if (is_wp_error($resp)) {
            $resp->add('url', $this->_userUrl($user_id));
            return $resp;
        }

        if ($resp['response']['code'] !== 200) {
            return new WP_Error('bad_response', $this->_userUrl($user_id) . " responded with code " . $resp['response']['code']);
        }
        $html = $resp['body'];

        require_once GK_PLUGIN_PATH . '/libs/simple_html_dom/simple_html_dom.php';

        $html = str_replace("\r", '', $html);
        $doc = str_get_html($html, $lowercase=true, $forceTagsClosed=true, $target_charset=DEFAULT_TARGET_CHARSET, $stripRN=false, $defaultBRText=DEFAULT_BR_TEXT, $defaultSpanText=DEFAULT_SPAN_TEXT);

        $hentry_node = $doc->find('.hentry', 0);
        if (!$hentry_node) {
            return null;
        }

        $name_node = $hentry_node->find('h2 strong', 0);
        if (!$name_node) {
            return null;
        }
        $result = new GovnokodRuAuthorModel();
        $result->name = $name_node->innertext;

        $img_node = $hentry_node->find('img', 0);
        if (!$img_node) {
            return null;
        }
        $result->avatar = $this->_parseGravatar($img_node);
        $result->id = (int) $user_id;
        return $result;
    }

    private function _parseGravatar($img_node) {
        if (!preg_match('#avatar\/([^?]+)[?]#', $img_node->src, $m)) {
            return '';
        }
        return $m[1];
    }
}