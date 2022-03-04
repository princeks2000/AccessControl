<?php

namespace Princeks2000\AccessControl;
class AccessControl
{
private $role;
private $db;
private $accesslist;
public function __construct($db,$role)	{
	$this->db= $db;	
	$this->role= $role;
	$list = $this->db->from('components')->innerJoin('role_access_control ON role_access_control.action_id = components.id')
	->select(null)->select(['components.id','components.name'])
	->where('components.status','1')
	->where('role_access_control.role_id',$this->role)
	->fetchPairs('components.id','components.name');
	$this->accesslist = array_values($list);
}
public function checkaccess($component)	{
	if(is_array($component)){
		foreach ($component as $key => $item) {
		$status =  boolval(in_array($item, $this->accesslist));	
		if($status){
			return true;
		}
	}
		return false;
	}
	else{
	return boolval(in_array($component, $this->accesslist));
	}
}
public function getrolerule($role_id=null){
	$__components =  $this->db->from('components')->where('status','1')->fetchAll();
	if($role_id){
		$rules =  $this->db->from('role_access_control')->where('role_id',$role_id)->fetchAll();
		$rules = array_column($rules,'action_id');
	}
	else{
		$rules = [];			
	}

	$_components = [];
	foreach($__components as $key => $value) {
		$_components[$value['family']][] = $value;
	}
	$components = [];
	foreach ($_components as $fkey => $family) {
		$component = [];
		$children = [];
		$check = 0;
		foreach ($family as $ikey => $item) {
			$_values = [];
			if(in_array($item['id'], $rules)){
				$_values['checked'] = true;
				$check++;
			}
			else{
				$_values['checked'] = false;
			}
			$_values['id'] = $item['id'];
			$_values['label'] = $item['name'];
			$_values['title'] = $item['title'];
			$children[] = $_values;
		}

		$checked = (count($family) == $check ) ? true : false;
		$components[] = ['id'=>$fkey,'label'=>$fkey,'value'=>$fkey,'checked'=>$checked,'children' => $children];
	}
	return $components;
}
public function saverolerule($role_id,$roles){
	$this->db->deleteFrom('role_access_control')->where('role_id',$role_id)->execute();
	foreach ($roles as $key => $action_id) {
		$values  = ['action_id'=>$action_id,'role_id'=>$role_id];
		$this->db->insertInto('role_access_control')->values($values)->execute();
	}
}
}
