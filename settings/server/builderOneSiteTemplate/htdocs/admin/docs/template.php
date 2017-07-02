<?php
/**
* Template Admin Control Class for Controlling Admin Database Tables
*/
class admin_doc_class
{
	/**
	* var to override the authentication
	*/
	protected $adminAuthOverride=false;
	/**
	* Construct the admin doc template
	* @param array $options - Some options
	* 	array(
			'adminAuthOverride'=>true - if set to true then we will assume the user is authenticated and not check again within this template framework
		)
	*/
	public function __construct($options=array())
	{
		$this->primary_field=(!empty($options['primary_field'])&&is_string($options['primary_field'])?$options['primary_field']:"id");
		if(isset($options['adminAuthOverride'])&&$options['adminAuthOverride']===true) $this->adminAuthOverride=true;
		if($this->_check_global_variables())
		{
			if(isset($_GET['delete'])&&is_numeric($_GET['delete']))
				$this->_delete();

			if(isset($_POST['delete_multiple'])&&$_POST['delete_multiple'])
				$this->_delete_multiple();

			if(isset($_GET['dupe'])&&is_numeric($_GET['dupe']))
				$this->_duplicate();

			if(isset($_POST['add'])&&$_POST['add'])
			{
				$this->_insert();
				$_POST['update']=1;
			}

			if(isset($_POST['update'])&&$_POST['update'])
				$this->_update();

			if((isset($_GET['id'])&&is_numeric($_GET['id']))||(isset($_GET['new'])&&$_GET['new']))
			{
				if(isset($_GET['id'])&&is_numeric($_GET['id']))
					$this->id=$_GET['id'];

				if(!$this->_show_single())
					$this->_show_multiple();
			}
			else
			{
				$this->_show_multiple();
			}
			if(is_array($this->messages))
			{
				if(count($this->messages))
				{
					$this->_setup_messages();
				}
			}
		}
	}

	private function _setup_messages()
	{
		$out="";
		if(count($this->messages))
		{
			$out.="<ul id='messages_ul'>";
			foreach($this->messages as $message)
			{
				$out.="<li>".date('Y-m-d H:i:s').": ".$message."</li>";
			}
			$out.="</ul>";
			$this->output_content=$out.$this->output_content;
		}
	}

	private function _css($parts=NULL)
	{
		$css="<style>\n";
		if(is_array($parts)){
			if(in_array('global',$parts)){
				$css.="#messages_ul{list-style:none;margin:0px;padding:0px;text-align:center;}";
				$css.="#messages_ul li{background-color:#CCCCCC;padding:5px;width:400px;font-weight:bold;}";
				$css.="#title_div{font-weight:bold;padding:10px;float:left;clear:both;color:#222277;}";
				$css.="#title_div a{color:#222277;}";
				$css.="form{margin:0px;padding:0px;}";
				$css.="#search_form{float:left;padding:5px;}";
			}
			if(in_array('multiple',$parts)){
				$css.="#multiple_table{width:100%;clear:both;}";
				$css.="#multiple_table td{padding:10px;border:margin:1px;font-size:14px;}";
				$css.="#multiple_table thead{font-weight:bold;background-color:#CCCCCC}";
				$css.="#multiple_table thead a{color:#222277;}";
				$css.="#new_td{width:50px;}";
				$css.="#multiple_table tbody .edit_del_td a{color:#222277;}";
				$css.="#multiple_table tbody .edit_del_td{font-size:12px;width:50px;}";
				$css.="#multiple_table tbody .inactive{background-color:#FF5555;}";
				$css.="#multiple_table tbody {cursor:pointer;}";
				$css.="#multiple_table tbody .odd{background-color:#EEEEEE;}";
				$css.="#multiple_table tbody .even{background-color:#DDDDDD;}";
				$css.="#multiple_table tbody .tr_hover{background-color:#555555;color:#FFFFFF;}";
				$css.="#multiple_table tbody .tr_hover a{color:#c3c3ff;}";
			}
			if(in_array('single',$parts)){
				$css.=""
					."#single_table{width:100%;clear:both;}"
					."#single_table .input_name{width:100px;font-weight:bold;font-size:12px;}"
					."#single_table td{padding:10px;background-color:#EEEEEE;margin:1px;font-size:14px;}"
					."input.validate:valid{border: 1px solid #cfc;}"
					."input.validate:focus:valid{border: 1px solid #0f0;background-color: #cfc;}"
					."input.validate:invalid{border: 1px solid #f00;background-color: #fcc;}"
					."div.attrPatternNote{font-size: 0.8em;font-style: italic;}"
				;
			}
		}
		$css.="\n</style>";
		return $css;
	}

	private function _show_multiple()
	{
		global $echo_bot_javascript;
		if(is_array($this->list_fields))
		{
			$defaultLimitValue=500;

			global $go_to_row_url;
			global $hijack_new_link;

			$usePHPResultOrdering=false;
			if(isset($_GET['o'])&&isset($this->list_fields[$_GET['o']]['usePHPResultOrdering'])&&$this->list_fields[$_GET['o']]['usePHPResultOrdering'])
			{
				$usePHPResultOrdering=true;
				$usePHPResultOrderingKey=$this->list_fields[$_GET['o']]['usePHPResultOrdering'];
			}

			$limitValue=(isset($_GET['l'])&&is_numeric($_GET['l'])?$_GET['l']:$defaultLimitValue);
			$page=(isset($_GET['page'])&&is_numeric($_GET['page'])&&$_GET['page']>=1?$_GET['page']:1);
			$start=($page-1)*$limitValue;
			$limit=" LIMIT ".$start.",".$limitValue;

			//GET RESULTS
			//setup the sql string variables
			$sql="";
			$select="SELECT `".$this->table."`.*";
			//look for special fields in the edit fields
			foreach($this->list_fields as $field)
			{
				if(is_array($field)&&isset($field['name']))
					$fieldName=$field['name'];
				else
					$fieldName=$field;

				//looking for mysql_compressed and decompress
				if(isset($this->edit_fields[$fieldName]['MYSQL_COMPRESSED'])&&$this->edit_fields[$fieldName]['MYSQL_COMPRESSED'])
					$select.=",UNCOMPRESS(`".$fieldName."`) AS `".$fieldName."`";
			}
			$from=" FROM `".$this->database."`.`".$this->table."` ";
			$where="";

			$orderBy="ORDER BY ";

			if(isset($_GET['o'])&&$_GET['o']&&!$usePHPResultOrdering)
				$orderBy.=$_GET['o'];
			else
				$orderBy.="`".$this->table."`.`".$this->primary_field."`";

			$orderBy.=" ";

			if(isset($_GET['d'])&&$_GET['d']=='ASC')
			{
				$d="DESC";
				$orderByD="ASC";
			}
			else
			{
				$d="ASC";
				$orderByD="DESC";
			}
			$orderBy.=$orderByD;

			//build the select and where from the search fields
			if(is_array($this->search_fields)&&count($this->search_fields))
			{
				//check for relational database conjoining
				foreach($this->search_fields as $field)
				{
					if(!(isset($this->edit_fields[$field]['relationship_table'])&&isset($this->edit_fields[$field]['relationship_field'])&&isset($this->edit_fields[$field]['select_db'])&&isset($this->edit_fields[$field]['select_table'])))
						continue;
					$select.=",`".$this->edit_fields[$field]['relationship_table']."`.`".$this->edit_fields[$field]['relationship_field']."` ";
					$from.=",("
						."SELECT 0 AS `".$this->edit_fields[$field]['input_value']."`,'' AS `".$this->edit_fields[$field]['relationship_field']."`"
						." UNION ALL "
						."(SELECT `".$this->edit_fields[$field]['input_value']."`,`".$this->edit_fields[$field]['input_visible']."` AS `".$this->edit_fields[$field]['relationship_field']."` FROM `".$this->edit_fields[$field]['select_db']."`.`".$this->edit_fields[$field]['select_table']."`"
						.($usePHPResultOrdering&&$_GET['o']==$this->edit_fields[$field]['relationship_field']?" ORDER BY `".$this->edit_fields[$field]['input_visible']."` ".$orderByD." ".$limit:"")
						.")"
						.") AS `".$this->edit_fields[$field]['relationship_table']."` ";
					$where.=""
						.(!$where?"WHERE "
							:"AND "
						)
						."`".$this->edit_fields[$field]['relationship_table']."`.`".$this->edit_fields[$field]['input_value']."`=`".$this->table."`.`".$field."` "
					;
				}

				//build on the search_fields
				if(isset($_GET['q'])&&$_GET['q'])
				{
					$i=0;
					foreach($this->search_fields as $field)
					{
						if(!(isset($where)&&$where))
							$where="WHERE (";
						elseif(!$i)
							$where.="AND (";
						else
							$where.=" OR ";

						$where.=""
							.(isset($this->edit_fields[$field]['MYSQL_COMPRESSED'])&&$this->edit_fields[$field]['MYSQL_COMPRESSED']?""
								."UNCOMPRESS(`".$field."`)"
								:"`".$field."`"
							)
							."LIKE'%".$this->database_mysqli->mysqlidb->real_escape_string(stripslashes($_GET['q']))."%'";
						$i++;
					}
					if($i) $where.=")";
				}
			}
			$sql.=$select;
			$sql.=$from;
			$sql.=(isset($where)&&$where?$where:"");


			$sql.=$orderBy;
			$sql.=$limit;

			if($usePHPResultOrdering)//ordering must be wrapped or database server lags
				$sql="SELECT * FROM (".$sql.") AS `tmp1` ORDER BY ".$_GET['o']." ".$orderByD;

			$multipleResult=$this->database_mysqli->mysqlidb->getRowsFromQuery($sql);
			if($usePHPResultOrdering)
			{
				//
			}
			$multipleResultCount=count($multipleResult);

			//SHOW THE PAGE
			if(!isset($this->output_content)) $this->output_content="";
			$this->output_content.=""
				.$this->_css(array('global','multiple'))
				."<div id='title_div'>"
					.$this->multiple_name.""
					." "
					."<font style='font-size:11px;'>("
						.($page>1||$multipleResultCount>=$limitValue?"Page ".$page." : ":"")
						.$multipleResultCount." showing"
					.")</font>"
				."</div>"
			;
			if(is_array($this->search_fields)&&count($this->search_fields))
			{
				$this->output_content.=""
					."<form method='get' action='' id='search_form'><input type='hidden' name='p' value='".$_GET['p']."' />"
						."<select name='l' style='float:right;'>"
				;
				foreach(array($defaultLimitValue/100,$defaultLimitValue/10,$defaultLimitValue,$defaultLimitValue*10,$defaultLimitValue*100,$defaultLimitValue*1000) as $l) $this->output_content.="<option value='".$l."'".($limitValue==$l?" selected":"").">LIMIT ".number_format($l,0,'.',',')."</option>";
				$this->output_content.=""
						."</select>"
						."<input type='text' name='q' value=\"".(isset($_GET['q'])?htmlspecialchars(stripslashes($_GET['q'])):"")."\" />"
						."<input type='hidden' name='o' value='".(isset($_GET['o'])?$_GET['o']:"")."' />"
						."<input type='hidden' name='d' value='".(isset($_GET['d'])?$_GET['d']:"")."' />"
						."<input type='submit' value='search' />"
					."</form>"
				;
			}
			$this->output_content.=""
				."<form action='' method='post'><table id='multiple_table'>"
					."<thead><tr><td id='new_td'><b><a href='"
			;

			if($hijack_new_link)
				$this->output_content.=$hijack_new_link;
			else
				$this->output_content.="?p=".$_GET['p']."&new=1";

			$this->output_content.="'>NEW</a></b></td>";

			$this->output_content.=""
				."<td align='center'>"
				."<input type='submit' name='delete_multiple' value='Delete' onclick=\"return confirm('Delete Multiple?');\" />"
				."</td>"
			;

			foreach($this->list_fields as $field)
			{
				if(is_array($field))
				{
					if(isset($field['sortName']))
						$fieldName=$field['sortName'];
					else
						$fieldName=$field['name'];
				}
				else
				{
					$fieldName=$field;
				}

				$this->output_content.=""
					."<td>"
						."<a href='"
							."?p=".(isset($_GET['p'])?$_GET['p']:"")
							.(isset($_GET['q'])?"&q=".$_GET['q']:"")
							.(isset($fieldName)?"&o=".$fieldName:"")
							.(isset($d)?"&d=".$d:"")
							.(isset($_GET['l'])?"&l=".$_GET['l']:"")
							."'>"
							.ucwords(str_replace("_"," ",$fieldName))
						."</a>"
					."</td>"
				;
			}

			if($go_to_row_url) $this->output_content.="<td>GoTo</td>";

			$this->output_content.="</thead>";

			//SHOW RESULTS
			if($multipleResultCount)
			{
				$this->output_content.="<tbody>";
				foreach($multipleResult as $r)
				{
					$row_class=(isset($row_class)&&$row_class=='odd')?'even':'odd';
					$this->output_content.="<tr class='";

					if(isset($r['active'])&&$r['active']=='F')
						$this->output_content.="inactive";
					else
						$this->output_content.=$row_class;

					$this->output_content.="'";
					global $row_color_flags;
					if(is_array($row_color_flags))
						if(count($row_color_flags))
							foreach($row_color_flags as $key => $val)
								if($r[$key]==$val['value_trigger'])
									$this->output_content.=" style='background-color:".$val['color'].";'";

					$this->output_content.=""
						."><td class='edit_del_td'>"
						."<a href='".$this->reGet()."&id=".$r[$this->primary_field]."' class='edit_link'>"
						."Edit"
						."</a>"
						."<br />"
						."<a href='".$this->reGet()."&delete=".$r[$this->primary_field]."' onclick=\"return confirm('Delete ID#".(isset($r['id'])?$r['id']:"")."?');\" class='delete_link'>"
						."Delete"
						."</a>"
						."<br />"
						."<a href='".$this->reGet()."&dupe=".$r[$this->primary_field]."' onclick=\"return confirm('Duplicate ID#".(isset($r['id'])?$r['id']:"")."?');\" class='dupe_link'>"
						."Duplicate"
						."</a>"
						."</td>"
						."<td align='center'><input class='delete_checkbox' type='checkbox' name='delete_id[]' value='".$r[$this->primary_field]."' /></td>"
						."";
					foreach($this->list_fields as $field)
					{
						if(is_array($field))
							$fieldName=$field['name'];
						else
							$fieldName=$field;

						$fieldValue=(isset($fieldName)&&$fieldName&&isset($r[$fieldName])?$r[$fieldName]:"");
						$this->output_content.=""
							."<td>"
								.(is_array($field)&&is_string($field['evalFieldValue'])
									?eval($field['evalFieldValue'])
									:htmlspecialchars($fieldValue)
								)
							."</td>"
						;
					}
					//go to row click
					if($this_url=$go_to_row_url)
					{
						//match fields and replace e.g. https://example.com/whatever/{id}/{folder}
						preg_match_all('/\{([a-zA-Z0-9_.-]+)\}/',$go_to_row_url,$matches);
						foreach($matches[1] as $match)
						{
							$this_url=str_replace('{'.$match.'}',$r[$match],$this_url);
						}
						$this->output_content.="<td><a href='".$this_url."'>Go To</a></td>";
					}
				}
				$this->output_content.="</tbody>";
			}
			
			$this->output_content.=""
				."</table>"
				."<style type='text/css'>"
					."#adminMultRowsNav{"
						."height:30px;"
					."}"
					."#adminMultRowsNav>a{"
						."display:block;"
						."float:left;"
						."text-decoration:none;"
						."color:#999;"
						."padding:5px 15px;"
						."margin:5px;"
						."box-shadow:1px 1px 1px 1px #CCC;"
						."background-color:#EEE;"
						."border:1px solid #DDD;"
					."}"
					."#adminMultRowsNav>a:hover{"
						."color:#BBB;"
						."background-color:#DDD;"
						."border:1px solid #CCC;"
					."}"
				."</style>"
				."<div id='adminMultRowsNav'>"
					.($page&&$page>1
						?""
						."<a href='".$this->reGet(array('page'))."&page=".($page-1)."'>Previous</a>"
						." "
						."<a href='".$this->reGet(array('page'))."&page=".($page-1)."'>".($page-1)."</a>"
						:""
					)
					." <a href='' style='background-color:#FFF;'>".$page."</a> "
					.($multipleResultCount>=$limitValue
						?""
						."<a href='".$this->reGet(array('page'))."&page=".($page+1)."'>".($page+1)."</a>"
						." "
						."<a href='".$this->reGet(array('page'))."&page=".($page+1)."'>Next</a>"
						:""
					)
				."</div>"
			;
			zInterface::addBotJS(""
				//CONTROL CLICKS ON ROWS
				."\$(document).ready(function(){"
					."\$('#multiple_table tbody tr').hover(function(){"
						."\$(this).addClass('tr_hover');"
					."},function(){"
						."\$(this).removeClass('tr_hover');"
					."}).click(function(){"
						."document.location=\$(this).find('.edit_link:first').attr('href');"
					."});"
					."\$('#multiple_table .edit_del_td a').click(function(event){"
						."event.stopPropagation();"
					."});"
					."\$('#multiple_table .delete_checkbox').click(function(e){"
					."e.stopPropagation();"
					."});"
				."});"
			);
		}
	}

	private function _show_single()
	{
		if(is_array($this->edit_fields)){
			if(!isset($this->output_content)) $this->output_content="";
			$this->output_content.=$this->_css(array('global','single'));
			if(isset($this->id)&&is_numeric($this->id))
			{
				$this->single=array();
				$sql="SELECT *";

				#check for special field selections
				if(!empty($this->edit_fields))
					foreach($this->edit_fields as $key => $field)
					{
						#check for AES ENCRYPTED FIELDS
						if(!empty($field['MYSQL_AES_KEY']))
							$sql.=""
								.",AES_DECRYPT(".$field['field_name'].","
								.(!empty($this->edit_fields['nonce'])&&isset($field['keyEncryptVersion'])?""
									.($field['keyEncryptVersion']==1?"SHA1(CONCAT('".$field['MYSQL_AES_KEY']."',`nonce`))":"")
									:"'".$field['MYSQL_AES_KEY']."'"
								)
								.") AS `".$field['field_name']."_AES_DECRYPTED`"
							;
						#check for MYSQL COMPRESSED
						elseif(!empty($field["MYSQL_COMPRESSED"])&&$field['MYSQL_COMPRESSED'])
							$sql.=",UNCOMPRESS(".$field['field_name'].") AS `".$field['field_name']."`";
					}

				$sql.=" FROM `".$this->database."`.`".$this->table."` WHERE `".$this->primary_field."`='".$this->id."' LIMIT 1";
				$result=$this->database_mysqli->mysqlidb->getRowsFromQuery($sql);
				if(count($result))
				{
					foreach($result as $r)
						foreach($r as $k=>$v)
							$this->single[$k]=$v;
				}
				else
				{
					$this->messages[]=$this->single_name." with ID# ".$this->id." NOT FOUND";
					return false;
				}
			}
			$this->output_content.=""
			."<div id='title_div'><a href='".$this->reGet()."'>".$this->multiple_name."</a></div>"
			."<form action='' method='post' enctype='multipart/form-data'><table id='single_table'>";
			if(count($this->edit_fields))
			{
				foreach($this->edit_fields as $key => $field)
				{
					if((isset($field['hideWhenNoId'])&&$field['hideWhenNoId'])&&!$this->id) continue;
					$this->output_content.=$this->show_edit_field($key);
				}
			}
			if(isset($this->image_fields)&&is_array($this->image_fields))
			{
				if(count($this->image_fields))
				{
					foreach($this->image_fields as $key => $field)
					{
						if(isset($field['hideWhenNoId'])&&$field['hideWhenNoId']&&!$this->{'id'}) continue;
						$this->output_content.=$this->show_image_field($key);
					}
				}
			}
			$this->output_content.="<tfoot><tr><td colspan='2'><input type='submit' ";

			if(isset($_GET['new'])&&$_GET['new'])
				$this->output_content.="name='add' value=' ADD '";
			elseif(is_numeric($_GET['id']))
				$this->output_content.="name='update' value=' UPDATE '";

			$this->output_content.=""
				." /></td></tr></tfoot>"
				.(isset($this->id)&&is_numeric($this->id)?"<input type='hidden' name='id' value='".$this->id."' />":"")
				."</table></form>"
				.(isset($this->{'outsideSingleFormBottom'})?$this->{'outsideSingleFormBottom'}:'')
			;
			return true;
		}
		return false;
	}

	private function _insert()
	{
		if($this->database&&$this->table&&is_object($this->database_mysqli))
		{
			$this->id=$this->database_mysqli->mysqlidb->insertInto($this->database,$this->table,array());
			if($this->id)
			{
				$this->messages[]=$this->single_name." ".$this->id." ADDED";
				$this->record_activity("ADDED ".$this->id);
			}
			return $this->id;
		}
	}

	private function _update()
	{
		if(isset($this->id)&&is_numeric($this->id))
			$id=$this->id;
		elseif(isset($_POST['id'])&&is_numeric($_POST['id']))
			$id=$_POST['id'];
		elseif(isset($_GET['id'])&&is_numeric($_GET['id']))
			$id=$_GET['id'];
		else
			$this->messages[]=$this->single_name." UPDATE FAILED: NO ID";

		if(is_array($this->edit_fields)&&is_object($this->database_mysqli)&&is_array($_POST)&&is_numeric($id))
		{
			//UPDATE THE ENTRY
			$sql="UPDATE `".$this->database."`.`".$this->table."` SET ";
			$i=0;
			foreach($this->edit_fields as $field)
			{
				if((isset($field['edit_field_type'])&&$field['edit_field_type']=='read_only')||(isset($field['noMySQL'])&&$field['noMySQL']))
				{
					continue;
				}
				elseif(isset($field['edit_field_type'])&&in_array($field['edit_field_type'],array('select_2_multiple','select_multiple'))&&isset($_POST[$field['field_name']])&&is_array($_POST[$field['field_name']]))
				{
					$_POST[$field['field_name']]=implode(",",$_POST[$field['field_name']]);
				}
				elseif((isset($field['edit_field_type'])&&$field['edit_field_type']=='serialize')||(isset($field['serialize'])&&$field['serialize']))
				{
					$_POST[$field['field_name']]=serialize($_POST[$field['field_name']]);
				}
				elseif(isset($field['edit_field_type'])&&$field['edit_field_type']=='file_base64')
				{
					if(!isset($_FILES[$field['field_name']]['tmp_name'])||!is_file($_FILES[$field['field_name']]['tmp_name']))
						continue;
					if($_POST[$field['field_name']]=base64_encode(file_get_contents($_FILES[$field['field_name']]['tmp_name'])))
						unlink($_FILES[$field['field_name']]['tmp_name']);
				}

				if($i)
					$sql.=",";

				if(isset($field['_update_string_eval']))
					$sql.=eval($field['_update_string_eval']);
				elseif(!empty($field['MYSQL_AES_KEY']))
					$sql.=""
						."`".$field['field_name']."`=AES_ENCRYPT('".$this->database_mysqli->mysqlidb->real_escape_string($_POST[$field['field_name']])."','"
						.(!empty($_POST['nonce'])&&isset($field['keyEncryptVersion'])?""
							.($field['keyEncryptVersion']==1?$this->buildAESKeyFromKeyAndNonceV1($field['MYSQL_AES_KEY'],$_POST['nonce']):"")
							:$field['MYSQL_AES_KEY']
						)
						."') "
					;
				elseif(!empty($field['MYSQL_COMPRESSED'])&&$field['MYSQL_COMPRESSED'])
					$sql.="`".$field['field_name']."`=COMPRESS('".$this->database_mysqli->mysqlidb->real_escape_string((isset($_POST[$field['field_name']])?$_POST[$field['field_name']]:""))."') ";
				else
					$sql.="`".$field['field_name']."`='".$this->database_mysqli->mysqlidb->real_escape_string((isset($_POST[$field['field_name']])?$_POST[$field['field_name']]:""))."' ";
				$i++;
			}
			$sql.="WHERE `".$this->primary_field."`='".$id."' LIMIT 1";
			if($this->database_mysqli->mysqlidb->query($sql))
			{
				$_GET['id']=$this->id=$id;
				unset($_GET['new']);
				$this->messages[]=$this->single_name." UPDATED";
				$this->record_activity($sql);
			}
			else
			{
				$this->messages[]=$this->single_name." UPDATE FAILED: ".$sql." : ERROR: ".$this->database_mysqli->mysqlidb->{'error'};
			}
			//DONE WITH UPDATING THE ENTRY

			//UPDATE THE IMAGES
			if(isset($this->image_fields)&&is_array($this->image_fields))
			{
				if(count($this->image_fields))
				{
					foreach($this->image_fields as $key => $field)
					{
						if(isset($_POST['image_delete_'.$key])&&strlen($_POST['image_delete_'.$key]))
						{
							if(is_file($_POST['image_delete_'.$key]))
							{
								unlink($_POST['image_delete_'.$key]);
								$this->messages[]="IMAGE DELETED: ".$_POST['image_delete_'.$key];
							}
						}

						if($_FILES['image_'.$key]['size']>0)
						{
							$srcImageTmpName=$_FILES['image_'.$key]['tmp_name'];
							$file_acceptable=true;
							if($field['extension'])
							{
								$ext_start=-strlen($field['extension']);
								$ext_match=strtolower(substr($_FILES['image_'.$key]['name'],$ext_start));
								$lowRequiredExtension=strtolower($field['extension']);
								if($lowRequiredExtension!=$ext_match)
								{
									//convert file
									list($iWidth,$iHeight,$type,$attr)=getimagesize($srcImageTmpName);

									if(in_array($lowRequiredExtension,array('.jpg','.png'))&&in_array($ext_match,array('.jpg','.png','jpeg','.gif'))&&$iWidth&&$iHeight)
										$canConvert=true;
									else
										$canConvert=false;

									if($canConvert)
									{
										require_once '/aglob/class/createProportionateThumb.php';
										$dest='/tmp/temporaryImage'.date('YmdHis').$lowRequiredExtension;
										if($lowRequiredExtension=='.jpg')
											createProportionateThumb::jpg($srcImageTmpName,$dest,$iWidth,$iHeight);
										elseif($lowRequiredExtension=='.png')
											createProportionateThumb::png($srcImageTmpName,$dest,$iWidth,$iHeight);

										if(is_file($srcImageTmpName)) unlink($srcImageTmpName);
										$srcImageTmpName=$dest;

										if(is_file($srcImageTmpName)) $imageConverted=true;
									}

									if(isset($imageConverted)&&$imageConverted)
									{
										$file_acceptable=true;
									}
									else
									{
										$file_acceptable=false;
										$this->messages[]=$_FILES['image_'.$key]['name']." Format Not Accepted";
									}
								}
							}
							if($file_acceptable)
							{
								$filename=str_replace('{id}',$id,$field['file_name_format']);
								$filefolder=$field['folder'];
								foreach($this->edit_fields as $f)
								{
									$filefolder=str_replace("{".$f['field_name']."}",$_POST[$f['field_name']],$filefolder);
									$filename=str_replace("{".$f['field_name']."}",$_POST[$f['field_name']],$filename);
								}

								$dst=$filefolder."/".$filename;

								if(copy($srcImageTmpName,$dst))
									$this->messages[]="Image Upload Success: ".$dst;
								else
									$this->messages[]="Image Upload Failure: ".$dst;

								if(is_file($srcImageTmpName)) unlink($srcImageTmpName);
							}
						}
					}
				}
			}
			//END UPDATE THE IMAGES
		}
		else
		{
			$this->messages[]=$this->single_name." UPDATE FAILED: CONFIGURATION";
		}
	}

	private function _delete()
	{
		if(is_numeric($_GET['delete']))
		{
			$sql="DELETE FROM `".$this->database."`.`".$this->table."` WHERE `".$this->primary_field."`='".$_GET['delete']."' LIMIT 1";
			if($this->database_mysqli->mysqlidb->query($sql))
			{
				$this->messages[]=$this->single_name." DELETED";
				$this->record_activity($sql);
			}
		}
	}

	private function _duplicate()
	{
		if(is_numeric($_GET['dupe']))
		{
			$sql="INSERT INTO `".$this->database."`.`".$this->table."` (";
			$i=0;
			foreach($this->edit_fields as $field)
			{
				if($i)
				{
					$sql.=",";
				}
				$sql.="`".$field['field_name']."`";
				$i++;
			}
			$sql.=")SELECT ";
			$i=0;
			foreach($this->edit_fields as $field)
			{
				if($i)
				{
					$sql.=",";
				}
				$sql.="`".$field['field_name']."`";
				$i++;
			}
			$sql.=" FROM `".$this->database."`.`".$this->table."` "
				."WHERE `".$this->primary_field."`='".$_GET['dupe']."'"
				."";
			if($this->database_mysqli->mysqlidb->query($sql))
			{
				$this->messages[]=$this->single_name." ".$_GET['dupe']." DUPLICATED TO ID ".$this->database_mysqli->mysqlidb->{'insert_id'};
				$this->record_activity($sql);
			}
		}
	}

	private function _delete_multiple()
	{
		if(is_array($_POST['delete_id']))
		{
			if(count($_POST['delete_id']))
			{
				$sql="DELETE FROM `".$this->database."`.`".$this->table."` WHERE `".$this->primary_field."`IN(".implode(',',$_POST['delete_id']).")";
				if($this->database_mysqli->mysqlidb->query($sql))
				{
					$this->messages[]=$this->database_mysqli->mysqlidb->{'affected_rows'}." DELETED";
					$this->record_activity($sql);
				}
			}
		}
	}

	private function record_activity($activity=NULL)
	{
		//future development
		if($activity&&1==2)
		{
			$this->database_mysqli_record_activity->mysqlidb->insertInto('_admin','admin_activity',array(
				'email'=>$_COOKIE['email'],
				'database_name'=>$this->database,
				'table_name'=>$this->table,
				'activity'=>$activity
			));
		}
	}

	private function show_edit_field($key)
	{
		if(!$this->edit_fields[$key])
			return false;

		global $database_mysqli_local;
		global $echo_bot_javascript;

		$f=$this->edit_fields[$key];
		if(!(isset($f['edit_name'])&&$f['edit_name']))
			$f['edit_name']=ucwords(str_replace("_"," ",$f['field_name']));

		if(!(isset($f['value'])&&$f['value'])&&isset($this->single)&&is_array($this->single))
			$f['value']=(isset($this->single[$key])?$this->single[$key]:"");

		if(!empty($f['MYSQL_AES_KEY'])&&!empty($this->single[$key."_AES_DECRYPTED"]))
			$f['value']=$this->single[$key."_AES_DECRYPTED"];

		$out="";
		if(!(isset($f['edit_field_type'])&&$f['edit_field_type'])||(isset($f['edit_field_type'])&&$f['edit_field_type']=='text'))
		{
		//TEXT FIELD
			if(!(isset($f['value'])&&strlen($f['value']))&&isset($f['default_value'])&&strlen($f['default_value']))
				$f['value']=$f['default_value'];

			$out.=""
				."<tr>"
					."<td class='input_name'>"
							.$f['edit_name']
					."</td>"
					."<td class='input_value'>"
						."<input type='text' name='".$f['field_name']."' value=\"".(isset($f['value'])?htmlspecialchars($f['value']):"")."\""
					;

			$c='attr.';
			foreach($f as $k=>$v)
				if(substr($k,0,strlen($c))==$c)
					$out.=" ".substr($k,strlen($c))."=\"".htmlspecialchars($v)."\"";

			if(!(isset($f['size'])&&is_numeric($f['size'])))
				$out.=" style='width:100%;'";
			else
				$out.=" size=".$f['size'];

			$out.=""
				." />"
				.(!empty($f['attr.pattern'])?"<div class='attrPatternNote'>Required Pattern: ".$f['attr.pattern']."</div>":"")
					."</td>"
				."</tr>"
			;

			//do we have a suggestion field here?
			if(isset($f['attr.suggestionField'])&&strlen($f['attr.suggestionField']))
			{
				require_once '/aglob/class/js/adminInputTextSuggestionBox.php';
				$echo_bot_javascript.=adminInputTextSuggestionBox::getJS();
			}
		}
		elseif($f['edit_field_type']=='textarea')
		{
		//TEXTAREA
			if(!(isset($f['size'])&&is_numeric($f['size'])))
				$f['size']=10;

			$out.=""
				."<tr>"
				."<td class='input_name'>".$f['edit_name']."</td>"
				."<td class='input_value'>"
				."<textarea id='".$f['field_name']."Textarea' "
					."rows=".$f['size']." "
					."name='".$f['field_name']."' "
					."style='width:100%;".(isset($f['textarea_style'])?$f['textarea_style']:"")."'"
			;

			$c='attr.';
			foreach($f as $k=>$v)
				if(substr($k,0,strlen($c))==$c)
					$out.=" ".substr($k,strlen($c))."=\"".htmlspecialchars($v)."\"";

			$out.=""
					.">"
					.(isset($f['value'])?htmlspecialchars($f['value']):"")
				."</textarea>"
				."</td>"
				."</tr>"
			;

			//enable the suggestion system (if desired)
			if(isset($f['suggestionField'])&&$f['suggestionField'])
			{
				list($da,$ta,$fi)=explode('.',$f['suggestionField']);
				$echo_bot_javascript.=""
					."function addThisTo".$f['field_name']."SuggestionBox(zthis){"
						."var zta=$('#".$f['field_name']."Textarea');"
						."var x=zta.val().split(' ');"
						."x.splice(-1,1);"
						."x=x.join(' ').trim().replace(/,$/,'');"
						."zta.val(x+(x.length>0?', ':'')+$(zthis).text());"
						."$('#".$f['field_name']."SuggestionBox').html('');"
					."}"
					."$('#".$f['field_name']."Textarea').keyup(function(){"
						."var x=$(this).val().split(' ').pop();"
						."if(x.length<2){"
							."$('#".$f['field_name']."SuggestionBox').html('');"
							."return false;"
						."}"
						."$.getJSON('h/getJSON.php?database=".$da."&table=".$ta."&select=".$fi."&getSuggestions='+encodeURIComponent(x),function(data){"
							."$('#".$f['field_name']."SuggestionBox').html('');"
							."$.each(data,function(i,item){"
								."if(item.".$fi.".length<1) return;"
								."$('#".$f['field_name']."SuggestionBox').append(\""
									."<a href='javascript:void(0);' onclick='addThisTo".$f['field_name']."SuggestionBox(this);'>\"+item.".$fi."+\"</a> &nbsp; "
								."\");"
							."});"
						."});"
					."}).parent().prepend(\"<div id='".$f['field_name']."SuggestionBox' style='font-size:0.8em;'></div>\");"
				;
			}
		}
		elseif($f['edit_field_type']=='t_or_f')
		{
		//TRUE OR FALSE RADIO
			if(isset($f['default_value'])&&!isset($f['default']))
				$f['default']=$f['default_value'];
			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'>True <input type='radio' name='".$f['field_name']."' value='T'";
			if((isset($f['value'])&&$f['value']=='T')||((!isset($f['value'])||$f['value']!='F')&&isset($f['default'])&&$f['default']=='T'))
				$out.=" checked";

			$out.=" style='width: 20px;' /> &nbsp; &nbsp; ";
			$out.=" False <input type='radio' name='".$f['field_name']."' value='F'";

			if((isset($f['value'])&&$f['value']=='F')||((!isset($f['value'])||$f['value']!='T')&&isset($f['default'])&&$f['default']=='F'))
				$out.=" checked";

			$out.=" style='width: 20px;' /></td></tr>";
		}
		elseif($f['edit_field_type']=='radio')
		{
		//CUSTOM RADIO
			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'>";
			$i=0;
			foreach($f['radios'] as $radio)
			{
				if($i)
					$out.=" &nbsp; &nbsp; ";

				$out.=""
					."<nobr>"
						.$radio['display'].": "
						."<input "
							."type='radio' "
							."name='".$f['field_name']."' "
							."title=\"".htmlspecialchars($radio['value'])."\""
							."value=\"".htmlspecialchars($radio['value'])."\""
				;

				if(
					(isset($f['value'])&&isset($radio['value'])&&$f['value']==$radio['value'])
					||
					(isset($radio['value'])&&isset($f['default_value'])&&$radio['value']==$f['default_value']&&!(isset($f['value'])&&$f['value']))
				)
					$out.=" checked";

				$out.=""
					." />"
					."</nobr>"
				;
				$i++;
			}
			$out.="</td></tr>";
		}
		elseif($f['edit_field_type']=='select')
		{
		//SELECT
			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'><select name='".$f['field_name']."'>";

			if(isset($f['blank_top_select'])&&$f['blank_top_select'])
				$out.="<option value=''></option>";

			if(!(isset($f['sql'])&&$f['sql'])&&$f['input_value']&&$f['input_visible']&&$f['select_db']&&$f['select_table'])
			{
				$f['sql']="SELECT `".$f['input_value']."`,`".$f['input_visible']."` FROM `".$f['select_db']."`.`".$f['select_table']."` "
				.(isset($f['select_table_display_where'])?$f['select_table_display_where']:"")
				."ORDER BY `".$f['input_visible']."` ASC";

			}
			if($f['sql'])
			{
				$result=$this->database_mysqli->mysqlidb->getRowsFromQuery($f['sql']);
				foreach($result as $row)
				{
					$out.="<option value='".$row[$f['input_value']]."'";

					if(
						(isset($f['value'])&&$f['value']==$row[$f['input_value']])||
						(!(isset($f['value'])&&strlen($f['value']))&&isset($f['default_value'])&&isset($row[$f['input_value']])&&$row[$f['input_value']]==$f['default_value'])
					)
						$out.=" selected";

					$out.=">".$row[$f['input_visible']]."</option>";
				}
			}
			$out.="</select></td></tr>";
		}
		elseif($f['edit_field_type']=='select_relationship_manager')
		{
		//SELECT
			$out.=""
			."<tr>"
				."<td class='input_value' colspan='2'>"
					."<div>".$f['edit_name']."</div>"
					."<select "
						."skippedOnce='0' "
						."input_value='".$f['input_value']."' "
						."input_visible='".$f['input_visible']."' "
						."select_db='".$f['select_db']."' "
						."select_table='".$f['select_table']."' "
						."blank_top_select='".(isset($f['blank_top_select'])&&$f['blank_top_select']?1:0)."' "
						."name='".$f['field_name']."' "
						."id='".$f['field_name']."_relationship_manager_sel'"
						.">"
			;

			if(isset($f['blank_top_select'])&&$f['blank_top_select'])
				$out.="<option value=''></option>";

			if(!(isset($f['sql'])&&$f['sql'])&&$f['input_value']&&$f['input_visible']&&$f['select_db']&&$f['select_table'])
			{
				$f['sql']="SELECT `".$f['input_value']."`,`".$f['input_visible']."` FROM `".$f['select_db']."`.`".$f['select_table']."` "
				.(isset($f['select_table_display_where'])?$f['select_table_display_where']:"")
				."ORDER BY `".$f['input_visible']."` ASC";
			}
			if($f['sql'])
			{
				$result=$this->database_mysqli->mysqlidb->getRowsFromQuery($f['sql']);
				foreach($result as $row)
				{
					$out.="<option value='".$row[$f['input_value']]."'";

					if(
						(isset($f['value'])&&$f['value']==$row[$f['input_value']])||
						(!(isset($f['value'])&&strlen($f['value']))&&isset($f['default_value'])&&isset($row[$f['input_value']])&&$row[$f['input_value']]==$f['default_value'])
					)
						$out.=" selected";

					$out.=">".$row[$f['input_visible']]."</option>";
				}
			}
			$selectManagerStyle='border:1px solid #fff;width:100%;height:250px;';
			$selectManagerOnLoad="buildSelectFromId(this);";
			$selectManagerControllerId=$f['field_name']."_relationship_manager_sel";
			$selectManagerExtraParams=" frameborder='1' smci='".$selectManagerControllerId."' style='".$selectManagerStyle."' onload='".$selectManagerOnLoad."'";
			$out.=""
				."</select>"
				.(isset($f['admin_control_page'])?""
					."<div style='font-size:0.8em;padding:10px 0px;'>"
						."[<a href='javascript:void(0);' onclick='".$f['field_name']."RelationshipManagerNew();'>"
							."Add New ".$f['edit_name']
						."</a>]"
					."</div>"
					."<div id='".$f['field_name']."_relationship_manager' style='padding:10px;'>"
					."<script>"
						."$('#".$f['field_name']."_relationship_manager_sel').change(function(){"
							."if($(this).val())"
								."$('#".$f['field_name']."_relationship_manager').html(\""
									."<iframe src='/admin/controlWindow.php?p=".$f['admin_control_page']."&id=\"+$(this).val()+\"'".$selectManagerExtraParams."></iframe>"
								."\");"
							." else "
								."$('#".$f['field_name']."_relationship_manager').html(\""
								."\");"
						."});"
						."$('#".$f['field_name']."_relationship_manager').html(\""
							.(isset($f['value'])&&$f['value']?""
								."<iframe src='/admin/controlWindow.php?p=".$f['admin_control_page']."&id=".$f['value']."'".$selectManagerExtraParams."></iframe>"
								:(isset($_GET['new'])&&$_GET['new']&&!isset($this->single)?""
									."<iframe src='/admin/controlWindow.php?p=".$f['admin_control_page']."&new=1'".$selectManagerExtraParams."></iframe>"
								:"")
							)
						."\");"
						."function ".$f['field_name']."RelationshipManagerNew(){"
							."$('#".$f['field_name']."_relationship_manager').html(\""
								."<iframe src='/admin/controlWindow.php?p=".$f['admin_control_page']."&new=1'".$selectManagerExtraParams."></iframe>"
							."\")"
						."}"
						."function buildSelectFromId(zthis){"
							."$(zthis).height( $(zthis).contents().find('body').height()+100 );"//adjust height to contents height
							."var xsel='#'+$(zthis).attr('smci');"
							."if($(xsel).attr('skippedOnce')=='0'){"
								."$(xsel).attr('skippedOnce','1');"
								."return true;"
							."}"
							."var xsel_current_val=$(xsel).val();"
							."var xsel_input_value=$(xsel).attr('input_value');"
							."var xsel_input_visible=$(xsel).attr('input_visible');"
							."$(xsel).attr('current_val',xsel_current_val);"
							."$.getJSON('/admin/h/getJSON.php?database='+$(xsel).attr('select_db')+'&table='+$(xsel).attr('select_table')+'&select='+$(xsel).attr('input_value')+','+$(xsel).attr('input_visible'),function(data){"
								."$(xsel).html('');"
								."if($(xsel).attr('blank_top_select')=='1') $(xsel).append(\"<option value='0'></option>\");"
								."$.each(data,function(i,item){"
									."if(xsel_current_val==item[xsel_input_value]){"
										."$(xsel).append(\"<option value='\"+item[xsel_input_value]+\"' selected='selected'>\"+item[xsel_input_visible]+\"</option>\");"
									."} else {"
										."$(xsel).append(\"<option value='\"+item[xsel_input_value]+\"'>\"+item[xsel_input_visible]+\"</option>\");"
									."}"
								."});"
							."});"
						."}"
					."</script>"
					."</div>"
					:"")
				."</td></tr>"
			;
		}
		elseif($f['edit_field_type']=='input_select')
		{
		//SELECT WITH INPUT FIELD
			$out.=""
				."<tr><td class='input_name'>".$f['edit_name']."</td>"
				."<td class='input_value'>"
				."<input type='text' name='".$f['field_name']."' value=\"".$f['value']."\"".(isset($f['size'])?" size=\"".$f['size']."\"":"")." />"
				."<select onchange=\"$('input[name=\'".$f['field_name']."\']').val($(this).val());\">"
			;
			if(isset($f['blank_top_select'])&&$f['blank_top_select'])
			{
				$out.="<option value=''></option>";
			}
			if(!$f['sql']&&$f['input_value']&&$f['input_visible']&&$f['select_db']&&$f['select_table'])
			{
				$f['sql']="SELECT `".$f['input_value']."`,`".$f['input_visible']."` FROM `".$f['select_db']."`.`".$f['select_table']."` ".$f['select_table_display_where']
				."ORDER BY `".$f['input_visible']."` ASC";

			}
			if(isset($f['sql'])&&$f['sql'])
			{
				foreach($database_mysqli_local->mysqlidb->getRowsFromQuery($f['sql']) as $row)
				{
					$out.="<option value='".$row[$f['input_value']]."'";
					if($f['value']==$row[$f['input_value']])
						$out.=" selected";

					$out.=">".$row[$f['input_visible']]."</option>";
				}
			}
			$out.="</select></td></tr>";
		}
		elseif($f['edit_field_type']=='read_only')
		{
		//READ ONLY
			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'>".(isset($f['value'])?htmlspecialchars($f['value']):"")
			."<input type='hidden' name='".$f['field_name']."' value=\"".(isset($f['value'])?htmlspecialchars($f['value']):"")."\" />"
			."</td></tr>";
		}
		elseif($f['edit_field_type']=='read_only_serialized')
		{
		//READ ONLY
			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'><pre>".(isset($f['value'])?htmlspecialchars(print_r(unserialize($f['value']),true)):"")."</pre>"
			."<input type='hidden' name='".$f['field_name']."' value=\"".(isset($f['value'])?htmlspecialchars($f['value']):"")."\" />"
			."</td></tr>";
		}
		elseif($f['edit_field_type']=='none')
		{
			//no edit field...
		}
		elseif($f['edit_field_type']=='file_base64')
		{
			if(isset($f['value'])&&strlen($f['value']))
			{
				$fileData = base64_decode($f['value']);
				$fo=finfo_open();
				$mime_type=finfo_buffer($fo,$fileData,FILEINFO_MIME_TYPE);

				if($mime_type=='image/png')
					$ext='png';
				elseif($mime_type=='image/gif')
					$ext='gif';
				elseif($mime_type=='image/jpg')
					$ext='jpg';
				elseif($mime_type=='image/jpeg')
					$ext='jpg';
				elseif($mime_type=='image/x-icon')
					$ext='ico';

				global $SITE_PROTOCOL;
				global $SITE_CONTROL_DOMAIN;
				$fileURL=$SITE_PROTOCOL.'://'.$SITE_CONTROL_DOMAIN.'/'
					.(isset($f['database'])&&$f['database']=='site'?'af':'afile')
					.'/'.$this->table.'/'.$this->single['id'].'.'.$f['field_name'].'.'.$ext
				;
			}
			$out.=""
				."<tr>"
					."<td class='input_name'>".$f['edit_name']."</td>"
					."<td class='input_value'>"
						.(isset($fileURL)&&$fileURL?"<div>".$fileURL."</div>":"")
						.(isset($f['value'])&&strlen($f['value'])&&isset($f['file_type'])&&$f['file_type']=='image'&&isset($f['show_file_preview'])&&$f['show_file_preview']?""
							."<div>"
								."<img src='data:".$mime_type.";base64,".$f['value']."' />"
							."</div>"
						:"")
						.(isset($f['value'])&&strlen($f['value'])?"File Uploaded ":"")
						."<input type='file' name='".$f['field_name']."'/>"
					."</td>"
				."</tr>"
			;
		}//SELECT MULTIPLE
		elseif($f['edit_field_type']=='select_multiple')
		{
			if($f['value'])
			{
				$val_array=explode(",",$f['value']);
			}
			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'><select name='".$f['field_name']."[]' multiple='multiple' size='10'>";
			if(isset($f['sql'])&&$f['sql'])
				$sql=$f['sql'];
			else
				$sql="SELECT `id`,`".$f['input_visible']."` FROM `".$this->db_name."`.`".$f['select_table']."` ".$f['select_table_display_where']
				."ORDER BY `".$f['input_visible']."` ASC"
				;
			foreach($database_mysqli_local->mysqlidb->getRowsFromQuery($sql) as $row)
			{
				$out.="<option value='".$row['id']."'";
				if(isset($val_array)&&is_array($val_array))
					if(in_array($row['id'],$val_array))
						$out.=" selected";

				$out.=">".$row[$f['input_visible']]."</option>";
			}
			$out.="</select></td></tr>";
		}
		elseif($f['edit_field_type']=='select_2_multiple')
		{
		//SELECT 2 MULTIPLES
			if(!(isset($f['size'])&&is_numeric($f['size'])))
				$f['size']=10;

			if(isset($f['value'])&&$f['value'])
				$val_array=explode(",",$f['value']);
			else
				$val_array=array();

			foreach($val_array as $key => $val)
				$val_array[$key]=trim($val);

			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'>"
			."<table border='0' cellpadding='0' cellspacing='0'><tr>"
			."<td><select id='".$f['field_name']."_from_select' multiple='multiple' size=".$f['size'].">";
			$selectable=array();
			$sql=""
				."SELECT `".$f['input_value']."`,`".$f['input_visible']."` "
				."FROM `".$f['select_db']."`.`".$f['select_table']."` "
				.(isset($f['select_table_display_where'])?$f['select_table_display_where']:"")
				."ORDER BY `".$f['input_visible']."` ASC"
			;
			foreach($database_mysqli_local->mysqlidb->getRowsFromQuery($sql) as $row)
			{
				$selectable[]=$row;
				if(!in_array($row[$f['input_value']],$val_array))
					$out.="<option value='".$row[$f['input_value']]."'>".$row[$f['input_visible']]."</option>";
			}
			$out.="</select></td>"
			."<td><a href='javascript:void(0);' id='".$f['field_name']."_add'>add &gt;&gt;</a><br/><br/><a href='javascript:void(0);' id='".$f['field_name']."_remove'>&lt; &lt; remove</a></td>"
			."<td><select id='".$f['field_name']."_to_select' name='".$f['field_name']."[]' multiple size=".$f['size'].">";
			foreach($selectable as $row)
			{
				$selectable[]=$row;
				if(in_array($row[$f['input_value']],$val_array))
					$out.="<option value='".$row[$f['input_value']]."'>".$row[$f['input_visible']]."</option>";
			}
			$out.="</select></td>"
			."</tr></table>"
			."</td></tr>"
			;
			zInterface::addBotJS(""
			."\$(document).ready(function(){"
				."\$('#".$f['field_name']."_add').click(function(){"
					."\$('#".$f['field_name']."_from_select option:selected').remove().appendTo('#".$f['field_name']."_to_select');"
				."});"
				."\$('#".$f['field_name']."_remove').click(function(){"
					."\$('#".$f['field_name']."_to_select option:selected').remove().appendTo('#".$f['field_name']."_from_select');"
				."});"
			."});"
			."\$('form').submit(function(){"
				."\$('#".$f['field_name']."_to_select option').each(function(i){"
					."\$(this).attr('selected','selected');"
				."});"
			."});"
			);
		}
		elseif($f['edit_field_type']=='one_to_ten')
		{//ONE TO TEN SELECT
			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'>";
			$out.="<select name='".$f['field_name']."'>";
			for($i=1;$i<=10;$i++)
			{
				$out.="<option value='".$i."'";
				if($i==$f['value'])
					$out.=" selected";

				$out.=">".$i."</option>";
			}
			$out.="</select>";
			$out.="</td></tr>";
		}
		elseif($f['edit_field_type']=='datetime')
		{//DATETIME
			if(!$f['value'])
				$f['value']=date('Y-m-d H:i:s',$f['default_value']);

			if($f['value'])
			{
				$parts=explode(" ",$f['value']);
				$date_parts=explode("-",$parts[0]);
				$time_parts=explode(":",$parts[1]);
			}
			$out.="<tr><td class='input_name'>".$f['edit_name']."</td>"
			."<td class='input_value'><table border='0' cellpadding='0' cellspacing='0' class='datetime_table'><tr>"
			."<td>";
			$out.="<select id='".$f['field_name']."_mm' onchange='set_time_".$f['field_name']."();'>";
			for($i=1;$i<=12;$i++)
			{
				if(strlen($i)==1)
				{
					$j='0'.$i;
				}
				else
				{
					$j=$i;
				}
				$out.="<option value='".$j."'";
				if($j==$date_parts[1])
				{
					$out.=" selected";
				}
				$out.=">".date('M',mktime(1,1,1,$i,1,2010))."</option>";
			}
			$out.="</select></td>";
			$out.="<td>";
			$out.="<select id='".$f['field_name']."_dd' onchange='set_time_".$f['field_name']."();'>";
			for($i=1;$i<=31;$i++)
			{
				if(strlen($i)==1)
					$j='0'.$i;
				else
					$j=$i;

				$out.="<option value='".$j."'";
				if($j==$date_parts[2])
					$out.=" selected";

				$out.=">".$i."</option>";
			}
			$out.="</select></td>";
			$out.="<td>";
			$out.="<select id='".$f['field_name']."_yyyy' onchange='set_time_".$f['field_name']."();'>";
			for($i=date('Y',strtotime('-1 Years'));$i<=date('Y',strtotime('+20 Years'));$i++)
			{
				$j=$i;
				$out.="<option value='".$j."'";
				if($j==$date_parts[0])
				{
					$out.=" selected";
				}
				$out.=">".$i."</option>";
			}
			$out.="</select></td>";
			$out.="<td>";
			$out.="<select id='".$f['field_name']."_hh' onchange='set_time_".$f['field_name']."();'>";
			for($i=0;$i<=23;$i++)
			{
				if(strlen($i)==1)
				{
					$j='0'.$i;
				}
				else
				{
					$j=$i;
				}
				$out.="<option value='".$j."'";
				if($j==$time_parts[0])
				{
					$out.=" selected";
				}
				$out.=">".$i."</option>";
			}
			$out.="</select></td>";
			$out.="<td>";
			$out.="<select id='".$f['field_name']."_ii' onchange='set_time_".$f['field_name']."();'>";
			for($i=0;$i<=59;$i++)
			{
				if(strlen($i)==1)
				{
					$j='0'.$i;
				}
				else
				{
					$j=$i;
				}
				$out.="<option value='".$j."'";
				if($j==$time_parts[1]){
					$out.=" selected";
				}
				$out.=">".$i."</option>";
			}
			$out.="</select></td>";
			$out.="<td>";
			$out.="<select id='".$f['field_name']."_ss' onchange='set_time_".$f['field_name']."();'>";
			for($i=0;$i<=59;$i++)
			{
				if(strlen($i)==1)
				{
					$j='0'.$i;
				}
				else
				{
					$j=$i;
				}
				$out.="<option value='".$j."'";
				if($j==$time_parts[2])
				{
					$out.=" selected";
				}
				$out.=">".$i."</option>";
			}
			$out.="</select></td>";
			$out.="</tr></table>"
			."<script>"
			."function set_time_".$f['field_name']."(){"
			."document.getElementById('".$f['field_name']."_time').value=document.getElementById('".$f['field_name']."_yyyy').value+'-'+document.getElementById('".$f['field_name']."_mm').value+'-'+document.getElementById('".$f['field_name']."_dd').value+' '+document.getElementById('".$f['field_name']."_hh').value+':'+document.getElementById('".$f['field_name']."_ii').value+':'+document.getElementById('".$f['field_name']."_ss').value;"
			."}"
			."</script>"
			."<input type='hidden' name='".$f['field_name']."' id='".$f['field_name']."_time' value='".$f['value']."' /></td></tr>";
		}
		elseif($f['edit_field_type']=='datetime-local')
		{//DATETIME-LOCAL
				$out.=""
					."<tr>"
						."<td class='input_name'>".$f['edit_name']."</td>"
						."<td class='input_value'>"
							."<input type='datetime-local' name='".$f['field_name']."' value='".str_replace(' ','T',$f['value'])."' />"
						."</td>"
					."</tr>"
				;
		}
		elseif($f['edit_field_type']=='date')
		{//DATE
				$out.=""
					."<tr>"
						."<td class='input_name'>".$f['edit_name']."</td>"
						."<td class='input_value'>"
							."<input type='date' name='".$f['field_name']."' value='".$f['value']."' />"
						."</td>"
					."</tr>"
				;
		}
		elseif($f['edit_field_type']=='number')
		{//NUMBER
			if(!strlen($f['value'])&&strlen($f['default_value']))
				$f['value']=$f['default_value'];
			$out.=""
				."<tr>"
					."<td class='input_name'>".$f['edit_name']."</td>"
					."<td class='input_value'>"
						."<input"
							." type='number' name='".$f['field_name']."' value='".$f['value']."'"
							.(!empty($f['number_min'])?" min='".$f['number_min']."'":"")
							.(!empty($f['number_max'])?" max='".$f['number_max']."'":"")
							.(!empty($f['number_step'])?" step='".$f['number_step']."'":"")
						." />"
					."</td>"
				."</tr>"
			;
		}
		elseif($f['edit_field_type']=='profile_link')
		{
			if(is_numeric($f['value']))
			{
				$out.=""
					."<tr><td class='input_name'>".$f['edit_name']."</td>"
					."<td class='input_value'>"
					."<a href='/admin/?p=account_get_info&sub=go&id=".$f['value']."'>".$f['value']."</a>"
					."<input type='hidden' name='".$f['field_name']."' value='".$f['value']."' />"
					."</td>"
					."";
				$out.="</td></tr>";
			}
		}
		elseif($f['edit_field_type']=='image_hold_cell'&&isset($this->single)&&is_array($this->single)&&isset($this->single['id']))
		{
			if(!isset($this->{'outsideSingleFormBottom'})) $this->{'outsideSingleFormBottom'}='';
			$this->{'outsideSingleFormBottom'}.=""
				."<table><tr>"
					."<td class='input_name'>"
						.$f['edit_name']
					."</td>"
					."<td class='input_value' id='imageHoldCell'>"
					."</td>"
				."</tr></table>"
			;
			if(!isset($echo_bot_javascript)) $echo_bot_javascript="";
			$echo_bot_javascript.=""
				."$(document).ready(function(){"
					."imageHoldCell();"
				."});"
				."function imageHoldCell(){"
					."$.getScript('/admin/h/imageCellUpload.php?domain=".$f['image_hold_cell_domain']."&folder=".str_replace('%%id%%',$this->single['id'],$f['image_hold_cell_folder'])."');"
				."}"
			;
		}
		elseif($f['edit_field_type']=='serialize')
		{
			if(!(isset($f['size'])&&is_numeric($f['size'])))
				$f['size']=10;

			$out.=""
				."<tr>"
				."<td class='input_name'>".$f['edit_name']."</td>"
				."<td class='input_value'>"
			;
			$f['value']=(isset($f['value'])&&$f['value']?unserialize($f['value']):array());
			if(is_array($f['value']))
				foreach($f['value'] as $k => $v)
					$out.=""
						."<div class='serializeValueFieldWrap'>"
							."<div>"
								."[<a href='javascript:void();' onclick=\"if(confirm('Remove?')){\$(this).parents('.serializeValueFieldWrap:first').remove();}\" style='color:#ff0000;'>"
									."X"
								."</a>] ".$k.":"
							."</div>"
							."<textarea "
							."rows='".$f['size']."' "
								."name=\"".$f['field_name']."[".$k."]\" "
								."style='width:100%;'"
								.">"
								.(isset($v)?htmlspecialchars($v):"")
							."</textarea>"
						."</div>"
					;

			$out.=""
					."<div id='addSerializeVariable".$f['field_name']."Holder'>"
						."<input placeholder='Add Custom Variable' type='text' id='addSerializeVariable".$f['field_name']."' /> <a href='javascript:void(0);' id=\"addSerializeVariable".$f['field_name']."Cl\">Add Custom Variable</a>"
					."</div>"
				."</td>"
				."</tr>"
			;
			zInterface::addBotJS(""
			."\$(document).ready(function(){"
				."$('#addSerializeVariable".$f['field_name']."Cl').click(function(){"
					."var v=$('#addSerializeVariable".$f['field_name']."').val();"
					."var h=$('#addSerializeVariable".$f['field_name']."Holder');"
					."if(v.length<=0) return false;"
					."h.prepend(\"<div>\"+v+\":</div><textarea name=\\\"".$f['field_name']."[\"+v+\"]\\\" style='width:100%;' rows='".$f['size']."'></textarea>\");"
					."$('#addSerializeVariable".$f['field_name']."').val('');"
					."return false;"
				."});"
			."});"
			);
		}
		elseif($f['edit_field_type']=='redactor_WYSIWYG')
		{
		//REDACTOR WYSIWYG
			if(!isset($this->redactor_WYSIWYG_initiated))
			{
				$out.=""
					."<style type='text/css'>"
					."@import url('/admin/redactor/redactor.css');"
					."</style>"
				;
				zInterface::addBotJS(""
						."\$(document).ready(function(){"
							."$.getScript('/admin/redactor/redactor.min.for.jquery.1.9.js',function(){"
								."$('.redactor').redactor("
									."{"
										."imageUpload:'/admin/redactor/imageUpload.php',"
										."fileUpload:'/admin/redactor/fileUpload.php'"
									."}"
								.");"
							."});"
							//FOR SOME REASON THE REDACTOR IS NOT UPDATING WITHOUT BEING IN CODE MODE - WE WILL FORCE CODE MODE WHEN WE UPDATE
							."$(\"#single_table input[type='submit']\").click(function(){"
								."$('a.redactor_btn_html').not('.redactor_act').click();"
							."});"
							//BRING THE CONTROLS DOWN AS NEEDED
							."$(window).scroll(function(){"
								.'$("ul.redactor_toolbar").each(function(){'
									."var ot=parseInt($(this).attr('zinitOffsetTop'));"
									."if(isNaN(ot)){"
										."$(this).attr('zinitOffsetTop',$(this).offset().top);"
										."var ot=parseInt($(this).attr('zinitOffsetTop'));"
									."}"
									."var st=parseInt($(window).scrollTop());"
									."var tm=ot-st;"
									."if(tm>0){"
										."var o=0;"
									."}else{"
										."var o=tm*-1;"
									."}"
									."var m=$(this).parent().height()-$(this).height();"
									."if(o>m) o=m;"
									."$(this)"
										.".stop()"
										.".animate({'top':(o)+'px'},'slow');"
								.'});'
							."});"
						."});"
				);
				$this->redactor_WYSIWYG_initiated=true;
			}
			$out.=""
				."<tr>"
					."<td colspan='2'>"
						."<textarea rows='20' class='redactor' name='".$f['field_name']."' style='width:100%;height:300px;'>"
							.htmlspecialchars($f['value'])
						."</textarea>"
					."</td>"
				."</tr>"
			;
		}
		elseif($f['edit_field_type']=='template_plug-in')
		{
			if(is_file(__DIR__."/includes/plug-ins/".$f['plug-in'].".plug-in.php"))
				require_once __DIR__."/includes/plug-ins/".$f['plug-in'].".plug-in.php";
		}

		return $out;
	}

	private function show_image_field($key)
	{
		if(!$this->image_fields[$key])
		{
			return false;
		}
		$out="";
		if($this->image_fields[$key]['folder']&&$this->image_fields[$key]['file_name_format'])
		{
			$out.=""
				."<tr>"
				."<td class='input_name'>".ucwords(str_replace('_',' ',$key))
				."";
			if($this->image_fields[$key]['extension'])
			{
				$out.=""
					."<br/>"
					."<font size='1'><i><b>"
					."(".$this->image_fields[$key]['extension'].")"
					."</b></i></font>"
				;
			}
			$out.=""
				."</td>"
				."<td class='input_value'><input type='file' name='image_".$key."' /></td>"
				."</tr>"
			;
			if($this->image_fields[$key]['url_base_folder'])
			{
				$folder=$this->image_fields[$key]['folder'];
				$url_base_folder=$this->image_fields[$key]['url_base_folder'];
				$filename=$this->image_fields[$key]['file_name_format'];
				if(is_array($this->single))
				{
					foreach($this->single as $k => $v)
					{
						$folder=str_replace("{".$k."}",$v,$folder);
						$url_base_folder=str_replace("{".$k."}",$v,$url_base_folder);
						$filename=str_replace("{".$k."}",$v,$filename);
					}

					$out.=""
						."<tr>"
						."<td colspan='2'>"
					;

					if(is_file($folder."/".$filename))
					{
						$out.=""
							."<div><input type='checkbox' name='image_delete_".$key."' value='".$folder."/".$filename."' /> Delete</div>"
							."<img src='".$url_base_folder."/".$filename."?t=".date('YmdHis')."' />"
						;
					}
					else
					{
						$out.="No Image Found";
					}

					$out.=""
						."</td>"
						."</tr>"
					;

				}
			}
		}
		return $out;
	}

	private function _check_global_variables()
	{
		//CHECK AUTHORIZATION
		$user=loginModel::requireAdmin();
		if($user['is_admin']!='T')
			$this->authorized=false;
		else
			$this->authorized=true;

		if($this->authorized)
		{
			//CHECK LINK
			global $template_database_mysqli_alternate;
			global $database_mysqli_local;
			if(isset($database_mysqli_local)&&is_object($database_mysqli_local))
			{
				if(isset($template_database_mysqli_alternate)&&is_object($template_database_mysqli_alternate))
					$this->database_mysqli=$template_database_mysqli_alternate;
				else
					$this->database_mysqli=$database_mysqli_local;

				$this->database_mysqli_record_activity=$database_mysqli_local;
			}
			//CHECK DATABASE
			global $database;
			if($database)
			{
				$this->database=$database;
			}
			//CHECK TABLE
			global $table;
			if($table)
			{
				$this->table=$table;
			}
			//CHECK SINGLE NAME
			global $single_name;
			if($single_name)
			{
				$this->single_name=$single_name;
			}else{
				$this->single_name="ITEM";
			}
			//CHECK MULTIPLE NAME
			global $multiple_name;
			if($multiple_name)
			{
				$this->multiple_name=$multiple_name;
			}else{
				$this->multiple_name="ITEMS";
			}
			//CHECK EDIT FIELDS
			global $edit_fields;
			if(is_array($edit_fields))
			{
				foreach($edit_fields as $k=>$v)
				{
					//SIMPLE BUILD ENTRY UPDATE
					if(!is_array($v)&&is_string($v))
					{
						unset($edit_fields[$k]);
						$k=$v;
						$edit_fields[$k]=array();
					}

					//SET THE DEFAULT FIELD NAME
					if(!isset($edit_fields[$k]['field_name']))
						$edit_fields[$k]['field_name']=$k;
				}
				$this->edit_fields=$edit_fields;
			}
			//CHECK IMAGE FIELDS
			global $image_fields;
			if(is_array($image_fields))
			{
				$this->image_fields=$image_fields;
			}
			//SETUP SEARCH FIELDS
			global $search_fields;
			if(is_array($search_fields))
			{
				$this->search_fields=$search_fields;
			}
			//CHECK LIST FIELDS
			global $list_fields;
			if(is_array($list_fields))
			{
				$this->list_fields=$list_fields;
			}
			//SETUP MESSAGES VARIABLE
			if(!(isset($this->messages)&&is_array($this->messages)))
			{
				$this->messages=array();
				global $messages;
				if(is_array($messages))
					foreach($messages as $m)
						if(is_string($m)&&strlen($m))
							$this->messages[]=$m;
			}
		}
		if($this->authorized&&$this->database&&$this->table&&$this->single_name&&$this->edit_fields&&$this->list_fields){
			return true;
		}else{
			return false;
		}
	}

	private function reGet($exclude=NULL)
	{
		//BUILD THE reGET STRING
		$reget=array(
			'p'=>(isset($_GET['p'])?$_GET['p']:""),
			'q'=>(isset($_GET['q'])?urlencode(stripslashes($_GET['q'])):""),
			'o'=>(isset($_GET['o'])?$_GET['o']:""),
			'd'=>(isset($_GET['d'])?$_GET['d']:""),
			'l'=>(isset($_GET['l'])?$_GET['l']:""),
			'page'=>(isset($_GET['page'])?$_GET['page']:"")
		);

		if(is_array($exclude)) foreach($exclude as $x) unset($reget[$x]);

		$reGetString="";
		$i=0;
		foreach($reget as $k=>$v)
		{
			$reGetString.=(!$i?"?":"&").$k."=".$v;
			$i++;
		}
		return $reGetString;
	}

	private function buildAESKeyFromKeyAndNonceV1($key,$nonce)
	{
		return hash('sha1',$key.$nonce);
	}

}


?>