<?php namespace S3UTaxonomy\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use S3UTaxonomy\Entity\ZfTerm;
 use S3UTaxonomy\Entity\ZfTermTaxonomy;
 use Zend\ServiceManager\ServiceManager;
 use S3UTaxonomy\Form\ZfTermTaxonomyForm;
 use S3UTaxonomy\Form\ChildZfTermTaxonomyForm;
 

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
    $tax=$this->params()->fromRoute('tax');
      
    if($tax==null)
    {
      return $this->redirect()->toRoute('s3u_taxonomy');
    }      

    $entityManager= $this->getEntityManager();

    
    $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
    $queryBuilder = $repository->createQueryBuilder('t');
    $queryBuilder->add('where','t.slug=\''.$tax.'\'');
    $query = $queryBuilder->getQuery();
    $taxonomy = $query->execute();
    
    $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
    $queryBuilder = $repository->createQueryBuilder('t');
    $queryBuilder->add('where','t.taxonomy=\''.$tax.'\'');
    $query = $queryBuilder->getQuery();
    $termTaxonomys = $query->execute();
  
    if($termTaxonomys==null)
    {
      return $this->redirect()->toRoute('s3u_taxonomy');
    }   
    $plugin=$this->TreePlugin();

    $termTaxonomys=$plugin->xuatMenu($termTaxonomys, $root = null);  
    return array(
      'termTaxonomys'=>$termTaxonomys,
      'taxonomy'  => $taxonomy,
    );
 	}

 	public function taxonomyAddAction()
 	{
    $tax=$this->params()->fromRoute('tax');
    //die(var_dump($tax));
    if($tax==null)
    {
      return $this->redirect()->toRoute('s3u_taxonomy');
    } 

    $entityManager= $this->getEntityManager();
    $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
    $queryBuilder = $repository->createQueryBuilder('t');
    $queryBuilder->add('where','t.slug=\''.$tax.'\'');
    $query = $queryBuilder->getQuery();
    $termId= $query->execute();

    if($termId==null)
    {
      return $this->redirect()->toRoute('s3u_taxonomy');
    }
    $id=$termId[0]->getTermId();
    //die(var_dump($id));

 		$objectManager=$this->getEntityManager();
    $zfTermTaxonomy=new ZfTermTaxonomy();
    $form= new ChildZfTermTaxonomyForm($objectManager,$id);
    $form->bind($zfTermTaxonomy);

    $request = $this->getRequest();
    if ($request->isPost())
    {
      die(var_dump('Post_OK'));
    }        
    return array(
      'form' => $form, 
      'taxs'=> $tax,
      'checkTermTaxonomy'=>1,           
    );
 	}

 	public function taxonomyEditAction()
 	{  
 	}

 	public function taxonomyDeleteAction()
 	{   
 	}
 }
?>