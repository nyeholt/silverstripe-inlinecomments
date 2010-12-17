<?php

/**
 *
 * @author marcus@silverstripe.com.au
 * @license http://silverstripe.org/bsd-license/
 */
class InlineCommentControllerExtension extends Extension {
	public static $allowed_actions = array(
		'InlineCommentForm',
	);
	
	public function InlineCommentForm($element) {
		if (!$element) {
			return;
		}
		if (Member::currentUserID()) {
			return new InlineCommentForm($this->owner, 'InlineCommentForm', $this->owner->data(), $element);
		}
	}
}