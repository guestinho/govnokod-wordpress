<?php

$post_lines_limit = gk_get_option('gk_post_lines_limit');

$text = '';
$description = '';
$language_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = array();

    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'gk-action')) {
        $errors[] = 'CSRF проверка провалена. Обновите страницу.';
    }

    if (!current_user_can('edit_posts')) {
        $errors[] = 'У Вас нет прав на создание поста.';
    } else {
        if (empty($_REQUEST['language']) || !is_string($_REQUEST['language'])) {
            $errors[] = 'Укажите язык';
        } else {
            $language_id = (int)$_REQUEST['language'];
            $term = get_term($language_id);

            if (!$term) {
                $errors[] = 'Укажите язык';
            }
        }

        if (empty($_REQUEST['text']) || !is_string($_REQUEST['text'])) {
            $errors[] = 'Введите код';
        } else {
            $text = $_REQUEST['text'];
            $lines_count = max(1, substr_count($text, "\n"));
            if ($lines_count > $post_lines_limit) {
                $errors[] = sprintf('Количество строк не должно превышать %s. У Вас %s.', $post_lines_limit, $lines_count);
            }
        }

        $description = isset($_REQUEST['description']) && is_string($_REQUEST['description']) ? $_REQUEST['description'] : '';
    }

    if (empty($errors)) {
        $next_id = PostTitleController::getNextName();

        kses_remove_filters();

        $post_id = wp_insert_post(array(
            'post_content' => $text,
            'post_excerpt' => $description,
            'post_status' => 'publish',
            'post_name' => $next_id . '',
        ));

        if (is_wp_error($post_id)) {
            $errors[] = 'Произошла ошибка при создании поста: ' . $post_id->get_error_message();
        } else {
            $terms = wp_set_post_terms($post_id, array($language_id), 'language');

            if ($terms === false) {
                $errors[] = 'Произошла ошибка при создании поста.';
            } else if (is_wp_error($terms)) {
                $errors[] = 'Произошла ошибка при создании поста: ' . $terms->get_error_message();
            } else {
                wp_redirect(get_permalink($post_id));
            }
        }
    }
}

?>

<?php if (is_user_logged_in()): ?>
    <form method="post" action="">
        <?php wp_nonce_field('gk-action'); ?>

        <?php if (!empty($errors)): ?>
            <dl class="errors">
                <dt>Ошибка компиляции кода:</dt>
                <dd>
                    <ol>
                        <?php foreach ($errors as $error) echo "<li>$error</li>"; ?>
                    </ol>
                </dd>
            </dl>
        <?php endif; ?>

        <dl>
            <dt>
                <label for="formElm_category_id" id="formElm_category_id_label">Язык: <span class="required">*</span></label>
            </dt>
            <dd>
                <select class="lang" id="formElm_category_id" name="language">
                    <option value="">&nbsp;</option>
                    <?php foreach (gk_get_languages() as $term): ?>
                        <option value="<?php echo $term->term_id; ?>"<?php if ($language_id === $term->term_id) echo ' selected' ?>><?php echo $term->name; ?></option>
                    <?php endforeach; ?>
                </select>
            </dd>

            <dt>
                <label for="formElm_text" id="formElm_text_label">Код (максимум строк: <?php echo $post_lines_limit; ?>): <span class="required">*</span></label>
            </dt>
            <dd>
                <textarea class="code" cols="50" id="formElm_text" name="text" rows="10"><?php echo esc_html(stripslashes($text)); ?></textarea>
            </dd>

            <dt>
                <label for="formElm_description" id="formElm_description_label">Описание:</label>
            </dt>
            <dd>
                <textarea cols="50" id="formElm_description" name="description" rows="6"><?php echo esc_html(stripslashes($description)); ?></textarea>
            </dd>
        </dl>

        <p>
            <input class="send" id="formElm_submit" name="submit" type="submit" value="Накласть">
        </p>
    </form>
<?php else: ?>
    <h2>Говнокодить могут только авторизованные пользователи</h2>

    Авторизуйтесь.
<?php endif; ?>