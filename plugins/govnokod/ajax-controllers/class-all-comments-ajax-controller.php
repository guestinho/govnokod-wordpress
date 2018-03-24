<?php

class AllCommentsAjaxController extends AjaxControllerBase {

    public function main() {
        ob_start();
        get_template_part('template-parts/page/comments');
        $html_result = ob_get_clean();

        return $this->html($html_result);
    }

    public function noprivActionFunc() {
        $this->actionFunc();
    }
}

AjaxControllerBase::add('all-comments', new AllCommentsAjaxController());