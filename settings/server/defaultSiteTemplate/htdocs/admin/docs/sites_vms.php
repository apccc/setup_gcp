<?php
$user=loginModel::requireAdmin();
$database=$SYSTEM_DATABASE;
$table="sites_vms";
$single_name="Site VM";
$multiple_name=$single_name."s";
$list_fields=array(
	'site_id','vm_id'
);
$search_fields=array(
	'site_id','vm_id'
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
	'vm_id'=>array(
		'field_name'=>'vm_id',
		'blank_top_select'=>false,
		'input_value'=>'id',
		'input_visible'=>'name',
		'select_db'=>$database,
		'select_table'=>'googleComputeEngine_VMInstances',
		'select_table_display_where'=>"WHERE `active`='T'",
		'edit_field_type'=>'select'
	)
);
require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>
