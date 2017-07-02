<?php

$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="site_pages";
$single_name="Site Page";
$multiple_name=$single_name."s";
$list_fields=array(
	'site_section_id','name','identifier','title'
);
$search_fields=array(
	'name','identifier','title'
);
$edit_fields=array(
	'site_section_id'=>array(
		'field_name'=>'site_section_id',
		'blank_top_select'=>true,
		'input_value'=>'id',
		'input_visible'=>'zname',
		'select_db'=>$database,
		'select_table'=>'site_sections',
		'sql'=>"SELECT `id`,CONCAT(`site_id`,':',`name`) AS `zname` FROM `".$database."`.`site_sections` WHERE `active`='T' ORDER BY `site_id` ASC,`name` ASC",
		'edit_field_type'=>'select'
	),
	'name'=>array(),
	'identifier'=>array(
		'attr.pattern'=>"^[a-z0-9_-]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required"
	),
	'title'=>array(),
	'description'=>array(
		'edit_field_type'=>'textarea'
	),
	'keywords'=>array(),
	'code'=>array(
		'edit_field_type'=>'textarea'
	),
	'active'=>array(
		'edit_field_type'=>'t_or_f'
	)
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>