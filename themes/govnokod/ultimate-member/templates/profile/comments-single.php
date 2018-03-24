<?php
gk_enqueue_highlight_js_assets();

foreach($ultimatemember->shortcodes->loop as $comment) {
    $GLOBALS['comment'] = $comment;
    get_template_part('template-parts/comment/single');
}
?>

<?php if (isset($ultimatemember->shortcodes->modified_args) && count($ultimatemember->shortcodes->loop) >= 10) { ?>
    <div class="um-load-items">
        <a href="#" class="um-ajax-paginate um-button" data-hook="um_load_comments" data-args="<?php echo $ultimatemember->shortcodes->modified_args; ?>"><?php _e('load more comments','ultimate-member'); ?></a>
    </div>
<?php } ?>