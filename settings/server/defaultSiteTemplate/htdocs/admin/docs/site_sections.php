<?php

$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="site_sections";
$single_name="Site Section";
$multiple_name=$single_name."s";
$list_fields=array(
	'site_id','name','identifier'
);
$search_fields=array(
	'name'
);
$edit_fields=array(
	'site_id'=>array(
		'field_name'=>'site_id',
		'blank_top_select'=>false,
		'input_value'=>'id',
		'input_visible'=>'subdomain',
		'select_db'=>$database,
		'select_table'=>'sites',
		'select_table_display_where'=>"WHERE `active`='T'",
		'edit_field_type'=>'select'
	),
	'name'=>array(),
	'identifier'=>array(
		'attr.pattern'=>"^[a-z0-9_-]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required"
	),
	'css_ids'=>array(
		'edit_field_type'=>'select_2_multiple',
		'input_value'=>'id',
		'input_visible'=>'title',
		'select_db'=>$database,
		'select_table'=>'css',
	),
	'js_ids'=>array(
		'edit_field_type'=>'select_2_multiple',
		'input_value'=>'id',
		'input_visible'=>'title',
		'select_db'=>$database,
		'select_table'=>'js',
	),
	'active'=>array(
		'edit_field_type'=>'t_or_f'
	)
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>