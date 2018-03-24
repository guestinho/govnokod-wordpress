<div class="um <?php echo $this->get_class( $mode ); ?> um-<?php echo $form_id; ?>">
	<div class="um-form">
		<form method="post" action="">
		<?php
			do_action("um_before_form", $args);
			do_action("um_before_{$mode}_fields", $args);
			ob_start();
			do_action("um_main_{$mode}_fields", $args);
			echo str_replace('{legacy_token}', gk_get_registration_token(), ob_get_clean());
			do_action("um_after_form_fields", $args);
			do_action("um_after_{$mode}_fields", $args);
			do_action("um_after_form", $args);
		?>
		</form>
	</div>
</div>