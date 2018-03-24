<?php

require_once dirname(__FILE__) . '/govnokod-ru-model.php';
require_once dirname(__FILE__) . '/class-govnokod-ru-language-decoder.php';
require_once dirname(__FILE__) . '/class-govnokod-ru-text-decoder.php';

interface IGovnokodDataSource {
    /**
     * @param int|string $post_id
     * @return GovnokodRuPostModel|WP_Error|null
     */
    public function loadPost($post_id);
}