<?php
/**
 * [PUBLISH] ブログコメント登録完了
 * 
 * Ajaxで呼び出される
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright (c) baserCMS Users Community <http://basercms.net/community/>
 *
 * @copyright		Copyright (c) baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			Blog.View
 * @since			baserCMS v 0.1.0
 * @license			http://basercms.net/license/index.html
 */


if ($dbData) {
	$this->BcBaser->element('blog_comment', array('dbData' => $dbData));
}
