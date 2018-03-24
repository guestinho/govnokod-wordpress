<h2>Поиск говнокода</h2>
<p>Этот поиск практически ничего не может найти! Но вы всё-таки попытайтесь, вдруг повезет.</p>
<form role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
	<dl><dt><label for="formElm_search" id="formElm_search_label">Поисковая строка:</label></dt>
		<dd><input id="formElm_search" maxlength="50" name="s" type="text" value="<?php the_search_query(); ?>"></dd>
		<dt><label for="formElm_language" id="formElm_language_label">В языке:</label></dt>
		<dd>
			<select id="formElm_language" name="language">
				<option style="font-weight: bold;" value="">Во всех</option>
				<?php foreach (gk_get_languages() as $term): ?>
					<option
						<?php if (get_query_var('language') === $term->slug) echo 'selected="selected"'; ?>
						value="<?php echo $term->slug; ?>"
					>
						<?php echo $term->name; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</dd>
	</dl>

	<p><input type="submit" class="send" value="Покопаться!"></p>
</form>