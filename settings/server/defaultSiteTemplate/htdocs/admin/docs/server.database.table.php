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
$single_name=$server." : ".str_replace('_',' ',$database)." : ".str_replace('_',' ',$table)." : entry";
$multiple_name=$server." : ".str_replace('_',' ',$database)." : ".str_replace('_',' ',$table)." : entries";
$list_fields=array();
$search_fields=array();
$edit_fields=array();

if(!isset($dbMysqli)||!is_object($dbMysqli))
	throw new Exception("dbMysqli object not found for server $server");

$sql="SELECT * FROM `".$SYSTEM_DATABASE."`.`database_table_fields` WHERE `database_table_id`='".(int)$databaseTableId."' AND `active`='T' ORDER BY `priority` ASC";
$zDatabaseTableFields=$database_mysqli_local->mysqlidb->getRowsFromQuery($sql);

if(!empty($zDatabaseTableFields))
	foreach($zDatabaseTableFields as $f)
		if($f['list']==='T')
			$list_fields[]=$f['field'];

if(!empty($zDatabaseTableFields))
	foreach($zDatabaseTableFields as $f)
		if($f['search']==='T')
			$search_fields[]=$f['field'];

$encryptionRequired=false;
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

		if($f['type']==='text'&&(int)$f['length']>255)
			$edit_fields[$f['field']]['edit_field_type']='textarea';
		elseif($f['type']==='image'||$f['type']==='file')
			$edit_fields[$f['field']]['edit_field_type']='file_raw';
		
		if($f['form']!=='T')
			$edit_fields[$f['field']]['edit_field_type']='none';

		if($f['encrypt']==='T'){
			$encryptionRequired=true;
			$edit_fields[$f['field']]['MYSQL_AES_KEY']=$MYSQL_AES_KEY;
			$edit_fields[$f['field']]['keyEncryptVersion']=2;
		}

		if($f['reference_table']){
			$zReferenceDatabaseTable=$database_mysqli_local->mysqlidb->getRow($SYSTEM_DATABASE,'database_tables',(int)$f['reference_table']);
			$edit_fields[$f['field']]=array(
				'field_name'=>$f['field'],
				'blank_top_select'=>true,
				'input_value'=>'id',
				'input_visible'=>'moniker',
				'select_db'=>$zReferenceDatabaseTable['database'],
				'select_table'=>$zReferenceDatabaseTable['table'],
				'edit_field_type'=>'select'
			);
		}
	}
}

if($encryptionRequired){
	$edit_fields['nonce']=array(
			'field_name'=>'nonce',
			'edit_field_type'=>'nonce'
	);
}

if($server!='local')
	$template_database_mysqli_alternate=$dbMysqli;

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>
