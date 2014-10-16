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

        $entityManager=$this->getEntityManager();
        $query = $entityManager->createQuery("SELECT distinct tt.taxonomy FROM S3UTaxonomy\Entity\ZfTermTaxonomy tt");
        $distincTermTaxonomys = $query->getResult();
    


        $objectManager= $this->getEntityManager();
        $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.term_id =0');
        $query = $queryBuilder->getQuery();
        $termTaxonomys = $query->execute();

 		$objectManager=$this->getEntityManager();
 		$zfTermTaxonomys=$objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->findAll();

 		return array(
            'zfTermTaxonomys'=>$zfTermTaxonomys,
            'distincTermTaxonomys'=>$distincTermTaxonomys,
            'termTaxonomys'=>$termTaxonomys,
        );
 	}

 	public function addAction()
 	{
         
 		 $objectManager=$this->getEntityManager();


         $zfTermTaxonomy=new ZfTermTaxonomy();
         $form= new ZfTermTaxonomyForm($objectManager);
         $form->bind($zfTermTaxonomy);

         $request = $this->getRequest();
         if ($request->isPost()) {     
             
             $taxonomy=$request->getPost()->taxonomy;            
             $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
             $queryBuilder = $repository->createQueryBuilder('tt');
             $queryBuilder->add('where','tt.taxonomy =\''.$taxonomy.'\'');
             $query = $queryBuilder->getQuery(); 
             $checkTermTaxonomy = $query->execute();
             if(!$checkTermTaxonomy)
             {
                $form->setData($request->getPost()); 
                if ($form->isValid()) {
                   $objectManager->persist($zfTermTaxonomy);
                   $objectManager->flush();

                   return $this->redirect()->toRoute('s3u_taxonomy');
                }

             } 
             else
             {
                return array(
                    'form' => $form,
                    'checkTermTaxonomy'=>0,

                );
             }            
         }         
       
         return array(
            'form' => $form, 
            'checkTermTaxonomy'=>1,           
         );
           
 	}

 	public function editAction()
 	{
 	}

 	public function deleteAction()
 	{
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('s3u_taxonomy');
        }
        $objectManager= $this->getEntityManager();
        $form = new ZfTermTaxonomyForm($objectManager); 
        die(var_dump($id));
        $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.taxonomy =\''.$id.'\'');
        $query = $queryBuilder->getQuery(); 
        $termTaxonomy = $query->execute();
        //die(var_dump($termTaxonomy));
        //$termTaxonomy = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->find($id);
        if($termTaxonomy)
        {
            $objectManager->remove($termTaxonomy);
            $objectManager->flush();
            
        }
        
        return $this->redirect()->toRoute('s3u_taxonomy');

 	}
 }
?>