<?php

$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="users";
$single_name="User";
$multiple_name=$single_name."s";
$list_fields=array(
	'first_name','email'
);
$search_fields=array(
	'first_name','email'
);
$edit_fields=array(
	'first_name'=>array(),
	'last_name'=>array(),
	'email'=>array(),
	'url'=>array(),
	'active'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'is_admin'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'password'=>array(
		'edit_field_type'=>'read_only'
	),
	'nonce'=>array(
		'edit_field_type'=>'read_only'
	),
	'timestamp'=>array(
		'edit_field_type'=>'read_only'
	),
	'variables'=>array(
		'edit_field_type'=>'serialize'
	)
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>
