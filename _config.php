<?php

if (basename(dirname(__FILE__)) != 'inlinecomments') {
	throw new Exception("Module inlinecomments is not installed in correct location");
}

define('INLINE_COMMENTS_DIR', dirname(__FILE__));