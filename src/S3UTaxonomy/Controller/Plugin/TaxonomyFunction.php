<?php
namespace S3UTaxonomy\Controller\Plugin;
 use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceManager;
 
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
}