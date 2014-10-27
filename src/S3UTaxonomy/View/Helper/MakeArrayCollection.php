<?php
namespace S3UTaxonomy\View\Helper;

use Zend\View\Helper\AbstractHelper;

class MakeArrayCollection extends AbstractHelper{
	public function __invoke($mang){

		$array = array();		
		foreach($mang as $item){
			$array[$item->getTermTaxonomyId()] = $item->getTermId()->getName(); 
		}
		return $array;
		
	}
}