<?php

$user=loginModel::requireAdmin();

$database=$SITE_DATABASE;
$table="images";
$single_name="Image";
$multiple_name=$single_name."s";
$list_fields=array(
	'name','active','timestamp'
);
$search_fields=array(
	'name'
);
$edit_fields=array(
	'name'=>array(
	),
	'active'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'im'=>array(
		'edit_field_type'=>'file_base64',
		'file_type'=>'image',
		'show_file_preview'=>true,
		'database'=>'site'
	),
	'timestamp'=>array(
		'edit_field_type'=>'read_only',
	),
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>