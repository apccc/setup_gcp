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
		'edit_field_type'=>'t_or_f',
		'default'=>'T',
	),
	'controls'=>array(
		'edit_field_type'=>'t_or_f',
		'default'=>'T'
	),
	'server'=>array(
		'field_name'=>'server',
		'attr.pattern'=>"^[a-z0-9_]+$",
		'attr.class'=>"validate",
		'attr.required'=>"required",
		'default_value'=>"local"
	)
);

//add database server table
if(!empty($_POST['add'])&&!empty($_POST['database'])&&!empty($_POST['server'])&&!empty($_POST['table'])){
  $d=preg_replace('/[^a-z0-9_]/','',$_POST['database']);
  $t=preg_replace('/[^a-z0-9_]/','',$_POST['table']);
  $s=preg_replace('/[^a-z0-9]/','',$_POST['server']);
  if($d===$_POST['database']&&$s===$_POST['server']&&$t===$_POST['table']){
	//get the database instance variable name
    $dbMysqli=${"database_mysqli_$s"};
    if(isset($dbMysqli)&&is_object($dbMysqli))
      $dbMysqli->mysqlidb->createDatabaseTable($d,$t);
  }
}

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>