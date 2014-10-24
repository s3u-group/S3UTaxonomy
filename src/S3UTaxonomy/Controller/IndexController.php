<?php namespace S3UTaxonomy\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use S3UTaxonomy\Entity\ZfTerm;
 use S3UTaxonomy\Entity\ZfTermTaxonomy;
 use Zend\ServiceManager\ServiceManager;
 use S3UTaxonomy\Form\ZfTermTaxonomyFieldset;
 use S3UTaxonomy\Form\ZfTermFieldset;
 use S3UTaxonomy\Form\CreateTaxonomyForm;
 use S3UTaxonomy\Form\CreateTermTaxonomyForm;

 use BaconStringUtils\Slugifier;
 use BaconStringUtils\UniDecoder;

 

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
    
        $objectManager= $this->getEntityManager();
        $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.parent is NULL');
        $query = $queryBuilder->getQuery();
        $termTaxonomys = $query->execute();
 		
 		return array(
            'termTaxonomys'=>$termTaxonomys,
        );
 	}

 	public function addAction()
 	{

        $objectManager=$this->getEntityManager();
        $zfTermTaxonomy=new ZfTermTaxonomy();

        $form= new CreateTermTaxonomyForm($objectManager);
        $form->bind($zfTermTaxonomy); 
        
        //$request = $this->getRequest();

        return array(
            'form' => $form, 
            'checkTermTaxonomy'=>1,           
         );
        /* $objectManager=$this->getEntityManager();

         $zfTerm=new ZfTerm();
         $form= new ZfTermForm($objectManager);
         $form->bind($zfTerm);
         $request = $this->getRequest();
         if ($request->isPost()) {     
             
             $name=$request->getPost()->name; 
             $slugifier=new Slugifier;
             $decoder=new UniDecoder;   
             $slug=$slugifier->slugify($decoder->decode($name));     
             //die(var_dump($slug));      
             $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
             $queryBuilder = $repository->createQueryBuilder('t');
             $queryBuilder->add('where','t.name =\''.$name.'\'');
             $query = $queryBuilder->getQuery(); 
             $checkTerm = $query->execute();
             //die(var_dump($checkTerm));
             if(!$checkTerm)
             {
                $form->setData($request->getPost()); 
                if ($form->isValid()) {
                    $zfTerm->setSlug($slug);
                    $zfTerm->setTermGroup(0);
                    $objectManager->persist($zfTerm);
                    $objectManager->flush();

                     $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
                     $queryBuilder = $repository->createQueryBuilder('t');
                     $queryBuilder->add('where','t.name =\''.$name.'\'');
                     $query = $queryBuilder->getQuery(); 
                     $idTerm= $query->execute();



                     $zfTermTaxonomy=new ZfTermTaxonomy();
                     $formTermTaxonomy= new ZfTermTaxonomyForm($objectManager);
                                         
                     $zfTermTaxonomy->setTermId($idTerm[0]->getTermId());
                     //die(var_dump($idTerm[0]->getTermId()));
                     $zfTermTaxonomy->setTaxonomy($slug);                     
                     $zfTermTaxonomy->setDescription('Taxonomy');
                     $zfTermTaxonomy->setCount(0);
                     $formTermTaxonomy->bind($zfTermTaxonomy); 
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
         );*/
         
 	}

 	public function editAction()
 	{        
        $entityManager=$this->getEntityManager();
        $objectManager=$this->getEntityManager();
        $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('s3u_taxonomy', array(
                 'action' => 'add'
             ));
         }

         $form= new ZfTermForm($objectManager);         
         $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm')->find($id);        
         
         $form->bind($repository);           
         $request = $this->getRequest();         
         if ($request->isPost()) {
             
             // lấy taxonomy theo id ra;
             
             $suaTaxonomy=$repository->getName();
             $suaSlug=$repository->getSlug();
             //die(var_dump($suaTaxonomy));
             $rq=$request->getPost()->name;
             $slugifier=new Slugifier;
             $decoder=new UniDecoder;   
             $slug=$slugifier->slugify($decoder->decode($rq)); 
             //die(var_dump($rq));

             // kiểm tra trong csdl có rq chưa
             $kiemTraTonTai = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
             $queryBuilder = $kiemTraTonTai->createQueryBuilder('t');             
             $queryBuilder->add('where','t.name=\''.$rq.'\'');       
             $query = $queryBuilder->getQuery();        
             $kqKiemTraTonTai = $query->execute();
             if($suaTaxonomy==$rq)
             {
                return $this->redirect()->toRoute('s3u_taxonomy');
             }
             if($kqKiemTraTonTai)
             {
                 return array(
                     'id' => $id,
                     'form' => $form,
                     'coKiemTraTonTai' => 1,
                 );
             }
             else
             {
                 //die(var_dump($suaSlug));
                 $repository->setName($rq);
                 $repository->setSlug($slug);
                 $entityManager->merge($repository);
                 $objectManager->flush();
              
                 $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
                 $queryBuilder = $repository->createQueryBuilder('tt');             
                 $queryBuilder->add('where','tt.taxonomy=\''.$suaSlug.'\'');       
                 $query = $queryBuilder->getQuery();        
                 $termTaxonomys = $query->execute();
                 //die(var_dump($slug));
                 foreach ($termTaxonomys as $termTaxonomy) {
                    $entityManager=$this->getEntityManager();
                    $termTaxonomy->setTaxonomy($slug);
                    $entityManager->merge($termTaxonomy);
                    $entityManager->flush();                 
                 }
                 
                 return $this->redirect()->toRoute('s3u_taxonomy');
             }
         }
         //die(var_dump($form));
         return array(
             'id' => $id,
             'form' => $form,
             'coKiemTraTonTai' => 0,
         );

 	}

 	public function deleteAction()
 	{        
        $id = $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('s3u_taxonomy');
        }

        $objectManager= $this->getEntityManager();        
        
        $term = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm')->find($id);
        $taxonomy=$term->getSlug();
        $form = new ZfTermTaxonomyForm($objectManager);

        $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.taxonomy =\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery(); 
        $termTaxonomys = $query->execute();        
        
        if($termTaxonomys)
        {
            foreach ($termTaxonomys as $termTaxonomy) {
                //$termTaxonomy = new ZfTermTaxonomyForm();
                $entityManager=$this->getEntityManager();
                $termTaxonomy->setParent(NULL);
                $entityManager->merge($termTaxonomy);
                $entityManager->flush();
            }
            foreach ($termTaxonomys as $termTaxonomy) {

                $termId=$termTaxonomy->getTermId();
                $objectManager->remove($termTaxonomy);
                $objectManager->flush();
                $termId = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm')->find($termId);                
                // kiểm tra có ai xài chung term_id của thằng này nữa không nếu không thì xóa ở bảng term luôn
                //1. kiểm tra xem có ai trong bảng termtaxonomy xài thằng này ko
                $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
                $queryBuilder = $repository->createQueryBuilder('tt');
                $queryBuilder->add('where','tt.term_id =\''.$termId->getTermId().'\'');
                $query = $queryBuilder->getQuery(); 
                $kiemTraTermTaxonomy = $query->execute();                
                if(!$kiemTraTermTaxonomy)
                {
                    //2. lệnh xóa bỏ trong bảng term
                    $deleteTerm = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm')->find( $termId);                    
                    $objectManager->remove($deleteTerm);
                    $objectManager->flush();                     
                }                
            } 
        }                       
        return $this->redirect()->toRoute('s3u_taxonomy');        
 	}
 }
?>