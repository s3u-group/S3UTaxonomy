<?php
namespace S3UTaxonomy\Controller\Plugin;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceManager;
use S3UTaxonomy\Controller\Plugin\TreePlugin;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
 
class TaxonomyFunction extends AbstractPlugin{	

	private $entityManager; 
    
	public function getEntityManager()
    {       
        return $this->entityManager;
    }
	
	public function setEntityManager($entityManager)
	{
		$this->entityManager=$entityManager;
	}


    private $level=0;
    private $mangTam=array();
    public function outputTree($tree, $root = null) {          
        die(var_dump($root));
        foreach($tree as $i=>$child) {           
            $parent = $child->getParent();
            if($parent == $root) {
                unset($tree[$i]);
                $child->setCap($this->level);           
                $this->mangTam[]=$child;                
                $this->level++;
                $this->outputTree($tree, $child->getTermTaxonomyId());
                $this->level--;
            } 
            
        }
        return $this->mangTam;
 
    }


	public function getListTaxonomy(){
		$entityManager=$this->getEntityManager();
        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('t');
        $queryBuilder->add('where','t.parent IS NULL');
        $query = $queryBuilder->getQuery();
        $listTaxonomys = $query->execute();
        $list=array();
        foreach ($listTaxonomys as $listTaxonomy) {
        	$list[$listTaxonomy->getTermTaxonomyId()]=$listTaxonomy->getTaxonomy();
        }
        return $list;
	}

    public function getIdTermTaxonomy($taxonomy, $name, $slug)
    {
        $entityManager=$this->getEntityManager();

        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
        $queryBuilder = $repository->createQueryBuilder('t');
        $queryBuilder->add('where','t.name=\''.$name.'\''.' and t.slug=\''.$slug.'\'');
        $query = $queryBuilder->getQuery();
        $zfTerm = $query->execute();
        //die(var_dump($zfTerm));


        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.termId='.(int)$zfTerm[0]->getTermId().' and tt.taxonomy=\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery();
        $zfTermTaxonomy = $query->execute();
        $id=(int)$zfTermTaxonomy[0]->getTermTaxonomyId();
        return $id;
    }


    public function getListChildTaxonomy($taxonomy)
    {
        $entityManager=$this->getEntityManager();
        $hydrator = new DoctrineHydrator($entityManager);

        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.taxonomy=\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery();
        $zfTermTaxonomys = $query->execute(); 

        $listChildTaxonomys = array();
        $zfTermTaxonomys=$this->outputTree($zfTermTaxonomys, $root = null); 

        foreach ($zfTermTaxonomys as $zfTermTaxonomy) {
            $cap=$zfTermTaxonomy->getCap();
            $dataArray = $hydrator->extract($zfTermTaxonomy);
            $dataArray['termId']=$hydrator->extract($dataArray['termId']);
            $dataArray['cap']=$cap;
            $listChildTaxonomys[]=$dataArray;
        }
        return $listChildTaxonomys;
    }



    public function getChildTaxonomy($taxonomy, int $id)
    {
        $entityManager=$this->getEntityManager();
        $hydrator = new DoctrineHydrator($entityManager);

        die(var_dump($id));
        $root = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->find($id);

        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.taxonomy=\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery();
        $zfTermTaxonomys = $query->execute(); 

        $listChildTaxonomys = array();
        die(var_dump($root));
        $zfTermTaxonomys=$this->outputTree($zfTermTaxonomys, $root->getTermTaxonomyId());
        die(var_dump($zfTermTaxonomys)); 

        foreach ($zfTermTaxonomys as $zfTermTaxonomy) {
            $cap=$zfTermTaxonomy->getCap();
            $dataArray = $hydrator->extract($zfTermTaxonomy);
            $dataArray['termId']=$hydrator->extract($dataArray['termId']);
            $dataArray['cap']=$cap;
            $listChildTaxonomys[]=$dataArray;
        }
        die(var_dump($zfTermTaxonomys));
        return $listChildTaxonomys;
    }
}
?>
