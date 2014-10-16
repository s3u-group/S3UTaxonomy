<?php namespace S3UTaxonomy\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use S3UTaxonomy\Entity\ZfTerm;
 use S3UTaxonomy\Entity\ZfTermTaxonomy;
 use Zend\ServiceManager\ServiceManager;
 use S3UTaxonomy\Form\ZfTermTaxonomyForm;

 class IndexController extends AbstractActionController
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
 	public function indexAction()
 	{
 		$objectManager=$this->getEntityManager();
 		$zfTermTaxonomys=$objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->findAll();

 		return array('zfTermTaxonomys'=>$zfTermTaxonomys);
 	}

 	public function addAction()
 	{
 		 $objectManager=$this->getEntityManager();
         $zfTermTaxonomy=new ZfTermTaxonomy();
         $form= new ZfTermTaxonomyForm($objectManager);
         $form->bind($zfTermTaxonomy);

         $request = $this->getRequest();
         if ($request->isPost()) {     
             //$form->setInputFilter($album->getInputFilter());
             $form->setData($request->getPost());
            
             if ($form->isValid()) {
               $objectManager->persist($zfTermTaxonomy);
               $objectManager->flush();

               return $this->redirect()->toRoute('s3u_taxonomy');
             }
         }         
         return array('form' => $form);     
 	}

 	public function editAction()
 	{
 	}

 	public function deleteAction()
 	{
 	}
 }
?>