<?php

$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="documentation";
$single_name="Document";
$multiple_name=$single_name."s";
$list_fields=array(
	'title','category','updated'
);
$search_fields=array(
	'title','category'
);
$edit_fields=array(
	'title'=>array(),
	'category'=>array(),
	'content'=>array(
		'edit_field_type'=>'redactor_WYSIWYG'
	),
	'updated'=>array(
		'edit_field_type'=>'read_only'
	)
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>