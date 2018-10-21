<?php
$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="database_table_fields";
$single_name="Database Table Field";
$multiple_name=$single_name."s";
$list_fields=array(
	'database_table_id','field','active','type'
);
$search_fields=array(
	'field'
);
$edit_fields=array(
	'database_table_id'=>array(
		'field_name'=>'database_table_id',
		'blank_top_select'=>false,
		'input_value'=>'id',
		'input_visible'=>'zname',
		'select_db'=>$database,
		'select_table'=>'database_tables',
		'edit_field_type'=>'select',
		'sql'=>"SELECT `id`,CONCAT(`database`,':',`table`) AS `zname` FROM `".$database."`.`database_tables` WHERE `active`='T' ORDER BY `database` ASC, `table` ASC"
	),
	'field'=>array(
		'field_name'=>'field',
		'attr.pattern'=>"^[a-z0-9_]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required"
	),
	'active'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'list'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'search'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'form'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'index'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'unique'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'encrypt'=>array(
		'edit_field_type'=>'t_or_f'
	),
	'type'=>array(
		'field_name'=>'type',
		'attr.pattern'=>"^[a-z0-9_]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required",
		'default_value'=>"text"
	),
	'length'=>array(
		'field_name'=>'length',
		'attr.pattern'=>"^[0-9]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required",
		'default_value'=>"100"
	),
	'priority'=>array(
		'field_name'=>'priority',
		'attr.pattern'=>"^[0-9]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required",
		'default_value'=>"100"
	),
);


//add database server table fields
if(!empty($_POST['add'])&&!empty($_POST['database_table_id'])&&!empty($_POST['field'])){
	//get the database instance variable name
	$zDatabaseTable=$database_mysqli_local->mysqlidb->getRow($database,'database_tables',(int)$_POST['database_table_id']);
	if(empty($zDatabaseTable['server'])) throw new Exception("Database Server Entry Not Found! d: ".$_POST['database_table_id']);
	$s=preg_replace('[^a-z0-9_]','',$zDatabaseTable['server']);
	$dbMysqli=${"database_mysqli_$s"};
	if($zDatabaseTable['database']&&$zDatabaseTable['table']&&isset($dbMysqli)&&is_object($dbMysqli))
		$dbMysqli->mysqlidb->createDatabaseTableField($zDatabaseTable['database'],$zDatabaseTable['table'],$_POST['field'],$_POST);
}


require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>