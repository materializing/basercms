<?php
/* SVN FILE: $Id$ */
/**
 * [ADMIN] ログイン
 *
 * PHP versions 4 and 5
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2012, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2012, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			baser.views
 * @since			baserCMS v 2.0.0
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://basercms.net/license/index.html
 */
if ( $session->check('Message.auth') ) {
    $session->flash('auth');
}
$userModel = Configure::read('BcAuthPrefix.'.$currentPrefix.'.userModel');
$this->addScript(<<< CSS_END
<style type="text/css">
#Contents {
	display: none;
}
#CreditScroller,#CreditScroller a{
	color:#333!important;
}
#Credit {
	text-align: right;
}
#CreditScrollerInner {
	margin-right:0;
}
</style>
CSS_END
);
?>

<script type="text/javascript">
$(function(){

	$("body").prepend($("#Login"));
	$('#UserName').focus();
	changeNavi('#UserName');
	changeNavi('#UserPassword');

	$('#UserName,#UserPassword').bind('keyup', function(){
		if($(this).val()) {
			$(this).prev().hide();
		} else {
			$(this).prev().show();
		}
	});

	$("#Login").click(function(){
		changeView(false);
	});

	$("#LoginInner").click(function(e){
		if (e && e.stopPropagation) {
			e.stopPropagation();
		} else {
			window.event.cancelBubble = true;
		}
	});

	$("#BtnLogin").click(function(e){

		$("#UserAjaxLoginForm").ajaxSubmit({
			beforeSend: function() {
				$("#Waiting").show();
			},
			url: $("#UserAjaxLoginForm").attr('action'),
			success: function(response, status) {
				if(response) {
					$("#Login").fadeOut(500);
					if($("#Credit").css('display') == 'none') {
						document.location = response;
					} else {
						openCredit(function(){
							document.location = response;
						});
					}
				} else {
					$("#AlertMessage").html('ログインに失敗しました。アカウント名、パスワードを確認してください。');
					$("#AlertMessage").fadeIn(500);
				}
			},
			error: function() {
				$("#AlertMessage").html('ログイン処理に失敗しました。');
				$("#AlertMessage").fadeIn(500);
			},
			complete: function(){
				$("#Waiting").hide();
			}
		});

		return false;

	});

	if($("#LoginCredit").html() == 1) {
		changeView($("#LoginCredit").html());
	}

});
function changeNavi(target){
	if($(target).val()) {
		$(target).prev().hide();
	} else {
		$(target).prev().show();
	}
}
function changeView(creditOn) {

	if(creditOn) {
		credit();
		$("#LoginInner").css('color', '#FFF');
		$("#HeaderInner").css('height', '70px');
		$("#Logo").css('position', 'absolute');
		$("#Logo").css('z-index', '10000');
	} else {
		openCredit();
	}

}
function openCredit(completeHandler) {
	$("#LoginInner").css('color', '#333');
	$("#HeaderInner").css('height', 'auto');
	$("#Logo").css('position', 'relative');
	$("#Logo").css('z-index', '0');
	$("#Wrap").css('height', '280px');
	if(completeHandler) {
		$("#Credit").fadeOut(1000, completeHandler);
	} else {
		$("#Credit").fadeOut(1000);
	}
}
</script>

<div id="LoginCredit"><?php echo $bcBaser->siteConfig['login_credit'] ?></div>
<div id="Login">

	<div id="LoginInner">

		<h1><?php $bcBaser->contentsTitle() ?></h1>
		<div id="AlertMessage" class="message" style="display:none"></div>
<?php if($currentPrefix == 'front'): ?>
		<?php echo $bcForm->create($userModel, array('action' => 'ajax_login', 'url' => array('controller' => 'users'))) ?>
<?php else: ?>
		<?php echo $bcForm->create($userModel, array('action' => 'ajax_login', 'url' => array($this->params['prefix'] => true, 'controller' => 'users'))) ?>
<?php endif ?>
		<div class="float-left login-input">
			<?php echo $bcForm->label($userModel.'.name', 'アカウント名') ?>
			<?php echo $bcForm->input($userModel.'.name', array('type' => 'text', 'size'=>16 ,'tabindex'=>1)) ?>
		</div>
		<div class="float-left login-input">
			<?php echo $bcForm->label($userModel.'.password', 'パスワード') ?>
			<?php echo $bcForm->input($userModel.'.password',array('type' => 'password', 'size'=>16,'tabindex'=>2)) ?>
		</div>
		<div class="float-left submit">
			<?php echo $bcForm->submit('ログイン', array('div' => false, 'class' => 'btn-red button', 'id' => 'BtnLogin','tabindex'=>4)) ?>
		</div>
		<div class="clear login-etc">
			<?php echo $bcForm->input($userModel.'.saved', array('type' => 'checkbox', 'label' => '保存する','tabindex'=>3)) ?>　
<?php if($currentPrefix == 'front'): ?>
			<?php $bcBaser->link('パスワードを忘れた場合はこちら', array('action' => 'reset_password'), array('rel' => 'popup')) ?>
<?php else: ?>
			<?php $bcBaser->link('パスワードを忘れた場合はこちら', array('action' => 'reset_password', $this->params['prefix'] => true), array('rel' => 'popup')) ?>
<?php endif ?>
		</div>
		<?php echo $bcForm->end() ?>
	</div>

</div>


