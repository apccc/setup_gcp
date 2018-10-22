<?php
if(empty($p)||!is_numeric($p)||!$p) throw new Exception("bad p");
//setting up database table id from $p template
$databaseTableId=(int)$p;

$user=loginModel::requireAdmin();

//get the database table info
$zDatabaseTable=$database_mysqli_local->mysqlidb->getRow($database,'database_tables',$databaseTableId);
if(empty($zDatabaseTable['server'])) throw new Exception("Database Server Entry Not Found! $databaseTableId");

$database=preg_match('/[a-z0-9_]/','',$zDatabaseTable['database']);
$table=preg_match('/[a-z0-9_]/','',$zDatabaseTable['table']);
$single_name=str_replace('_',' ',$table)." entry";
$multiple_name=$single_name." entries";
$list_fields=array();
$search_fields=array();
$edit_fields=array();

$zDatabaseTableFields=$database_mysqli_local->mysqlidb->getRows($database,'database_table_fields',array(
	'database_table_id'=>$databaseTableId,
	'active'=>'T'
	));

if(!empty($zDatabaseTableFields))
	foreach($zDatabaseTableFields as $f)
		if($f['list']==='T')
			$list_fields[]=$f['field'];

if(!empty($zDatabaseTableFields))
	foreach($zDatabaseTableFields as $f)
		if($f['search']==='T')
			$search_fields[]=$f['field'];

if(!empty($zDatabaseTableFields))
	foreach($zDatabaseTableFields as $f)
		if($f['form']==='T')
			$edit_fields[$f['field']]=array(
				'field_name'=>$f['field'],
				'edit_field_type'=>$f['type']
			);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>