<?php
/* SVN FILE: $Id$ */
/**
 * [ADMIN] メールフィールド フォーム
 *
 * PHP versions 5
 *
 * baserCMS :  Based Website Development Project <http://basercms.net>
 * Copyright 2008 - 2012, baserCMS Users Community <http://sites.google.com/site/baserusers/>
 *
 * @copyright		Copyright 2008 - 2012, baserCMS Users Community
 * @link			http://basercms.net baserCMS Project
 * @package			baser.plugins.mail.views
 * @since			baserCMS v 0.1.0
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://basercms.net/license/index.html
 */
?>


<script type="text/javascript">
$(window).load(function() {
	$("#MailFieldFieldName").focus();
});
jQuery(function($) {

	// タイプを選択すると入力するフィールドが切り替わる
	$("#MailFieldType").change(function(){loadSetting($("#MailFieldType").val())});
	// 項目名を入力時に項目見出しを自動入力
	$("#MailFieldName").change(function(){
		if(!$("#MailFieldHead").val()){
			$("#MailFieldHead").val($("#MailFieldName").val());
		}
	});

	loadSetting($("#MailFieldType").val());

/**
 * タイプの値によってフィールドの表示設定を行う
 */
	function loadSetting(value){

		switch ($("#MailFieldType").val()){
			case 'text':
			case 'email':
				$("#rowSize").show();
				$("#rowRows").hide();$("#MailFieldRows").val('');
				$("#rowMaxlength").show();
				$("#rowSource").hide();$("#MailFieldSource").val('');
				$("#rowAutoConvert").show();
				$("#rowSeparator").hide();$("#MailFieldSeparator").val('');
				break;
			case 'textarea':
				$("#rowSize").show();
				$("#rowRows").show();
				$("#rowMaxlength").hide();$("#MailFieldMaxlength").val('');
				$("#rowSource").hide();$("#MailFieldSource").val('');
				$("#rowAutoConvert").show();
				$("#rowSeparator").hide();$("#MailFieldSeparator").val('');
				break;
			case 'radio':
			case 'multi_check':
				$("#rowSize").hide();$("#MailFieldSize").val('');
				$("#rowRows").hide();$("#MailFieldRows").val('');
				$("#rowMaxlength").hide();$("#MailFieldMaxlength").val('');
				$("#rowSource").show();
				$("#rowAutoConvert").hide();$("#MailFieldAutoConvert").val('');
				$("#rowSeparator").show();
				break;
			case 'select':
				$("#rowSize").hide();$("#MailFieldSize").val('');
				$("#rowRows").hide();$("#MailFieldRows").val('');
				$("#rowMaxlength").hide();$("#MailFieldMaxlength").val('');
				$("#rowSource").show();
				$("#rowAutoConvert").hide();$("#MailFieldAutoConvert").val('');
				$("#rowSeparator").hide();$("#MailFieldSeparator").val('');
				break;
			case 'pref':
			case 'date_time_wareki':
			case 'date_time_calender':
				$("#rowSize").hide();$("#MailFieldSize").val('');
				$("#rowRows").hide();$("#MailFieldRows").val('');
				$("#rowMaxlength").hide();$("#MailFieldMaxlength").val('');
				$("#rowSource").hide();$("#MailFieldSource").val('');
				$("#rowAutoConvert").hide();$("#MailFieldAutoConvert").val('');
				$("#rowSeparator").hide();$("#MailFieldSeparator").val('');
				break;
			case 'autozip':
				$("#rowSize").show();
				$("#rowRows").hide();$("#MailFieldRows").val('');
				$("#rowMaxlength").show();$("#MailFieldMaxlength").val('7');
				$("#rowSource").show();
				$("#rowAutoConvert").show();$("#MailFieldAutoConvert").val('CONVERT_HANKAKU');
				$("#rowSeparator").hide();$("#MailFieldSeparator").val('');
				break;
		}
	}
});
</script>


<?php /* MailContent.idを第一引数にしたいが為にURL直書き */ ?>
<?php if($this->action == 'admin_add'): ?>
<?php echo $bcForm->create('MailField', array('url' => array('controller' => 'mail_fields', 'action' => 'add', $mailContent['MailContent']['id']))) ?>
<?php elseif($this->action == 'admin_edit'): ?>
<?php echo $bcForm->create('MailField', array('url' => array('controller' => 'mail_fields', 'action' => 'edit', $mailContent['MailContent']['id'], $bcForm->value('MailField.id'), 'id' => false))) ?>
<?php endif; ?>
<?php echo $bcForm->hidden('MailField.id') ?>

<h2>基本項目</h2>

<!-- form -->
<div class="section">
	<table cellpadding="0" cellspacing="0" id="FormTable" class="form-table">
	<?php if($this->action == 'admin_edit'): ?>
		<tr>
			<th class="col-head"><?php echo $bcForm->label('MailField.no', 'NO') ?></th>
			<td class="col-input">
				<?php echo $bcForm->value('MailField.no') ?>
				<?php echo $bcForm->input('MailField.no', array('type' => 'hidden')) ?>
			</td>
		</tr>
	<?php endif; ?>
		<tr id="rowFieldName">
			<th class="col-head"><?php echo $bcForm->label('MailField.field_name', 'フィールド名') ?>&nbsp;<span class="required">*</span></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.field_name', array('type' => 'text', 'size' => 40, 'maxlength' => 255)) ?>
				<?php echo $html->image('admin/icn_help.png', array('id' => 'helpFieldName', 'class' => 'btn help', 'alt' => 'ヘルプ')) ?>
				<?php echo $bcForm->error('MailField.field_name') ?>
				<div id="helptextFieldName" class="helptext">重複しない半角英数字で入力してください。</div>
			</td>
		</tr>
		<tr id="rowName">
			<th class="col-head"><?php echo $bcForm->label('MailField.name', '項目名') ?>&nbsp;<span class="required">*</span></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.name', array('type' => 'text', 'size' => 40, 'maxlength' => 255)) ?>
				<?php echo $html->image('admin/icn_help.png', array('id' => 'helpName', 'class' => 'btn help', 'alt' => 'ヘルプ')) ?>
				<?php echo $bcForm->error('MailField.name') ?>
				<div id="helptextName" class="helptext">項目を特定しやすいわかりやすい名前を入力してください。日本語可。</div>
			</td>
		</tr>
		<tr id="rowType">
			<th class="col-head"><?php echo $bcForm->label('MailField.type', 'タイプ') ?>&nbsp;<span class="required">*</span></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.type', array('type' => 'select', 'options' => $controlSource['type'])) ?>
				<?php echo $html->image('admin/icn_help.png', array('id' => 'helpType', 'class' => 'btn help', 'alt' => 'ヘルプ')) ?>
				<?php echo $bcForm->error('MailField.type') ?>
				<div id="helptextType" class="helptext">
					<ul>
						<li>Eメールを選択すると、メールフォーム送信の際、入力されたEメール宛に自動返信メールを送信します。<br />
							<small>※ 前バージョンとの互換性の為、フィールド名を「email_1」とした場合、Eメールを選択しなくても自動返信メールを送信します。</small></li>
						<li>自動補完郵便番号の場合は、選択リストに都道府県のフィールドと住所のフィールドのリストを指定します。</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr id="rowHead">
			<th class="col-head"><?php echo $bcForm->label('MailField.head', '項目見出し') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.head', array('type' => 'text', 'size' => 40, 'maxlength' => 255)) ?>
				<?php echo $html->image('admin/icn_help.png', array('id' => 'helpHead', 'class' => 'btn help', 'alt' => 'ヘルプ')) ?>
				<?php echo $bcForm->error('MailField.head') ?>
				<div id="helptextHead" class="helptext"> グループの場合、２番目以降のフィールドは不要です。 </div>
			</td>
		</tr>
		<tr id="rowNotEmpty">
			<th class="col-head"><?php echo $bcForm->label('MailField.not_empty', '必須マーク') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.not_empty', array('type' => 'checkbox', 'label'=>'項目見出しに必須マークを表示する')) ?>
				<?php echo $bcForm->error('MailField.not_empty') ?>
			</td>
		</tr>
		<tr id="rowValid">
			<th class="col-head"><?php echo $bcForm->label('MailField.valid', '入力チェック') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.valid', array('type' => 'select', 'options' => $controlSource['valid'], 'empty' => 'なし')) ?>
				<?php echo $bcForm->error('MailField.valid') ?>
			</td>
		</tr>
		<tr id="rowAttention">
			<th class="col-head"><?php echo $bcForm->label('MailField.attention', '注意書き') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.attention', array('type' => 'textarea', 'cols' => 35, 'rows' => 3)) ?>
				<?php echo $bcForm->error('MailField.attention') ?>
			</td>
		</tr>
		<tr id="rowBeforeAttachment">
			<th class="col-head"><?php echo $bcForm->label('MailField.before_attachment', '前見出し') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.before_attachment', array('type' => 'textarea', 'cols' => 35, 'rows' => 3)) ?>
				<?php echo $bcForm->error('MailField.before_attachment') ?>
			</td>
		</tr>
		<tr id="rowAfterAttachment">
			<th class="col-head"><?php echo $bcForm->label('MailField.after_attachment', '後見出し') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.after_attachment', array('type' => 'textarea', 'cols' => 35, 'rows' => 3)) ?>
				<?php echo $bcForm->error('MailField.after_attachment') ?>
			</td>
		</tr>
		<tr id="rowDescription">
			<th class="col-head"><?php echo $bcForm->label('MailField.description', '説明文') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.description', array('type' => 'textarea', 'cols' => 35, 'rows' => 3)) ?>
				<?php echo $bcForm->error('MailField.description') ?>
			</td>
		</tr>
		<tr id="rowSource">
			<th class="col-head"><?php echo $bcForm->label('MailField.source', '選択リスト') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.source', array('type' => 'textarea', 'cols' => 35, 'rows' => 4)) ?>
				<?php echo $html->image('admin/icn_help.png', array('id' => 'helpSource', 'class' => 'btn help', 'alt' => 'ヘルプ')) ?>
				<?php echo $bcForm->error('MailField.source') ?>
				<div id="helptextSource" class="helptext">
					<ul>
						<li>ラジオボタン、セレクトボックス、マルチチェックボックスの場合の選択リスト指定します。</li>
						<li>自動補完郵便番号の場合は、都道府県のフィールドと住所のフィールドのリストを指定します。</li>
						<li>リストは　|　で区切って入力します。</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr id="rowSize">
			<th class="col-head"><?php echo $bcForm->label('MailField.size', '表示サイズ') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.size', array('type' => 'text', 'size'=>10,'maxlength'=>255)) ?>
				<?php echo $bcForm->error('MailField.size') ?>
			</td>
		</tr>
		<tr id="rowRows">
			<th class="col-head"><?php echo $bcForm->label('MailField.rows', '行数') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.rows', array('type' => 'text', 'size' => 10, 'maxlength' => 255)) ?>
				<?php echo $html->image('admin/icn_help.png', array('id' => 'helpRows', 'class' => 'btn help', 'alt' => 'ヘルプ')) ?>
				<?php echo $bcForm->error('MailField.rows') ?>
				<div id="helptextRows" class="helptext">テキストボックスの場合の行数を指定します。</div>
			</td>
		</tr>
		<tr id="rowMaxlength">
			<th class="col-head"><?php echo $bcForm->label('MailField.maxlength', '最大値') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.maxlength', array('type' => 'text', 'size' => 10, 'maxlength' => 255)) ?>文字
				<?php echo $bcForm->error('MailField.maxlength') ?>
			</td>
		</tr>
	</table>
</div>
<h2 class="btn-slide-form"><a href="javascript:void(0)" id="formOption">オプション</a></h2>
<div class="section">
	<table cellpadding="0" cellspacing="0" class="form-table slide-body" id="formOptionBody">
		<tr id="rowValidEx">
			<th class="col-head"><?php echo $bcForm->label('MailField.valid_ex', '拡張入力チェック') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.valid_ex', array('type' => 'select', 'options' =>$controlSource['valid_ex'], 'empty' => 'なし')) ?>
				<?php echo $bcForm->error('MailField.valid_ex') ?>
			</td>
		</tr>
		<tr id="rowGroupField">
			<th class="col-head"><?php echo $bcForm->label('MailField.group_field', 'グループ名') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.group_field', array('type' => 'text', 'size' => 40, 'maxlength' => 255)) ?>
				<?php echo $html->image('admin/icn_help.png', array('id' => 'helpGroupField', 'class' => 'btn help', 'alt' => 'ヘルプ')) ?>
				<?php echo $bcForm->error('MailField.group_field') ?>
				<div id="helptextGroupField" class="helptext">
					<ul>
						<li>各項目を同じグループとするには同じグループ名を入力します。</li>
						<li>半角英数字で入力してください。</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr id="rowGroupValid">
			<th class="col-head"><?php echo $bcForm->label('MailField.group_valid', 'グループ入力チェック') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.group_valid', array('type' => 'text', 'size' => 40, 'maxlength' => 255)) ?>
				<?php echo $html->image('admin/icn_help.png', array('id' => 'helpGroupValid', 'class' => 'btn help', 'alt' => 'ヘルプ')) ?>
				<?php echo $bcForm->error('MailField.group_valid') ?>
				<div id="helptextGroupValid" class="helptext">
					<ul>
						<li>グループで連帯して入力チェックを行うには同じグループ名を入力します。</li>
						<li>グループ内の項目が一つでもエラーとなるとグループ内の全ての項目にエラーを意味する背景色が付きます。</li>
						<li>半角英数字で入力してください。</li>
					</ul>
				</div>
			</td>
		</tr>
		<tr id="rowOptions">
			<th class="col-head"><?php echo $bcForm->label('MailField.options', 'オプション') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.options', array('type' => 'text', 'size' => 40, 'maxlength' => 255)) ?>
				<?php echo $bcForm->error('MailField.options') ?>
			</td>
		</tr>
		<tr id="rowClass">
			<th class="col-head"><?php echo $bcForm->label('MailField.class', 'クラス名') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.class', array('type' => 'text', 'size' => 40, 'maxlength' => 255)) ?>
				<?php echo $bcForm->error('MailField.class') ?>
			</td>
		</tr>
		<tr id="rowSeparator">
			<th class="col-head"><?php echo $bcForm->label('MailField.separator', '区切り文字') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.separator', array('type' => 'text', 'size' => 40, 'maxlength' => 255)) ?>
				<?php echo $bcForm->error('MailField.separator') ?>
			</td>
		</tr>
		<tr id="rowDefault">
			<th class="col-head"><?php echo $bcForm->label('MailField.default_value', '初期値') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.default_value', array('type' => 'textarea', 'cols' => 35, 'rows' => 2)) ?>
				<?php echo $bcForm->error('MailField.default_value') ?>
			</td>
		</tr>
		<tr id="rowAutoConvert">
			<th class="col-head"><?php echo $bcForm->label('MailField.auto_convert', '自動変換') ?></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.auto_convert', array('type' => 'select', 'options' => $controlSource['auto_convert'], 'empty' => 'なし')) ?>
				<?php echo $bcForm->error('MailField.auto_convert') ?>
			</td>
		</tr>
		<tr id="rowUseField">
			<th class="col-head"><?php echo $bcForm->label('MailField.use_field', '利用状態') ?>&nbsp;<span class="required">*</span></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.use_field', array(
						'type'		=> 'radio',
						'options'	=> $bcText->booleanStatusList(),
						'legend'	=> false,
						'separator'	=>	'&nbsp;&nbsp;')) ?>
				<?php echo $bcForm->error('MailField.use_field') ?>
			</td>
		</tr>
		<tr id="rowNoSend">
			<th class="col-head"><?php echo $bcForm->label('MailField.no_send', 'メール送信') ?>&nbsp;<span class="required">*</span></th>
			<td class="col-input">
				<?php echo $bcForm->input('MailField.no_send', array(
						'type'		=> 'radio',
						'options'	=> array(0 => '送信する', 1 => '送信しない'),
						'legend'	=> false,
						'separator'	=> '&nbsp;&nbsp;')) ?>
				<?php echo $bcForm->error('MailField.no_send') ?>
			</td>
		</tr>
	</table>
</div>
<!-- button -->
<div class="submit">
<?php if($this->action == 'admin_add'): ?>
	<?php echo $bcForm->submit('登録', array('div' => false, 'class' => 'btn-red button')) ?>
<?php else: ?>
	<?php echo $bcForm->submit('更新', array('div' => false, 'class' => 'btn-orange button')) ?>
	<?php $bcBaser->link('削除',
			array('action' => 'delete', $mailContent['MailContent']['id'], $bcForm->value('MailField.id')),
			array('class'=>'btn-gray button'),
			sprintf('%s を本当に削除してもいいですか？', $bcForm->value('MailField.name')),
			false); ?>
<?php endif ?>
</div>

<?php echo $bcForm->end() ?>