<?php
/* 
 *  @license http://silverstripe.org/bsd-license/
 */

/**
 * A piece of content that represents a content someone has tagged onto a section of a page
 *
 * @author marcus@silverstripe.com.au
 */
class InlineComment extends DataObject {
    public static $db = array(
		'Comment' => 'HTMLText',
		'CommentOnElement' => 'Varchar(255)',
		'TargetType' => 'Varchar(64)',
		'TargetID' => 'Int',
	);

	public static $has_one = array(
		'Author' => 'Member',
	);

	public function toMap() {
		$map = parent::toMap();
		if ($this->AuthorID) {
			$map['member'] = array(
				'FirstName' => $this->Author()->FirstName,
				'Surname' => $this->Author()->Surname,
				'Fullname' => $this->Author()->getTitle(),
				'Email' => $this->Author()->Email,
			);
		}

		return $map;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();

		$this->Comment = nl2br($this->Comment);
		include_once INLINE_COMMENTS_DIR . '/thirdparty/htmlpurifier-4.0.0-lite/library/HTMLPurifier.auto.php';
		$purifier = new HTMLPurifier();
		$content = $purifier->purify($this->Comment);
		$this->Comment = preg_replace_callback('/\%5B(.*?)\%5D/', array($this, 'reformatShortcodes'), $content);
		
	}

	/**
	 * Reformats shortcodes after being run through htmlpurifier
	 *
	 * @param array $matches
	 */
	public function reformatShortcodes($matches) {
		$val = urldecode($matches[1]);
		return '['.$val.']';
	}
}