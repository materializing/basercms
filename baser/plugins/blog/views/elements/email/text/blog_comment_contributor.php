<?php
/* SVN FILE: $Id$ */
/**
 * [EMAIL] メール送信
 * 
 * PHP versions 5
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2012, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2012, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			baser.plugins.blog.views
 * @since			baserCMS v 0.1.0
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://basercms.net/license/index.html
 */
?>

                                           <?php echo date('Y-m-d H:i:s') ?> 
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
　　　　　　　　◆◇　コメントが投稿されました　◇◆ 
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

<?php echo $blogComment['name'] ?>さんが、
「<?php echo $blogPost['name'] ?>」にコメントしました。
<?php echo $bcBaser->getUri('/' . $blogContent['name'] . '/archives/' . $blogPost['no'], false) ?>　
 
<?php echo ($blogComment['message']) ?>　
　
　

