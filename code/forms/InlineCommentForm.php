<?php
/* 
 *  @license http://silverstripe.org/bsd-license/
 */

/**
 * A simple form used for adding comments.
 *
 * Clientside JS takes care of adding all the snazzy functionality
 *
 * @author marcus@silverstripe.com.au
 */
class InlineCommentForm extends Form {

	/**
	 * @var DataObject
	 */
	protected $context;

	/**
	 *
	 * @param ContentController $controller
	 *				The controller of the page we're attached to
	 * @param DataObject $context
	 *				The object we're being bound against
	 * @param String $commentOn
	 *				The jQuery string we're going to match to show 'comment' icons. 
	 */
	public function  __construct($controller, $name, $context, $commentOn='h1') {
		$this->context = $context;

		if (!$this->context->ID) {
			throw new Exception("Invalid context object for inline commenting form");
		}

		$fields = new FieldSet();
		$fields->push(new TextareaField('Comment', _t('InlineComment.COMMENT', 'Add Comment')));
		$fields->push(new HiddenField('CommentOnElement'));
		
		$fields->push(new HiddenField('TargetType', '', $this->context->ClassName));
		$fields->push(new HiddenField('TargetID', '', $this->context->ID));


		$actions = new FieldSet(
			new FormAction('add', _t('InlineComment.ADD', 'Add')),
			new FormAction('deleteinlinecomment', _t('InlineComment.DELETE', 'Delete'))
		);

		$this->addExtraClass('inlineCommentForm');

		parent::__construct($controller, $name, $fields, $actions, new RequiredFields('Comment'));

		Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui-1.8rc3.custom.js');
		Requirements::javascript('inlinecomments/javascript/inline-comments.js');
		Requirements::themedCSS('inline-comments');

		

		// load the existing items for the given context
		$filter = singleton('ICUtils')->dbQuote(array(
			'TargetType =' => $this->context->ClassName,
			'TargetID =' => $this->context->ID,
		));
		$comments = DataObject::get('InlineComment', $filter);
		$toLoad = array();
		if ($comments) {
			foreach ($comments as $comment) {
				$toLoad[] = $comment->toMap();
			}
		}

		$opts = array(
			'form' => '#' . $this->FormName(),
			'load' => $toLoad,
		);

		Requirements::customScript('jQuery("'.$commentOn.'").inlineComment(' . Convert::array2json($opts) . ')');
	}

	public function add(array $data, Form $form, $request) {
		
		if (!$this->context->canEdit()) {
			return;
		}

		$comment = new InlineComment();
		$form->saveInto($comment);
		$comment->AuthorID = Member::currentUserID();
		
		$comment->write();

		$res = array('comment' => $comment->toMap());
		return singleton('ICUtils')->ajaxResponse($res, true);
	}

	public function update(array $data, Form $form, $request) {
		if (!$this->context->canEdit()) {
			return;
		}
	}

	public function deleteinlinecomment(array $data, Form $form, $request) {
		if (!$this->context->canEdit()) {
			return;
		}

		if (!isset($data['ID'])) {
			throw new Exception("Invalid comment ID");
		}

		$comment = DataObject::get_by_id('InlineComment', $data['ID']);
		$comment->delete();

		return singleton('ICUtils')->ajaxResponse("Deleted", true);
	}

	public function listcomments($data, $request) {
		$on = isset($data['element']) ? $data['element'] : null;
		$type = isset($data['type']) ? $data['type'] : null;
		$id = isset($data['id']) ? $data['id'] : null;
		if (!$on || !$id || !$type) {
			return singleton('ICUtils')->ajaxResponse("No comments found", false);
		}

		$filter = singleton('ICUtils')->dbQuote(array(
			'CommentOnElement =' => $on,
			'TargetType =' => $type,
			'TargetID =' => $id,
		));

		$comments = DataObject::get('InlineComment', $filter);

		return singleton('ICUtils')->ajaxResponse($res, true);
	}
}
