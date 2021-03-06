<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) baserCMS Users Community <https://basercms.net/community/>
 *
 * @copyright       Copyright (c) baserCMS Users Community
 * @link            https://basercms.net baserCMS Project
 * @package         Blog.Test.Case
 * @since           baserCMS v 4.0.9
 * @license         https://basercms.net/license/index.html
 */

class BlogAllControllerTest extends CakeTestSuite
{

	/**
	 * Suite define the tests for this suite
	 *
	 * @return CakeTestSuite
	 */
	public static function suite()
	{
		$suite = new CakeTestSuite('All Controller tests');
		$suite->addTestDirectory(dirname(__FILE__) . DS . 'Controller' . DS);
		return $suite;
	}

}
