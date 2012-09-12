<?php
/* SVN FILE: $Id$ */
/**
 * BcManagerコンポーネント
 *
 * PHP versions 5
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2012, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2012, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			baser.components
 * @since			baserCMS v 0.1.0
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://basercms.net/license/index.html
 */
class BcManagerComponent extends Object {
/**
 * baserCMSのインストール
 * 
 * @param type $dbConfig
 * @param type $adminUser
 * @param type $adminPassword
 * @param type $adminEmail
 * @return boolean 
 */
	function install($siteUrl, $dbConfig, $adminUser = array(), $smartUrl = false, $baseUrl = '', $dbDataPattern = 'core.demo') {
		
		$result = true;
		
		// キャッシュ削除
		clearAllCache();
		
		// 一時フォルダ作成
		checkTmpFolders();
		
		if($dbConfig['driver'] == 'sqlite3' || $dbConfig['driver'] == 'csv') {
			switch($dbConfig['driver']) {
				case 'sqlite3':
					$dbFolderPath = APP.'db'.DS.'sqlite';
					break;
				case 'csv':
					$dbFolderPath = APP.'db'.DS.'csv';
					break;
				
			}
			$Folder = new Folder();
			if(!is_writable($dbFolderPath) && !$Folder->create($dbFolderPath, 00777)){
				$this->log('データベースの保存フォルダの作成に失敗しました。db フォルダの書き込み権限を見なおしてください。');
				$result = false;
			}
		}
		
		// SecritySaltの設定
		$securitySalt = $this->setSecuritySalt();

		// インストールファイル作成
		if(!$this->createInstallFile($securitySalt, $siteUrl)) {
			$this->log('インストールファイル生成に失敗しました。設定フォルダの書き込み権限を見なおしてください。');
			$result = false;
		}
		
		// データベース設定ファイル生成
		if(!$this->createDatabaseConfig($dbConfig)) {
			$this->log('データベースの設定ファイル生成に失敗しました。設定フォルダの書き込み権限を見なおしてください。');
			$result = false;
		}
		
		// データベース初期化
		if(!$this->constructionDb($dbConfig, $dbDataPattern)) {
			$this->log('データベースの初期化に失敗しました。データベースの設定を見なおしてください。');
			$result = false;
		}
		
		if($adminUser) {
			// サイト基本設定登録
			if(!$this->setAdminEmail($adminUser['email'])) {
				$this->log('サイト基本設定への管理者メールアドレスの設定処理が失敗しました。データベースの設定を見なおしてください。');
			}
			// ユーザー登録
			$adminUser['password_1'] = $adminUser['password'];
			$adminUser['password_2'] = $adminUser['password'];
			if(!$this->addDefaultUser($adminUser)) {
				$this->log('初期ユーザーの作成に失敗しました。データベースの設定を見なおしてください。');
				$result = false;
			}
		}
		
		// データベースの初期更新
		if(!$this->executeDefaultUpdates($dbConfig)) {
			$this->log('データベースのデータ更新に失敗しました。データベースの設定を見なおしてください。');
			return false;
		}
		
		// テーマを配置
		if(!$this->deployTheme()) {
			$this->log('テーマの配置に失敗しました。テーマフォルダの書き込み権限を確認してください。');
			$result = false;
		}
		
		if($smartUrl) {
			if(!$this->setSmartUrl(true, $baseUrl)) {
				$this->log('スマートURLの設定に失敗しました。.htaccessの書き込み権限を確認してください。');
			}
		}

		// ページファイルを生成
		$this->createPageTemplates();
		
		return $result;
		
	}
/**
 * データベースに接続する
 *
 * @param array $config
 * @return DboSource $db
 * @access public
 */
	function &connectDb($config, $name = 'baser') {

		if($name == 'plugin') {
			$config['prefix'].=Configure::read('BcEnv.pluginDbPrefix');
		}
		
		$result =  ConnectionManager::create($name ,array(
				'driver' => $config['driver'],
				'persistent' => false,
				'host' => $config['host'],
				'port' => $config['port'],
				'login' => $config['login'],
				'password' => $config['password'],
				'database' => $config['database'],
				'schema' => $config['schema'],
				'prefix' =>  $config['prefix'],
				'encoding' => $config['encoding']));

		if($result) {
			return $result;
		} else {
			return ConnectionManager::getDataSource($name);
		}

	}
/**
 * 実際の設定用のDB名を取得する
 *
 * @param string $type
 * @param string $name
 * @return string
 * @access	public
 */
	function getRealDbName($type, $name) {

		if(preg_match('/^\//', $name)) {
			return $name;
		}
		/* dbName */
		if(!empty($type) && !empty($name)) {
			$type = preg_replace('/^bc_/', '', $type);
			if($type == 'sqlite3') {
				return APP.'db'.DS.'sqlite'.DS.$name.'.db';
			}elseif($type == 'csv') {
				return APP.'db'.DS.'csv'.DS.$name;
			}
		}

		return $name;

	}
/**
 * テーマ用のページファイルを生成する
 *
 * @access	protected
 */
	function createPageTemplates() {

		App::import('Model','Page');
		$Page = new Page(null, null, 'baser');
		$pages = $Page->find('all', array('recursive' => -1));
		if($pages) {
			foreach($pages as $page) {
				$Page->data = $page;
				$Page->afterSave(true);
			}
		}
		ClassRegistry::removeObject('View');
		return true;
		
	}
/**
 * データベースのデータに初期更新を行う
 */
	function executeDefaultUpdates($dbConfig) {
		
		$result = true;
		if(!$this->_updateBlogEntryDate($dbConfig)) {
			$this->log('ブログ記事の投稿日更新に失敗しました。');
			$result = false;
		}
		if(!$this->_updatePluginStatus()) {
			$this->log('プラグインの有効化に失敗しました。');
			$result = false;
		}
		return $result;
		
	}
/**
 * プラグインのステータスを更新する
 *
 * @return boolean
 * @access	protected
 */
	function _updatePluginStatus() {

		$version = getVersion();
		App::import('Model', 'Plugin');
		$Plugin = new Plugin();
		$datas = $Plugin->find('all', array('recursive' => -1));
		if($datas){
			$result = true;
			foreach($datas as $data) {
				$data['Plugin']['version'] = $version;
				$data['Plugin']['status'] = true;
				if(!$Plugin->save($data)) {
					$result = false;
				}
			}
			return $result;
		} else {
			return false;
		}

	}
/**
 * 登録日を更新する
 *
 * @return boolean
 * @access	protected
 */
	function _updateBlogEntryDate($dbConfig) {

		$this->connectDb($dbConfig, 'plugin');
		App::import('Model', 'Blog.BlogPost');
		$BlogPost = new BlogPost();
		$BlogPost->contentSaving = false;
		$datas = $BlogPost->find('all', array('recursive' => -1));
		if($datas) {
			$ret = true;
			foreach($datas as $data) {
				$data['BlogPost']['posts_date'] = date('Y-m-d H:i:s');
				$BlogPost->set($data);
				if(!$BlogPost->save($data)) {
					$ret = false;
				}
			}
			return $ret;
		} else {
			return false;
		}

	}
/**
 * サイト基本設定に管理用メールアドレスを登録する
 * 
 * @param string $email
 * @return boolean
 * @access public 
 */
	function setAdminEmail($email) {
		
		App::import('Model','SiteConfig');
		$data['SiteConfig']['email'] = $email;
		$SiteConfig = new SiteConfig();
		return $SiteConfig->saveKeyValue($data);
		
	}
/**
 * 初期ユーザーを登録する
 * 
 * @param array $user
 * @return boolean 
 */
	function addDefaultUser($user, $securitySalt = '') {
		
		if($securitySalt) {
			Configure::write('Security.salt', $securitySalt);
		}
		
		$user += array(
			'real_name_1'=> $user['name']
		);
		$user = array_merge(array(
			'name'			=> '',
			'real_name_1'	=> '',
			'email'			=> '',
			'user_group_id'	=> 1,
			'password_1'	=> '',
			'password_2'	=> ''
		), $user);
	
		App::import('Model','User');
		$User = new User();

		$User->create($user);
		if ($User->validates()) {
			$user['password'] = Security::hash($user['password_1'], null, true);
			return $User->save($user,false);
		} else {
			return false;
		}
		
	}
/**
 * データベース設定ファイル[database.php]を保存する
 *
 * @param	array	$options
 * @return boolean
 * @access private
 */
	function createDatabaseConfig($options = array()) {

		if(!is_writable(CONFIGS)) {
			return false;
		}

		$options = array_merge(array(
			'driver'	=> '',
			'host'		=> 'localhost',
			'port'		=> '',
			'login'		=> 'dummy',
			'password'	=> 'dummy',
			'database'	=> 'dummy',
			'prefix'	=> '',
			'schema'	=> '',
			'encoding'	=> 'utf8'
		), $options);
		
		extract($options);

		App::import('File');

		$dbfilename = CONFIGS.'database.php';
		$file = & new File($dbfilename);

		if ($file!==false) {

			if ($file->exists()) {
				$file->delete();
			}

			$file->create();
			$file->open('w',true);
			$file->write("<?php\n");
			$file->write("//\n");
			$file->write("// Database Configuration File created by baserCMS Installation\n");
			$file->write("//\n");
			$file->write("class DATABASE_CONFIG {\n");
			$file->write('var $baser = array('."\n");
			$file->write("\t'driver' => '".$driver."',\n");
			$file->write("\t'persistent' => false,\n");
			$file->write("\t'host' => '".$host."',\n");
			$file->write("\t'port' => '".$port."',\n");
			$file->write("\t'login' => '".$login."',\n");
			$file->write("\t'password' => '".$password."',\n");
			$file->write("\t'database' => '".$database."',\n");
			$file->write("\t'schema' => '".$schema."',\n");
			$file->write("\t'prefix' => '".$prefix."',\n");
			$file->write("\t'encoding' => '".$encoding."'\n");
			$file->write(");\n");

			$file->write('var $plugin = array('."\n");
			$file->write("\t'driver' => '".$driver."',\n");
			$file->write("\t'persistent' => false,\n");
			$file->write("\t'host' => '".$host."',\n");
			$file->write("\t'port' => '".$port."',\n");
			$file->write("\t'login' => '".$login."',\n");
			$file->write("\t'password' => '".$password."',\n");
			$file->write("\t'database' => '".$database."',\n");
			$file->write("\t'schema' => '".$schema."',\n");
			$file->write("\t'prefix' => '".$prefix.Configure::read('BcEnv.pluginDbPrefix')."',\n");
			$file->write("\t'encoding' => '".$encoding."'\n");
			$file->write(");\n");
			$file->write("}\n");
			$file->write("?>\n");

			$file->close();
			return true;

		} else {
			return false;
		}

	}
/**
 * インストール設定ファイルを生成する
 * 
 * @return boolean 
 * @access public
 */
	function createInstallFile($securitySalt, $siteUrl = "") {

		$installFileName = CONFIGS.'install.php';
		
		if(!$siteUrl) {
			$siteUrl = siteUrl();
		}
		$installCoreData = array("<?php",	
			"Configure::write('Security.salt', '{$securitySalt}');",
			"Configure::write('Cache.disable', false);",
			"Configure::write('Session.save', 'cake');",
			"Configure::write('BcEnv.siteUrl', '{$siteUrl}');",
			"Configure::write('BcEnv.sslUrl', '');",
			"Configure::write('BcApp.adminSsl', false);",
			"Configure::write('BcApp.mobile', true);",
			"Configure::write('BcApp.smartphone', true);",
			"Cache::config('default', array('engine' => 'File'));",
			"Configure::write('debug', 0);",
		"?>");
		if(file_put_contents($installFileName, implode("\n", $installCoreData))) {
			return chmod($installFileName,0666);
		}else {
			return false;
		}
		
	}
/**
 * セキュリティ用のキーを生成する
 *
 * @param	int $length
 * @return string キー
 * @access	protected
 */
	function setSecuritySalt($length = 40) {

		$keyset = "abcdefghijklmABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$randkey = "";
		for ($i=0; $i<$length; $i++)
			$randkey .= substr($keyset, rand(0,strlen($keyset)-1), 1);
		Configure::write('Security.salt', $randkey);
		return $randkey;

	}
/**
 * データベースを初期化する
 * 
 * @param type $reset
 * @param type $dbConfig
 * @param type $dbDataPattern
 * @return type
 * @access public 
 */
	function initDb($dbConfig, $reset = true, $dbDataPattern = 'core.demo') {
		
		if($reset) {
			$this->deleteTables();
			$this->deleteTables('plugin');
		}
		
		return $this->constructionDb($dbConfig, $dbDataPattern);
		
	}
/**
 * データベースを構築する
 * 
 * @param array $dbConfig
 * @param string $dbDataPattern
 * @return boolean
 * @access public
 */
	function constructionDb($dbConfig, $dbDataPattern = 'core.demo') {

		if(!$this->constructionTable(BASER_CONFIGS, 'baser', $dbConfig, $dbDataPattern)) {
			$this->log("コアテーブルの構築に失敗しました。");
			return false;
		}
		$dbConfig['prefix'].=Configure::read('BcEnv.pluginDbPrefix');
		$corePlugins = Configure::read('BcApp.corePlugins');
		foreach($corePlugins as $corePlugin) {
			if(!$this->constructionTable(BASER_PLUGINS.$corePlugin.DS.'config'.DS, 'plugin', $dbConfig, $dbDataPattern)) {
				$this->log("プラグインテーブルの構築に失敗しました。");
				return false;
			}
		}

		if(strpos($dbDataPattern, '.') === false) {
			$this->log("データパターンの形式が不正です。");
			return false;
		}
		list($theme, $pattern) = explode('.', $dbDataPattern);
		if($theme == 'core') {
			if(!$this->loadDefaultDataPattern('baser', $dbConfig, $pattern)) {
				$this->log("コアの初期データのロードに失敗しました。");
				return false;
			}
			foreach($corePlugins as $corePlugin) {
				if(!$this->loadDefaultDataPattern('plugin', $dbConfig, $pattern, 'core', $corePlugin)) {
					$this->log("プラグインの初期データのロードに失敗しました。");
					return false;
				}
			}
		} else {
			if(!$this->loadDefaultDataPattern('baser', $dbConfig, $pattern, $theme)) {
				$this->log("初期データのロードに失敗しました。");
				return false;
			}
			foreach($corePlugins as $corePlugin) {
				if(!$this->loadDefaultDataPattern('plugin', $dbConfig, $pattern, $theme, $corePlugin)) {
					$this->log("プラグインの初期データのロードに失敗しました。");
					return false;
				}
			}
		}
		
		return true;

	}
/**
 * 初期データを読み込む
 * 
 * @param string $dbConfigKeyName
 * @param array $dbConfig
 * @param string $pattern
 * @param string $theme
 * @param string $plugin
 * @return boolean 
 */
	function loadDefaultDataPattern($dbConfigKeyName, $dbConfig, $pattern, $theme = 'core', $plugin = 'core') {
		
		$db =& $this->_getDataSource($dbConfigKeyName, $dbConfig);
		$driver = preg_replace('/^bc_/', '', $db->config['driver']);
		
		// CSVの場合ロックを解除しないとデータの投入に失敗する
		if($driver == 'csv') {
			$db->reconnect();
		}

		if($theme == 'core') {
			if($plugin == 'core') {
				$paths = array(BASER_CONFIGS.'data'.DS.$pattern);
			} else {
				$paths = array(
					BASER_PLUGINS.$plugin.DS.'config'.DS.'data'.DS.$pattern,
					BASER_PLUGINS.$plugin.DS.'config'.DS.'data'.DS.'default'
				);
			}
		} else {
			if($plugin == 'core') {
				$paths = array(
					BASER_THEMES.$theme.DS.'config'.DS.'data'.DS.$pattern,
					BASER_CONFIGS.'theme'.DS.$theme.DS.'config'.DS.'data'.$pattern
				);
			} else {
				$paths = array(
					BASER_THEMES.$theme.DS.'config'.DS.'data'.DS.$pattern.DS.$plugin,
					BASER_CONFIGS.'theme'.DS.$theme.DS.'config'.DS.'data'.$pattern.DS.$plugin,
					BASER_PLUGINS.$plugin.DS.'config'.DS.'data'.DS.$pattern,
					BASER_PLUGINS.$plugin.DS.'config'.DS.'data'.DS.'default'
				);
			}
		}
		
		$pathExists = false;
		foreach($paths as $path) {
			if(is_dir($path)) {
				$pathExists = true;
				break;
			}
		}

		if(!$pathExists) {
			$this->log("初期データフォルダが見つかりません。");
			return false;
		}
		
		$Folder = new Folder($path);
		$files = $Folder->read(true, true, true);
		// 初期データ投入
		foreach($files[1] as $file) {
			if(!preg_match('/\.csv$/',$file)) {
				continue;
			}
			if(!$db->loadCsv(array('path'=>$file, 'encoding'=>'SJIS'))){
				return false;
			}
		}
		
		return true;
		
	}
/**
 * テーブルを構築する
 *
 * @param string	$path
 * @param string	$dbConfigKeyName
 * @param string	$dbConfig
 * @param string	$dbDataPattern
 * @return boolean
 * @access public
 */
	function constructionTable($path, $dbConfigKeyName = 'baser', $dbConfig = null, $dbDataPattern = 'core.demo') {

		$db =& $this->_getDataSource($dbConfigKeyName, $dbConfig);
		$driver = preg_replace('/^bc_/', '', $db->config['driver']);
		
		if (@!$db->connected && $driver != 'csv') {
			return false;
		} elseif($driver == 'csv') {
			// CSVの場合はフォルダを作成する
			$Folder = new Folder($db->config['database'], true, 00777);
		} elseif($driver == 'sqlite3') {
			$db->connect();
			chmod($db->config['database'], 0666);
		}

		// DB構築
		$Folder = new Folder($path.'sql');
		$files = $Folder->read(true, true, true);
		if(isset($files[1])) {
			foreach($files[1] as $file) {

				if(!preg_match('/\.php$/',$file)) {
					continue;
				}
				if(!$db->createTableBySchema(array('path'=>$file))){
					return false;
				}
				
			}
		}
		
		return true;

	}
/**
 * 全てのテーブルを削除する
 * 
 * @param array $dbConfig 
 * @return boolean
 * @access public
 */
	function deleteAllTables($dbConfig = null) {

		$result = true;
		if(!$this->deleteTables('baser', $dbConfig)) {
			$result = false;
		}
		$dbConfig['prefix'] .= Configure::read('BcEnv.pluginDbPrefix');
		if(!$this->deleteTables('plugin', $dbConfig)) {
			$result = false;
		}
		return $result;
		
	}
/**
 * テーブルを削除する
 * 
 * @param string $dbConfigKeyName
 * @param array $dbConfig
 * @return boolean
 * @access public
 * TODO 処理を DboSource に移動する
 */
	function deleteTables($dbConfigKeyName = 'baser', $dbConfig = null) {

		$db =& $this->_getDataSource($dbConfigKeyName, $dbConfig);
		$dbConfig = $db->config;
		
		/* 削除実行 */
		// TODO schemaを有効活用すればここはスッキリしそうだが見送り
		$dbType = preg_replace('/^bc_/', '', $dbConfig['driver']);
		switch ($dbType) {
			case 'mysql':
				$sources = $db->listSources();
				foreach($sources as $source) {
					if(preg_match("/^".$dbConfig['prefix']."([^_].+)$/", $source)) {
						$sql = 'DROP TABLE '.$source;
						$db->execute($sql);
					}
				}
				break;

			case 'postgres':
				$sources = $db->listSources();
				foreach($sources as $source) {
					if(preg_match("/^".$dbConfig['prefix']."([^_].+)$/", $source)) {
						$sql = 'DROP TABLE '.$source;
						$db->execute($sql);
					}
				}
				// シーケンスも削除
				$sql = "SELECT sequence_name FROM INFORMATION_SCHEMA.sequences WHERE sequence_schema = '{$dbConfig['schema']}';";
				$sequences = $db->query($sql);
				$sequences = Set::extract('/0/sequence_name',$sequences);
				foreach($sequences as $sequence) {
					if(preg_match("/^".$dbConfig['prefix']."([^_].+)$/", $sequence)) {
						$sql = 'DROP SEQUENCE '.$sequence;
						$db->execute($sql);
					}
				}
				break;

			case 'sqlite':
			case 'sqlite3':
				@unlink($dbConfig['database']);
				break;

			case 'csv':
				$folder = new Folder($dbConfig['database']);
				$files = $folder->read(true,true,true);
				foreach($files[1] as $file) {
					if(basename($file) != 'empty') {
						@unlink($file);
					}
				}
				break;

		}
		return true;

	}
/**
 * データソースを取得する
 * 
 * @param string $configKeyName
 * @param array $dbConfig
 * @return DataSource
 * @access public
 */
	function &_getDataSource($dbConfigKeyName = 'baser', $dbConfig = null) {
		
		if($dbConfig) {
			$db =& ConnectionManager::create($dbConfigKeyName, $dbConfig);
			if(!$db) {
				$db =& ConnectionManager::getDataSource($dbConfigKeyName);
			}
		} else {
			$db =& ConnectionManager::getDataSource($dbConfigKeyName);
		}
		
		return $db;
		
	}
/**
 * テーマを配置する
 *
 * @param string $theme
 * @return boolean
 * @access public
 */
	function deployTheme($theme = null) {

		if($theme) {
			if(is_array($theme)) {
				$sources = $theme;
			} else {
				$sources = array($theme);
			}
		} else {
			$Folder = new Folder(BASER_CONFIGS.'theme');
			$files = $Folder->read();
			$sources = $files[0];
		}
		
		$result = true;
		foreach($sources as $theme) {
			$targetPath = WWW_ROOT.'themed'.DS.$theme;
			$sourcePath = BASER_CONFIGS.'theme'.DS.$theme;
			$Folder->delete($targetPath);
			if($Folder->copy(array('to'=>$targetPath,'from'=>$sourcePath,'mode'=>00777,'skip'=>array('_notes')))) {
				if(!$Folder->create($targetPath.DS.'pages',00777)) {
					$result = false;
				}
			} else {
				$result = false;
			}
		}
		
		return $result;

	}
/**
 * 設定ファイルをリセットする
 * 
 * @return boolean 
 * @access public
 */
	function resetSetting() {

		$result = true;
		if(file_exists(CONFIGS.'database.php')) {
			if(!unlink(CONFIGS.'database.php')) {
				$result = false;
			}
		}
		if(file_exists(CONFIGS.'install.php')) {
			if(!unlink(CONFIGS.'install.php')) {
				$result = false;
			}
		}
		return $result;
		
	}
/**
 * テーマのページテンプレートを初期化する 
 * 
 * @return boolean
 * @access public
 */
	function resetThemePages() {
		
		$result = true;
		$themeFolder = new Folder(WWW_ROOT.'themed');
		$themeFiles = $themeFolder->read(true,true,true);
		foreach($themeFiles[0] as $theme){
			$pagesFolder = new Folder($theme.DS.'pages');
			$pathes = $pagesFolder->read(true,true,true);
			foreach($pathes[0] as $path){
				if(basename($path) != 'admin') {
					$folder = new Folder();
					if(!$folder->delete($path)) {
						$result = false;
					}
					$folder = null;
				}
			}
			foreach($pathes[1] as $path){
				if(basename($path) != 'empty') {
					if(!unlink($path)) {
						$result = false;
					}
				}
			}
			$pagesFolder = null;
		}
		$themeFolder = null;
		return $result;
		
	}
/**
 * baserCMSをリセットする
 * 
 * @param array $dbConfig 
 * @access public
 */
	function reset($dbConfig) {

		$result = true;

		// スマートURLをオフに設定
		if($this->smartUrl()) {
			if(!$this->setSmartUrl(false)){
				$result = false;
				$this->log('スマートURLの設定を正常に初期化できませんでした。');
			}
		}
		
		if(BC_INSTALLED) {
			// 設定ファイルを初期化
			if(!$this->resetSetting()) {
				$result = false;
				$this->log('設定ファイルを正常に初期化できませんでした。');
			}
			// テーブルを全て削除
			if(!$this->deleteAllTables($dbConfig)) {
				$result = false;
				$this->log('データベースを正常に初期化できませんでした。');
			}
		}
		
		// テーマのページテンプレートを初期化
		if(!$this->resetThemePages()) {
			$result = false;
			$this->log('テーマのページテンプレートを初期化できませんでした。');
		}
		
		ClassRegistry::flush();
		clearAllCache();

		return $result;

	}
/**
 * スマートURLの設定を取得
 *
 * @return	boolean
 * @access	public
 */
	function smartUrl(){
		if (Configure::read('App.baseUrl')) {
			return false;
		} else {
			return true;
		}
	}
/**
 * スマートURLの設定を行う
 *
 * @param	boolean	$smartUrl
 * @return	boolean
 * @access	public
 */
	function setSmartUrl($smartUrl, $baseUrl = '') {

		/* install.php の編集 */
		if($smartUrl) {
			if(!$this->setInstallSetting('App.baseUrl', "''")){
				return false;
			}
		} else {
			if(!$this->setInstallSetting('App.baseUrl', '$_SERVER[\'SCRIPT_NAME\']')){
				return false;
			}
		}

		if(BC_DEPLOY_PATTERN == 2 || BC_DEPLOY_PATTERN == 3) {
			$webrootRewriteBase = '/';
		} else {
			$webrootRewriteBase = '/'.APP_DIR.'/webroot';
		}

		/* /app/webroot/.htaccess の編集 */
		$this->_setSmartUrlToHtaccess(WWW_ROOT.'.htaccess', $smartUrl, 'webroot', $webrootRewriteBase, $baseUrl);

		if(BC_DEPLOY_PATTERN == 1) {
			/* /.htaccess の編集 */
			$this->_setSmartUrlToHtaccess(ROOT.DS.'.htaccess', $smartUrl, 'root', '/', $baseUrl);
		}

		return true;

	}
/**
 * .htaccess にスマートURLの設定を書きこむ
 *
 * @param	string	$path
 * @param	array	$rewriteSettings
 * @return	boolean
 * @access	protected
 */
	function _setSmartUrlToHtaccess($path, $smartUrl, $type, $rewriteBase = '/', $baseUrl = '') {

		//======================================================================
		// WindowsのXAMPP環境では、何故か .htaccess を書き込みモード「w」で開けなかったの
		// で、追記モード「a」で開くことにした。そのため、実際の書き込み時は、 ftruncate で、
		// 内容をリセットし、ファイルポインタを先頭に戻している。
		//======================================================================

		$rewritePatterns = array(	"/\n[^\n#]*RewriteEngine.+/i",
									"/\n[^\n#]*RewriteBase.+/i",
									"/\n[^\n#]*RewriteCond.+/i",
									"/\n[^\n#]*RewriteRule.+/i");
		switch($type) {
			case 'root':
				$rewriteSettings = array(	'RewriteEngine on',
											'RewriteBase '.$this->getRewriteBase($rewriteBase, $baseUrl),
											'RewriteRule ^$ '.APP_DIR.'/webroot/ [L]',
											'RewriteRule (.*) '.APP_DIR.'/webroot/$1 [L]');
				break;
			case 'webroot':
				$rewriteSettings = array(	'RewriteEngine on',
											'RewriteBase '.$this->getRewriteBase($rewriteBase, $baseUrl),
											'RewriteCond %{REQUEST_FILENAME} !-d',
											'RewriteCond %{REQUEST_FILENAME} !-f',
											'RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]');
				break;
		}

		$file = new File($path);
		$file->open('a+');
		$data = $file->read();
		foreach ($rewritePatterns as $rewritePattern) {
			$data = preg_replace($rewritePattern, '', $data);
		}
		if($smartUrl) {
			$data .= "\n".implode("\n", $rewriteSettings);
		}
		ftruncate($file->handle,0);
		if(!$file->write($data)){
			$file->close();
			return false;
		}
		$file->close();

	}
/**
 * RewriteBase の設定を取得する
 *
 * @param	string	$base
 * @return	string
 */
	function getRewriteBase($url, $baseUrl){

		if(!$baseUrl) {
			$baseUrl = BC_BASE_URL;
		}
		
		if(preg_match("/index\.php/", $baseUrl)){
			$baseUrl = str_replace('index.php/', '', $baseUrl);
		}
		$baseUrl = preg_replace("/\/$/",'',$baseUrl);
		if($url != '/' || !$baseUrl) {
			$url = $baseUrl.$url;
		}else{
			$url = $baseUrl;
		}

		return $url;

	}
/**
 * インストール設定を書き換える
 *
 * @param	string	$key
 * @param	string	$value
 * @return	boolean
 * @access	public
 */
	function setInstallSetting($key, $value) {
		
		/* install.php の編集 */
		$setting = "Configure::write('".$key."', ".$value.");\n";
		$key = str_replace('.', '\.', $key);
		$pattern = '/Configure\:\:write[\s]*\([\s]*\''.$key.'\'[\s]*,[\s]*([^\s]*)[\s]*\);\n/is';
		$file = new File(CONFIGS.'install.php');
		if(file_exists(CONFIGS.'install.php')) {
			$data = $file->read();
		}else {
			$data = "<?php\n?>";
		}
		if(preg_match($pattern, $data)) {
			$data = preg_replace($pattern, $setting, $data);
		} else {
			$data = preg_replace("/\n\?>/is", "\n".$setting.'?>', $data);
		}
		$return = $file->write($data);
		$file->close();
		return $return;

	}
/**
 * 環境チェック
 * 
 * @return array 
 */
	function checkEnv() {
		
		if(function_exists('apache_get_modules')) {
			$rewriteInstalled = in_array('mod_rewrite', apache_get_modules());
		}else {
			$rewriteInstalled = -1;
		}
		
		$status = array(
			'encoding'		=> mb_internal_encoding(),
			'phpVersion'	=> phpversion(),
			'phpMemory'		=> intval(ini_get('memory_limit')),
			'safeModeOff'	=> !ini_get('safe_mode'),
			'configDirWritable'	=> is_writable(CONFIGS),
			'coreFileWritable'	=> is_writable(CONFIGS.'core.php'),
			'themeDirWritable'	=> is_writable(WWW_ROOT.'themed'),
			'tmpDirWritable'	=> is_writable(TMP),
			'dbDirWritable'		=> is_writable(APP.'db'),
			'phpActualVersion'	=> preg_replace('/[a-z-]/','', phpversion()),
			'phpGd'				=> extension_loaded('gd'),
			'phpPdo'			=> extension_loaded('pdo'),
			'apacheRewrite'		=> $rewriteInstalled
		);
		
		$check = array(
			'encodingOk'	=> (eregi('UTF-8', $status['encoding']) ? true : false),
			'phpVersionOk'	=> version_compare ( preg_replace('/[a-z-]/','', $status['phpVersion']), Configure::read('BcRequire.phpVersion'), '>='),
			'phpMemoryOk'	=> ((($status['phpMemory'] >= Configure::read('BcRequire.phpMemory')) || $status['phpMemory'] == -1) === TRUE)
		);
		
		if(!$status['coreFileWritable']) {
			chmod(CONFIGS.'core.php', 0666);
			$status['coreFileWritable'] = is_writable(CONFIGS.'core.php');
		}
		if(!$status['configDirWritable']) {
			chmod(CONFIGS, 0777);
			$status['configDirWritable'] = is_writable(CONFIGS);
		}
		if(!$status['themeDirWritable']) {
			chmod(WWW_ROOT.'themed', 0777);
			$status['themeDirWritable'] = is_writable(WWW_ROOT.'themed');
		}
		if(!$status['tmpDirWritable']) {
			chmod(TMP, 0777);
			$status['tmpDirWritable'] = is_writable(TMP);
		}
		if(!$status['dbDirWritable']) {
			chmod(APP.'db', 0777);
			$status['dbDirWritable'] = is_writable(APP.'db');
		}
		
		return $status + $check;

	}
}