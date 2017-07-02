<?php

$user=loginModel::requireAdmin();

$database=$SYSTEM_DATABASE;
$table="googleComputeEngine_VMInstances";
$single_name="VM Instance";
$multiple_name=$single_name."s";
$list_fields=array(
	'name','zone','region','machine_type'
);
$search_fields=array(
	'name'
);
$edit_fields=array(
	'name'=>array(
		'default_value'=>'',
		'attr.pattern'=>"^[a-z][a-z0-9-]{1,32}$",
		'attr.class'=>"validate",
		'attr.required'=>"required"
	),
	'description'=>array(
		'default_value'=>'',
		'edit_field_type'=>'textarea'
	),
	'zone'=>array(
		'edit_field_type'=>'radio',
		'default_value'=>'us-west1-a',
		'radios'=>array(
			0=>array(
				'display'=>'europe-west1-a',
				'value'=>'europe-west1-a',
			),
			1=>array(
				'display'=>'europe-west1-b',
				'value'=>'europe-west1-b',
			),
			2=>array(
				'display'=>'us-central1-a',
				'value'=>'us-central1-a',
			),
			3=>array(
				'display'=>'us-central1-b',
				'value'=>'us-central1-b',
			),
			4=>array(
				'display'=>'us-west1-a',
				'value'=>'us-west1-a',
			)
		)
	),
	'region'=>array(
		'edit_field_type'=>'radio',
		'default_value'=>'us-west1',
		'radios'=>array(
			0=>array(
				'display'=>'us-central1',
				'value'=>'us-central1',
			),
			1=>array(
				'display'=>'us-west1',
				'value'=>'us-west1',
			),
			2=>array(
				'display'=>'europe-west1',
				'value'=>'europe-west1',
			)
		)
	),
	'machine_type'=>array(
		'edit_field_type'=>'radio',
		'default_value'=>'f1-micro',
		'radios'=>array(
			0=>array(
				'display'=>'g1-small (1 CPU; 1740MB RAM)',
				'value'=>'g1-small',
			),
			1=>array(
				'display'=>'n1-standard-1 (1 CPU; 3840MB RAM)',
				'value'=>'n1-standard-1',
			),
			2=>array(
				'display'=>'f1-micro (1 CPU; 614MB RAM)',
				'value'=>'f1-micro',
			),
			3=>array(
				'display'=>'n1-highcpu-2 (2 CPUS; 1843MB RAM)',
				'value'=>'n1-highcpu-2',
			),
			4=>array(
				'display'=>'n1-standard-2 (2 CPUS; 7680MB RAM)',
				'value'=>'n1-standard-2',
			),
			5=>array(
				'display'=>'n1-highmem-2 (2 CPUS; 13312MB RAM)',
				'value'=>'n1-highmem-2',
			),
			6=>array(
				'display'=>'n1-highcpu-4 (4 CPUS; 3686MB RAM)',
				'value'=>'n1-highcpu-4',
			),
			7=>array(
				'display'=>'n1-highmem-4 (4 CPUS; 26624MB RAM)',
				'value'=>'n1-highmem-4',
			),
			8=>array(
				'display'=>'n1-standard-4 (4 CPUS; 15360MB RAM)',
				'value'=>'n1-standard-4',
			),
			9=>array(
				'display'=>'n1-highmem-8 (8 CPUS; 53248MB RAM)',
				'value'=>'n1-highmem-8',
			),
			10=>array(
				'display'=>'n1-highcpu-8 (8 CPUS; 7373MB RAM)',
				'value'=>'n1-highcpu-8',
			),
			11=>array(
				'display'=>'n1-standard-8 (8 CPUS; 30720MB RAM)',
				'value'=>'n1-standard-8',
			),
		)
	),
	'active'=>array(
		'edit_field_type'=>'t_or_f'
	)
);

require_once dirname(__FILE__)."/template.php";
$admin_class=new admin_doc_class;
?>