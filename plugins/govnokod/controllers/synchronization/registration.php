<?php

function gk_get_registration_token() {
    $nonce = wp_create_nonce('um_register_form');
    $salt = wp_salt();
    $ip = $_SERVER['REMOTE_ADDR'];
    return md5($nonce . $ip . $salt);
}

function gk_um_submit_form_errors_hook__blockedwords($args) {
    global $ultimatemember, $gk_sync_users;

    $fields = unserialize($args['custom_fields']);

    if (isset($fields) && !empty($fields) && is_array($fields)) {
        foreach ($fields as $key => $array) {
            if (isset($array['validate']) && in_array($array['validate'], array('unique_username'))) {
                if (!$ultimatemember->form->has_error($key) && isset($args[$key])) {

                    $username = $args[$key];

                    if (strtolower($username) === 'guest') {
                        $ultimatemember->form->add_error($key, 'Хорошая попытка, засранец! Это имя занято.');
                        continue;
                    }

                    if (!$gk_sync_users->isUserExists($username)) {
                        continue;
                    }

                    $comment_url = trim($args['legacy_token_url']);
                    if ($comment_url) {
                        if (strpos($comment_url, GK_LEGACY_SITE_URL) !== 0) {
                            $ultimatemember->form->add_error('legacy_token_url', 'Некорректная ссылка');
                            continue;
                        }
                        $comment_url = substr($comment_url, strlen(GK_LEGACY_SITE_URL));
                        if (!preg_match('#\/(\d+)\#comment(\d+)#', $comment_url, $m)) {
                            $ultimatemember->form->add_error('legacy_token_url', 'Некорректная ссылка');
                            continue;
                        }
                        $post_id = (int) $m[1];
                        $comment_id = (int) $m[2];

                        for ($try = 0; $try < 2; $try++) {
                            $post_name = GK_LEGACY_POST_NAME_PREFIX . $post_id;
                            $post = get_page_by_path($post_name, OBJECT, 'post');

                            $comments = get_comments(array(
                                'post_id' => $post->ID,
                                // нет возможности поискать по агенту, пока не буду писать свой запрос
                            ));
                            foreach ($comments as $c) {
                                if ((int)$c->comment_agent === $comment_id) {
                                    $comment = $c;
                                    break;
                                }
                            }
                            if (isset($comment) || $try > 0) {
                                break;
                            }
                            global $gk_sync;
                            $gk_sync->syncPost($post_id);
                        }

                        if (empty($comment)) {
                            $ultimatemember->form->add_error('legacy_token_url', 'Не удалось синхронизировать комментарий с govnokod.ru. Проверьте правильность ссылки, и повторите попытку через минуту.');
                            continue;
                        }
                        if ($comment->comment_author !== $username) {
                            $ultimatemember->form->add_error('legacy_token_url', sprintf('Вы должны были написать комментарий от имени <strong>%s</strong>.', esc_html($username)));
                            continue;
                        }
                        if (trim($comment->comment_content) !== gk_get_registration_token()) {
                            $ultimatemember->form->add_error('legacy_token_url', 'Содержание этого комментария не соответствует требуемому формату. Возможно, истек срок валидности токена. Попробуйте обновить страницу и повторить попытку ещё раз.');
                            continue;
                        }
                    } else {
                        $ultimatemember->form->add_error($key, 'Это имя зарезервировано. Попробуйте опцию "Привязать аккаунт govnokod.ru"');
                    }
                }
            }
        }
    }
}
add_action('um_submit_form_errors_hook__blockedwords', 'gk_um_submit_form_errors_hook__blockedwords', 11);