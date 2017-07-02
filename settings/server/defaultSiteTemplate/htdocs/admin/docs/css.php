<?php

$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="css";
$single_name="CSS Sheet";
$multiple_name=$single_name."s";
$list_fields=array(
	'id','title','ord','whitelisted_ips'
);
$search_fields=array(
	'title'
);
$edit_fields=array(
	'title'=>array(
		'field_name'=>'title'
	),
	'description'=>array(
		'field_name'=>'description',
		'edit_field_type'=>'textarea',
		'size'=>3
	),
	'sheet'=>array(
		'field_name'=>'sheet',
		'edit_field_type'=>'textarea',
		'size'=>50
	),
	'ord'=>array(
		'field_name'=>'ord'
	),
	'whitelisted_ips'=>array(
		'field_name'=>'whitelisted_ips',
		'edit_field_type'=>'textarea',
	),
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>