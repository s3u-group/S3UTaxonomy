<?php
namespace S3UTaxonomy\Controller\Plugin;
 
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
 
class TreePlugin extends AbstractPlugin{
   
    private $level=0;
    private $mangTam=array();

    public function xuatMenu($tree, $root = null) {          
        
        foreach($tree as $i=>$child) {           
            $parent = $child->getParent();
            if($parent == $root) {
                unset($tree[$i]);
                $child->setCap($this->level);       	
            	$this->mangTam[]=$child;                
                $this->level++;
                $this->xuatMenu($tree, $child->getTermTaxonomyId());
                $this->level--;
            } 
            
        }
        return $this->mangTam;
 
    }

}