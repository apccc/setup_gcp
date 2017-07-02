<?php

$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="googleCloudStorage_backupSchedule";
$single_name="Backup Schedule";
$multiple_name=$single_name."s";
$list_fields=array(
	'server','path','bucket_id','lastRun','nextRun'
);
$search_fields=array(
	'name','path'
);
$edit_fields=array(
	'server'=>array(
		'blank_top_select'=>true,
		'input_value'=>'name',
		'input_visible'=>'name',
		'select_db'=>$database,
		'select_table'=>'googleComputeEngine_VMInstances',
		'edit_field_type'=>'select',
//		'select_table_display_where'=>"WHERE `active`='T'"
	),
	'path'=>array(
		'default_value'=>''
	),
	'pathsToOmit'=>array(
		'default_value'=>'',
		'edit_field_type'=>'textarea',
	),
	'bucket_id'=>array(
		'edit_name'=>'Bucket',
		'field_name'=>'bucket_id',
		'edit_field_type'=>'select',
		'input_value'=>'id',
		'input_visible'=>'name',
		'select_db'=>$database,
		'select_table'=>'googleCloudStorage_buckets',
		'blank_top_select'=>true
	),
	'lastRun'=>array(
		'default_value'=>'0000-00-00 00:00:00'
	),
	'nextRun'=>array(
		'default_value'=>'0000-00-00 00:00:00'
	),
	'runFrequencyDays'=>array(
		'default_value'=>'30'
	)
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>