<?php
namespace S3UTaxonomy\Controller\Plugin;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
 
class TaxonomyFunction extends AbstractPlugin{
	public function getIdTermTaxonomy(string $nameTermTaxonomy)
	{
		
		$entityManager= $this->getEntityManager();    
	    $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
	    $queryBuilder = $repository->createQueryBuilder('t');
	    $queryBuilder->add('where','t.taxonomy=\''.$tax.'\'');
	    $query = $queryBuilder->getQuery();
	    $termTaxonomys = $query->execute();
	}
}
?>