<?php
$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="database_tables";
$single_name="Database Table";
$multiple_name=$single_name."s";
$list_fields=array(
	'database','table','active','controls','server'
);
$search_fields=array(
	'database','table','server'
);
$edit_fields=array(
	'database'=>array(
		'field_name'=>'database',
		'attr.pattern'=>"^[a-z0-9_]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required"
	),
	'table'=>array(
		'field_name'=>'table',
		'attr.pattern'=>"^[a-z0-9_]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required"
	),
	'active'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'controls'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'server'=>array(
		'field_name'=>'server',
		'attr.pattern'=>"^[a-z0-9_]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required",
		'default_value'=>"local"
	)
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>