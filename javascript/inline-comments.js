
(function ($) {
	var methods = {
		init: function (options) {
			var commentForm = $(options.form);
			if (!commentForm.length) {
				return;
			}

			var closeButton = $('<input type="button" value="Close" class="action" />').appendTo(commentForm.find('.Actions'));
			closeButton.click(function () {
				commentForm.hide();
			});

			commentForm.ajaxForm(function (data) {
				if (data) {
					var result = $.parseJSON(data);
					if (result && result.status) {
						methods.loadComment(result.message.comment, commentForm);
					} else {
						alert("There was an error saving your comment: " + result.message);
					}
					commentForm.hide();
				}
			});

			if (options.load) {
				$(options.load).each(function () {
					methods.loadComment(this, commentForm);
				})
			}

			return this.each(function(){
				$this = $(this);
				
				var id = $this.attr('id');
				
				// only want to be able to comment on uniquely identifiable items
				if (id.length) {
					var commentButton = $(' <img src="inlinecomments/images/comments.png"/>').appendTo($this);

					$this.hover(function () {
						$('.inlineCommentContainer').fadeTo(50, 0.1);
						$('#' + id + '_Comments').fadeTo(50, 1);
					}, function () {
						// hide my comments slowly
					});

					commentButton.click(function () {
						commentForm.show();
						commentForm.find('textarea[name=Comment]').val('');
						commentForm.find('input[name=CommentOnElement]').val(id);
						commentForm.position({ my: "left top", at: "right bottom", of: commentButton, collision: "fit"});
					})
				}
			});
		},

		loadComment: function (comment, commentForm) {
			var commentTemplate = $('#InlineCommentTemplate');
			if (!commentTemplate.length) {
				commentTemplate = this.createCommentTemplate();
			}

			var containerId = '#' + comment.CommentOnElement + '_Comments';
			var container = $(containerId);

			if (!container.length) {
				container = this.createCommentContainer(comment.CommentOnElement);
			}

			var commentBlock = commentTemplate.clone();
			commentBlock.removeAttr('id');
			if (comment.member) {
				commentBlock.find('.inlineCommentTitle').text(comment.member.Fullname + ' ' + comment.Created);
			} else {
				commentBlock.find('.inlineCommentTitle').text(comment.Created);
			}
			commentBlock.find('.inlineCommentText').html(comment.Comment);
			commentBlock.find('.commentDelete').click(function () {
				if (confirm("Are you sure?")) {
					$.post(commentForm.attr('action'), {
							SecurityID: $('input[name=SecurityID]').val(),
							Comment: ' needed because required field...!!',
							action_deleteinlinecomment: 'Delete',
							ID: comment.ID
						}, function (data) {
						if (data) {
							var result = $.parseJSON(data);
							if (result && result.status) {
								commentBlock.fadeOut('fast', function () { commentBlock.remove() });
							}
						}
					})
				}
			}).css('cursor', 'pointer');
			
			commentBlock.prependTo(container);
		},

		createCommentContainer: function (id) {
			var container = $('<div id="'+id+'_Comments" class="inlineCommentContainer"></div>');
			var offset = $('#'+id).offset();
			$('body').append(container);
			container.css('top', offset.top + 'px');
			container.show();

			container.hover(function () {
				$('.inlineCommentContainer').fadeTo(50, 0.1);
				$(this).fadeTo(50, 1);
			}, function () {
				
			})

			return container;
		},

		createCommentTemplate: function () {
			return $('<div id="InlineCommentTemplate" class="inlineComment"> ' +
						'<div><span class="inlineCommentTitle"></span> (<span class="commentDelete">X</span>)</div><div class="inlineCommentText"></div>' +
					'</div>');
		}
	}

	$.fn.inlineComment = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || ! method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' +  method + ' does not exist on jQuery.tooltip');
		}
	}

})(jQuery);