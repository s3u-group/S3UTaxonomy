<?php namespace S3UTaxonomy\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use S3UTaxonomy\Entity\ZfTerm;
 use S3UTaxonomy\Entity\ZfTermTaxonomy;
 use Zend\ServiceManager\ServiceManager;
 use S3UTaxonomy\Form\ZfTermTaxonomyForm;
 use S3UTaxonomy\Form\ZfTermForm;
 use S3UTaxonomy\Form\ChildZfTermTaxonomyForm;

 use BaconStringUtils\Slugifier;
 use BaconStringUtils\UniDecoder;

  use S3UTaxonomy\Form\CreateTermTaxonomyForm;
 

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
        $objectManager=$this->getEntityManager();
        $zfTermTaxonomy=new ZfTermTaxonomy();
        $form= new CreateTermTaxonomyForm($objectManager);
        $form->bind($zfTermTaxonomy); 
        $request = $this->getRequest();
        if ($request->isPost())
        { 
            $form->setData($request->getPost());
            var_dump($zfTermTaxonomy);
            //$zfTermTaxonomy->getTermId()->setSlug('thanh');
            //$zfTermTaxonomy->getTermId()->setTermGroup(0);
            if ($form->isValid()) {
            die(var_dump($zfTermTaxonomy));


            //    var_dump($request->getPost());
              //  die(var_dump($zfTermTaxonomy->getTerm()));                     
                $objectManager->persist($zfTermTaxonomy);
                $objectManager->flush();
                return $this->redirect()->toRoute('taxonomy');

            }
            else
            {
                die(var_dump($form->getMessages()));
            }

        }
        return array(
          'form' => $form, 
          'taxs'=> $tax,
        );
    }

 	public function taxonomyAddgidoAction()
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
        
    //========================================================================================    
             //$name=$request->getPost()->taxonomy; 
              die(var_dump($request->getPost()));
             $form->setData($request->getPost());

             $slugifier=new Slugifier;
             $decoder=new UniDecoder;   
             $slug=$slugifier->slugify($decoder->decode($name));  
                          
             $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
             $queryBuilder = $repository->createQueryBuilder('t');
             $queryBuilder->add('where','t.name =\''.$name.'\'');
             $query = $queryBuilder->getQuery(); 
             $checkTerm = $query->execute();
             
             if(!$checkTerm)
             {
               
                
                
                $zfTerm=new ZfTerm();
                $formTerm= new ZfTermForm($objectManager);
                $formTerm->bind($zfTerm);
                $zfTerm->setName($request->getPost()->taxonomy);
                $zfTerm->setSlug($slug);
                $zfTerm->setTermGroup(0);
                $objectManager->persist($zfTerm);
                $objectManager->flush();

                if ($form->isValid()) {
                 
                    $objectManager->persist($zfTermTaxonomy);
                    $objectManager->flush();
                    return $this->redirect()->toRoute('taxonomy');

                }

             } 
             else
             {
                return array(
                    'form' => $form,
                    'checkTermTaxonomy'=>1,
                );
             }   
  //========================================================================================
    }        
    return array(
      'form' => $form, 
      'taxs'=> $tax,
      'name'=>$termId[0]->getName(),
      'checkTermTaxonomy'=>0,
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