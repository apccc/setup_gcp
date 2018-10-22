<?php
if(empty($p)||!is_numeric($p)||!$p) throw new Exception("bad p");
//setting up database table id from $p template
$databaseTableId=(int)$p;

$user=loginModel::requireAdmin();

//get the database table info
$zDatabaseTable=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'database_tables',$databaseTableId);
if(empty($zDatabaseTable['server'])) throw new Exception("Database Server Entry Not Found! $databaseTableId");

$server=preg_replace('[^a-z0-9_]','',$zDatabaseTable['server']);
$database=preg_replace('/[^a-z0-9_]/','',$zDatabaseTable['database']);
$table=preg_replace('/[^a-z0-9_]/','',$zDatabaseTable['table']);
$dbMysqli=${"database_mysqli_$server"};
$single_name=str_replace('_',' ',$table)." entry";
$multiple_name=$single_name." entries";
$list_fields=array();
$search_fields=array();
$edit_fields=array();

if(!isset($dbMysqli)||!is_object($dbMysqli))
	throw new Exception("dbMysqli object not found for server $server");

$zDatabaseTableFields=$database_mysqli_local->mysqlidb->getRows($SYSTEM_DATABASE,'database_table_fields',array(
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

if(!empty($zDatabaseTableFields)){
	foreach($zDatabaseTableFields as $f){
		$edit_fields[$f['field']]=array(
			'field_name'=>$f['field']
		);
		if(!empty($f['type']))
			$edit_fields[$f['field']]['edit_field_type']=$f['type'];

		if($f['type']==='int'){
			$edit_fields[$f['field']]['attr.pattern']='^[0-9]*$';
			$edit_fields[$f['field']]['attr.class']='validate';
			$edit_fields[$f['field']]['edit_field_type']='text';
		}

		if($f['form']!=='T')
			$edit_fields[$f['field']]['edit_field_type']='none';
	}
}
if($server!='local')
	$template_database_mysqli_alternate=$dbMysqli;

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>