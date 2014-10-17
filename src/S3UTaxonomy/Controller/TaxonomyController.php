<?php namespace S3UTaxonomy\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use S3UTaxonomy\Entity\ZfTerm;
 use S3UTaxonomy\Entity\ZfTermTaxonomy;
 use Zend\ServiceManager\ServiceManager;
 use S3UTaxonomy\Form\ZfTermTaxonomyForm;
 

 class TaxonomyController extends AbstractActionController
 {
 	 private $entityManager;

     public function getEntityManager()
     {
        if(!$this->entityManager)
        {
          $this->entityManager=$this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        return $this->entityManager;
    }
 	public function taxonomyIndexAction()
 	{
        if($this->params()->fromRoute('tax')==null)
        {
            return $this->redirect()->toRoute('s3u_taxonomy');
        }
 	}

 	public function taxonomyAddAction()
 	{
 		
 	}

 	public function taxonomyEditAction()
 	{
       
 	}

 	public function taxonomyDeleteAction()
 	{
       
 	}


 }
?>