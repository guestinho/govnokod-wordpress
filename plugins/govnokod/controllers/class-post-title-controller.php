<?php

class PostTitleController {

    public static function getNextName() {

        // Берем посты в порядке убывания ID, пока name - это не число
        // если нашни число, то следуюшиее - наш новый name
        // в штатном режиме цикл выполнит 1 итерацию

        for ($offset = 0; ; $offset++) {
            $latest = get_posts(array(
                'posts_per_page' => 1,
                'offset' => $offset,
                'post_type' => 'post',
                'orderby' => 'ID',
                'order' => 'DESC',
            ));

            if (count($latest) === 0) {
                break;
            }

            foreach ($latest as $post) {
                if (preg_match('#^' . GK_POST_NAME_PREFIX . '([0-9]+)#', $post->post_name, $m)) {
                    return GK_POST_NAME_PREFIX . ((int) $m[1] + 1);
                }
            }
        }
        return GK_POST_NAME_PREFIX . '1';
    }
}