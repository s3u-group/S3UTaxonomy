<?php namespace S3UTaxonomy\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use S3UTaxonomy\Entity\ZfTerm;
 use S3UTaxonomy\Entity\ZfTermTaxonomy;
 use Zend\ServiceManager\ServiceManager;
 use S3UTaxonomy\Form\ZfTermTaxonomyForm;
 use S3UTaxonomy\Form\ZfTermForm;

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
                     $formTermTaxonomy->bind($zfTermTaxonomy);                     
                     $zfTermTaxonomy->setTermId($idTerm[0]);
                     $zfTermTaxonomy->setTaxonomy($slug);                     
                     $zfTermTaxonomy->setDescription('Taxonomy');
                     $zfTermTaxonomy->setCount(0);
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
        $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('s3u_taxonomy', array(
                 'action' => 'add'
             ));
         }

         $objectManager=$this->getEntityManager();
         $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm')->find($id);         
         $form= new ZfTermForm($objectManager,$id);                               
         $form->bind($repository);
         $form->get('submit')->setAttribute('value', 'Sửa');
         //die(var_dump($repository));
         $request = $this->getRequest();
        
         if ($request->isPost()) {
             
             // lấy taxonomy theo id ra;
             $suaTaxonomy=$repository->getName();
             $rq=$request->getPost()->name;
             // kiểm tra trong csdl có rq chưa
             $kiemTraTonTai = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
             $queryBuilder = $kiemTraTonTai->createQueryBuilder('tt');             
             $queryBuilder->add('where','tt.taxonomy=\''.$rq.'\'');       
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
                 //die(var_dump($suaTaxonomy));
                 $form->setData($request->getPost());
                 $objectManager->flush();// flush
                 /*
                 $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
                 $queryBuilder = $repository->createQueryBuilder('tt');             
                 $queryBuilder->add('where','tt.taxonomy=\''.$suaTaxonomy.'\'');       
                 $query = $queryBuilder->getQuery();        
                 $termTaxonomys = $query->execute();
                 
                 foreach ($termTaxonomys as $termTaxonomy) {
                    $entityManager=$this->getEntityManager();
                    $termTaxonomy->setTaxonomy($rq);
                    $entityManager->merge($termTaxonomy);
                    $entityManager->flush();                 
                 }
                 */
                 return $this->redirect()->toRoute('s3u_taxonomy');
             }
         }
         return array(
             'id' => $id,
             'form' => $form,
             'coKiemTraTonTai' => 0,
         );
 	}

 	public function deleteAction()
 	{
        
        $taxonomy = $this->params()->fromRoute('id', 0);
        if (!$taxonomy) {
            return $this->redirect()->toRoute('s3u_taxonomy');
        }
        $objectManager= $this->getEntityManager();
        $form = new ZfTermTaxonomyForm($objectManager);
        $termTaxonomys = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->find($taxonomy);
        
        $taxonomy=$termTaxonomys->getTaxonomy();
        //die(var_dump($taxonomy));
        $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.taxonomy =\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery(); 
        $termTaxonomys = $query->execute();
        //die(var_dump($termTaxonomys));
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
                
                $objectManager->remove($termTaxonomy);
                $objectManager->flush();
               
            }
                
        }
    
        return $this->redirect()->toRoute('s3u_taxonomy');        

 	}

 }
?>