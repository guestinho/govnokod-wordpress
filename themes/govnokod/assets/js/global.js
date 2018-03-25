window.$ = jQuery;

jQuery(function ($) {
	highlight($('body'));

	function highlight(dom) {
		if (window.hljs && hljs.highlightBlock) {
			dom.find('pre code:not(.hljs)').each(function (i, block) {
				hljs.highlightBlock(block);
			});
		}
	}

	$('#expand-trigger').click(function() {
		$('#userpane').toggleClass('expanded');
		return false;
	});

	[
		{
			className: 'comment-vote',
			type: 'comment'
		},
		{
			className: 'vote',
			type: 'govno'
		}
	].forEach(function (item) {
		$('body').on('click', '.' + item.className + ' a', function (event) {
			event.preventDefault();

			var wrapper = $(event.currentTarget).parents('.' + item.className);
			var prevHtml = wrapper[0].outerHTML;

			wrapper.html('<img src="' + TEMPLATE_PATH + '/assets/images/' + item.type + 'vote.gif" class="preloader" alt="Загрузка" title="Идёт учет голоса…">');

			var href = event.currentTarget.href;
			$.get(href, function (resp) {
				var html = prevHtml;
				try {
					resp = JSON.parse(resp);

					if (resp.status === 'success') {
						html = resp.html;
					}
				} catch (e) {
				}
				wrapper.replaceWith(html);
			});
		});
	});

	$('body').on('click', 'a.entry-comments-load', function (event) {
		event.preventDefault();

		var wrapper = $(event.currentTarget).parents('.entry-comments');
		var html = wrapper[0].outerHTML;
		wrapper.append('<img src="' + TEMPLATE_PATH + '/assets/images/commentsload.gif" alt="Загрузка" title="Загрузка списка комментариев…">');

		var href = event.currentTarget.href;
		$.get(href, function (resp) {
			try {
				resp = JSON.parse(resp);

				if (resp.status === 'success') {
					html = resp.html;
				}
			} catch (e) {
			}
			var dom = $(html);
			wrapper.replaceWith(dom);
			highlight(dom);
		});
	});

	$('body').on('click', '#respond #submit', function (event) {
		event.preventDefault();

		var $form = $(event.currentTarget).parents('form');
		var data = $form.serialize();

		$('#comment, #submit').attr('disabled', '');
		$form.find('.errors ol li').remove();

		$.post($form.attr('action'), data, function (resp) {
			var $comment;
			var error;
			try {
				resp = JSON.parse(resp);
				if (resp.status === 'success') {
					$comment = $(resp.html);
				} else if (resp.message) {
					error = resp.message;
				}
			} catch (e) {
			}

			if ($comment) {
				var $ul = $('#respond').siblings('.children, .comment-list');
				if (!$ul.length) {
					$ul = $('<ul class="children">');
					jQuery('#respond').after($ul);
				}

				$ul.append($comment);
				highlight($comment);
				$('#comment').val('');
				$('#cancel-comment-reply-link').click();
			} else {
				error = error || 'Failed to send a request';
				$form.find('.errors ol').append('<li>' + error + '</li>');
			}

			$('#comment, #submit').removeAttr('disabled');
		});
	});

	$('body').on('keydown', '#comment', function (event) {
		if (event.ctrlKey && event.keyCode == 13) {
			$('#submit').click();
		}
	});

	var $commentsDom = $('.all-comments');
	if ($commentsDom.length) {
		function update(position, callback) { // position: before or after
			var url = $commentsDom.attr('data-update-url');
			if (!url) {
				return;
			}

			function error(msg) {
				msg = msg ? msg + '<br>' : '';
				$('#update-comments-error').remove();
				$('#comments-notice').append('<span id="update-comments-error" class="error">' + msg + 'Error occurred while updating this page. Please refresh page.</span>');
			}
			function success(html) {
				$('#update-comments-error').remove();
				var $newComments = $(html).find('.hentry');
				if (position === 'before') {
					$commentsDom.prepend($newComments);
				} else if (position === 'after') {
					$commentsDom.find('.hentry:last').after($newComments);
				}
				highlight($newComments);
			}
			var requestParams = {};
			if (position === 'before') {
				requestParams.after = $('.all-comments .hentry:first .entry-info [datetime]').attr('datetime');
			} else if (position === 'after') {
				requestParams.before = $('.all-comments .hentry:last .entry-info [datetime]').attr('datetime');
			}

			$.getJSON(url, requestParams, function(data) {
				if (data && data.status === 'success' && data.html) {
					success(data.html);
				} else {
					error(data && data.message);
				}
			}).always(function () {
				if (position === 'before') {
					setTimeout(update, 1000 * parseInt($commentsDom.attr('data-update-interval') || 30), position);
				}
				callback && callback();
			}).fail(function () {
				error();
			});
		}
		setTimeout(update, 1000 * parseInt($commentsDom.attr('data-update-delay') || 30), 'before');
		
		$('body').on('click', '#more-comments button', function (event) {
			event.currentTarget.setAttribute('disabled', '');
			$('#more-comments').addClass('loading');
			update('after', function () {
				event.currentTarget.removeAttribute('disabled');
				$('#more-comments').removeClass('loading');
			});
		});
	}

});

jQuery('body').on('click', '.all-comments .add-ignore a', function (event) {
	event.preventDefault();
	var href = event.currentTarget.href;
	$.get(href);
	var username = $(event.currentTarget).closest('.entry-info').find('.comment-author').text();
	var removeCommenrs = $('.all-comments .comment-author').filter(function (i, el) {
		return $(el).text() === username;
	}).parents('.hentry');

	removeCommenrs.fadeOut(400, function () {
		removeCommenrs.remove();
	});
});