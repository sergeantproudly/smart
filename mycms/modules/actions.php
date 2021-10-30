<?php

class actions extends krn_abstract{
	
	function __construct(){
		parent::__construct();
	}
	
	function GetResult(){
	}
	
	/** System */
	function SystemMultiSelect($params){
		$storageTable=$params['storageTable'];
		$storageSelfField=$params['storageSelfField'];
		$storageField=$params['storageField'];
		$selfValue=$params['selfValue'];
		dbDoQuery('DELETE FROM `'.$storageTable.'` WHERE `'.$storageSelfField.'`="'.$selfValue.'"',__FILE__,__LINE__);
		if(isset($params['values'])){
			foreach($params['values'] as $value){
				dbDoQuery('INSERT INTO `'.$storageTable.'` SET `'.$storageSelfField.'`="'.$selfValue.'", `'.$storageField.'`="'.$value.'"',__FILE__,__LINE__);
			}
		}
	}

	/** Service */
	function OnAddService($newRecord) {
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>'','"'=>'',"'"=>''));
			dbDoQuery('UPDATE services SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'services');
		}
	}
	
	function OnEditService($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>'','"'=>'',"'"=>''));
			dbDoQuery('UPDATE services SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($oldRecord['Code'], 'services');
			Routing::SetRouting($code, 'services');
		}
	}

	function OnDeleteService($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM services WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code, 'services');
		}
	}
	
	/** New */
	function OnAddNew($newRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>'','"'=>'',"'"=>''));
			dbDoQuery('UPDATE news SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'articles');
		}
	}
	
	function OnEditNew($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE news SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($oldRecord['Code'], 'articles');
			Routing::SetRouting($code, 'articles');
		}
	}

	function OnDeleteNew($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM news WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code, 'articles');
		}
	}

	/** Work */
	function OnAddWork($newRecord) {
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>'','"'=>'',"'"=>''));
			dbDoQuery('UPDATE works SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'projects');
		}
	}
	
	function OnEditWork($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>'','"'=>'',"'"=>''));
			dbDoQuery('UPDATE works SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($oldRecord['Code'], 'projects');
			Routing::SetRouting($code, 'projects');
		}
	}

	function OnDeleteWork($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM works WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code, 'projects');
		}
	}
	
	/** Static pages */
	function OnAddStaticPage($newRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE static_pages SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}else{
			dbDoQuery('UPDATE static_pages SET LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}		
	}
	
	function OnEditStaticPage($newRecord,$oldRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE static_pages SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}else{
			dbDoQuery('UPDATE static_pages SET LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}	
	}

	/** Media */
	function OnAddGallery($newRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>'','"'=>'',"'"=>''));
			dbDoQuery('UPDATE media_galleries SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'company');
		}
	}
	
	function OnEditGallery($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'','.'=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>'','"'=>'',"'"=>''));
			dbDoQuery('UPDATE media_galleries SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($oldRecord['Code'], 'company');
			Routing::SetRouting($code, 'company');
		}
	}

	function OnDeleteGallery($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM media_galleries WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code, 'company');
		}
	}
}

?>