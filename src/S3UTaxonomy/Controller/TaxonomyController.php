<?php namespace S3UTaxonomy\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use S3UTaxonomy\Entity\ZfTerm;
 use S3UTaxonomy\Entity\ZfTermTaxonomy;
 use Zend\ServiceManager\ServiceManager;
 use S3UTaxonomy\Form\ZfTermTaxonomyForm;
 use S3UTaxonomy\Form\ZfTermForm;
 use S3UTaxonomy\Form\ChildZfTermTaxonomyForm;

 use S3UTaxonomy\Form\TermTaxonomyFieldset;
 use S3UTaxonomy\Form\TermFieldset;

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
        $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('t');
        $queryBuilder->add('where','t.parent IS NULL and t.taxonomy=\''.$tax.'\'');
        $query = $queryBuilder->getQuery();
        $parentTermTaxonomy = $query->execute();  
         // Lấy mảng này ra rồi hiển thị ở phần chọn cha
        $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('t');
        $queryBuilder->add('where','t.parent IS NOT NULL and t.taxonomy=\''.$tax.'\'');
        $query = $queryBuilder->getQuery();
        $termTaxonomys = $query->execute();  
        //die(var_dump($termTaxonomys));    

        $zfTermTaxonomy=new ZfTermTaxonomy();
        $form= new CreateTermTaxonomyForm($objectManager);
        $form->bind($zfTermTaxonomy); 
        $request = $this->getRequest();
        if ($request->isPost())
        { 
            $form->setData($request->getPost());
            //$zfTermTaxonomy->getTermId()->setSlug('thanh');
            //$zfTermTaxonomy->getTermId()->setTermGroup(0);
            if ($form->isValid()) {
              // kiểm tra có chọn cha chưa, nếu chua thì cha nó là root
               if(!$zfTermTaxonomy->getParent())
               {
                  $zfTermTaxonomy->setParent($parentTermTaxonomy[0]->getTermTaxonomyId());
               }
               // kiểm tra trong bảng zfterm có tồn tại tên cần thêm chưa, nếu có thì sử dụng lại tên đó
               $term=$objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm'); 
               $queryBuilder = $term->createQueryBuilder('t');             
               $queryBuilder->add('where','t.name=\''.$zfTermTaxonomy->getTermId()->getName().'\'');       
               $query = $queryBuilder->getQuery();        
               $terms = $query->execute(); 
               if($terms)
               {
                  // kiểm tra cùng cấp và đã tồn tại tên termtaxonomy cần thêm, thì không cho thêm
                 $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
                 $queryBuilder = $repository->createQueryBuilder('t');
                 $queryBuilder->add('where','t.termId='.$terms[0]->getTermId().' and t.parent='.$zfTermTaxonomy->getParent().' and t.taxonomy=\''.$tax.'\'');
                 $query = $queryBuilder->getQuery();
                 $kiemTraTonTaiTrongCungTaxonomy = $query->execute(); 
                 //var_dump('TermId: '.$terms[0]->getTermId());
                 //var_dump('parent: '.$zfTermTaxonomy->getParent());
                 //die(var_dump($kiemTraTonTaiTrongCungTaxonomy));
                 if($kiemTraTonTaiTrongCungTaxonomy)
                 {
                  return array(
                    'form' => $form, 
                    'taxs'=> $tax,
                    'termTaxonomys'=>$termTaxonomys,
                    'coKiemTraTonTai'=>1,
                  );
                 }


                 // kiểm tra cùng cấp và đã tồn tại tên termtaxonomy cần thêm, thì không cho thêm
                 $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
                 $queryBuilder = $repository->createQueryBuilder('t');
                 $queryBuilder->add('where','t.parent='.$zfTermTaxonomy->getParent().' and t.taxonomy=\''.$tax.'\'');
                 $query = $queryBuilder->getQuery();
                 $kiemTraTonTaiTrongCungTaxonomy = $query->execute(); 
                 foreach ($kiemTraTonTaiTrongCungTaxonomy as $kt) {
                   if($kt->getTermId()->getName()==$zfTermTaxonomy->getTermId()->getName())
                   {
                    return array(
                      'form' => $form, 
                      'taxs'=> $tax,
                      'termTaxonomys'=>$termTaxonomys,
                      'coKiemTraTonTai'=>1,
                    );
                   }
                 }
                 
                 // kiểm tra trong cùng một taxonomy nếu chưa tồn tại  thì dùng cái có sẵn
                 $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
                 $queryBuilder = $repository->createQueryBuilder('t');
                 $queryBuilder->add('where','t.termId='.$terms[0]->getTermId().' and t.taxonomy=\''.$tax.'\'');
                 $query = $queryBuilder->getQuery();
                 $kiemTraTonTaiTrongCungTaxonomy = $query->execute(); 
                 if(!$kiemTraTonTaiTrongCungTaxonomy)
                 {
                  $zfTermTaxonomy->setTermId($terms[0]);
                 }
                 else
                 {
                  // số nối phía sau slug
                  $slugParent = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->find($zfTermTaxonomy->getParent());
                  $slugNoi=$slugParent->getTermId()->getSlug();

                  $zfTermTaxonomy->getTermId()->setSlug($zfTermTaxonomy->getTermId()->getSlug().'-'.$slugNoi);
                  $zfTermTaxonomy->getTermId()->setTermGroup(0);
                 }

               }
               if(!$terms)// chưa có thì thêm mới
               {
                $zfTermTaxonomy->getTermId()->setTermGroup(0);
                //$zfTermTaxonomy->setTaxonomy($zfTermTaxonomy->getTermId()->getSlug());
               }               
               $zfTermTaxonomy->setTaxonomy($tax);
               $objectManager->persist($zfTermTaxonomy);
               $objectManager->flush();
               return $this->redirect()->toRoute('taxonomy/childTaxonomy',array('tax'=>$tax));
            }
        }
        return array(
          'form' => $form, 
          'taxs'=> $tax,
          'termTaxonomys'=>$termTaxonomys,
          'coKiemTraTonTai'=>0,
        );
    }

 	public function hamBoCon($mangs,$id)
  {
   foreach ($mangs as $vt=>$mang) {
     if($mang->getParent()==$id)
     {
       unset($mangs[$vt]);
       $mangs=$this->hamBoCon($mangs,$mang->getTermTaxonomyId());
      
     }         
   }       
   return $mangs;
  }

 	public function taxonomyEditAction()
 	{  
    // lấy thông tin gửi từ form index-taxonomy
    $tax=$this->params()->fromRoute('tax');
    //die(var_dump($tax));
    if($tax==null)
    {
      return $this->redirect()->toRoute('s3u_taxonomy');
    }
    $id=$this->params()->fromRoute('id'); 
    if(!$id)
    {
      return $this->redirect()->toRoute('taxonomy/childTaxonomy',array('tax'=>$tax));
    }

    $entityManager=$this->getEntityManager();
    //tạo 1 form có cấu trúc của form termTaxonomy
    $termTaxonomy = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->find($id);
    
    $form= new CreateTermTaxonomyForm($entityManager);
    $form->bind($termTaxonomy);


    // lấy danh sách các taxonomy khác taxonomy hiện tại và loại bỏ con cháu
    $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
    $queryBuilder = $repository->createQueryBuilder('t');
    $queryBuilder->add('where','t.termTaxonomyId!='.$id.' and t.parent IS NOT NULL and t.taxonomy=\''.$tax.'\'');
    $query = $queryBuilder->getQuery();
    $termTaxonomys = $query->execute();
    $termTaxonomys=$this->hamBoCon($termTaxonomys,$id);
    $request = $this->getRequest();
    if ($request->isPost())
    {
      $form->setData($request->getPost());
      if ($form->isValid()) {

         // nếu không chọn cha thì mặc định nó là cấp lớn nhất trong taxonomy đó
        if(!$termTaxonomy->getParent())
        {
          $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
          $queryBuilder = $repository->createQueryBuilder('t');
          $queryBuilder->add('where','t.parent IS NULL and t.taxonomy=\''.$tax.'\'');
          $query = $queryBuilder->getQuery();
          $parentTermTaxonomy = $query->execute();  
          $termTaxonomy->setParent($parentTermTaxonomy[0]->getTermTaxonomyId());
        }

        $slugifier=new Slugifier;
        $decoder=new UniDecoder;   
        $slug=$slugifier->slugify($decoder->decode($termTaxonomy->getTermId()->getName()));
        $termTaxonomy->getTermId()->setSlug($slug);

        $term=$entityManager->getRepository('S3UTaxonomy\Entity\ZfTerm'); 
        $queryBuilder = $term->createQueryBuilder('t');             
        $queryBuilder->add('where','t.name=\''.$termTaxonomy->getTermId()->getName().'\'');       
        $query = $queryBuilder->getQuery();        
        $terms = $query->execute(); 
        if($terms)
        {
         //$termTaxonomy->setTermId($terms[0]);
         $termTaxonomy->getTermId()->setName($termTaxonomy->getTermId()->getName());

         // kiểm tra trong bảng termtaxonomy có termtaxonomy nào 
         $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
         $queryBuilder = $repository->createQueryBuilder('t');
         $queryBuilder->add('where','t.termId='.$terms[0]->getTermId().' and t.taxonomy=\''.$tax.'\'');
         $query = $queryBuilder->getQuery();
         $tonTaiTermTaxonomyBangTen = $query->execute();

         foreach ($tonTaiTermTaxonomyBangTen as $i) {
           if($i->getParent()==$termTaxonomy->getParent()&&$i->getTermTaxonomyId()!=$id)
           {
            return array(
              'form' => $form, 
              'taxs'=> $tax,
              'termTaxonomys'=>$termTaxonomys,
              'coKiemTraTonTai'=>1,
            );
           }
         }
         if($tonTaiTermTaxonomyBangTen)
         {
          $parentTermTaxonomy = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->find($termTaxonomy->getParent());
          $slug.='-'.$slugifier->slugify($decoder->decode($parentTermTaxonomy->getTermId()->getSlug()));
          $termTaxonomy->getTermId()->setSlug($slug);
         }

         
        } 
        $entityManager->flush();

        return $this->redirect()->toRoute('taxonomy/childTaxonomy',array('tax'=>$tax));        
      }
    }

    return array(
      'form' => $form, 
      'taxs'=> $tax,
      'termTaxonomys'=>$termTaxonomys,
      'coKiemTraTonTai'=>0,
    );
 	}

 	public function taxonomyDeleteAction()
 	{   
    $tax=$this->params()->fromRoute('tax');
    //die(var_dump($tax));
    if($tax==null)
    {
      return $this->redirect()->toRoute('s3u_taxonomy');
    }
    $id=$this->params()->fromRoute('id'); 
    if(!$id)
    {
      return $this->redirect()->toRoute('taxonomy/childTaxonomy',array('tax'=>$tax));
    }
   
    $objectManager=$this->getEntityManager();
    $termTaxonomy = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->find($id);
    //die(var_dump($termTaxonomy));
    // lưu cha của termTaxonomy cần xóa
    $cha=$termTaxonomy->getParent();
    $termIdCanXoa=$termTaxonomy->getTermId()->getTermId();

    // kiểm tra con của termTaxonomy cần xóa
    $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
    $queryBuilder = $repository->createQueryBuilder('t');
    $queryBuilder->add('where','t.parent='.$id);
    $query = $queryBuilder->getQuery();
    $conCuaTermTaxonomyCanXoa = $query->execute();  
    foreach ($conCuaTermTaxonomyCanXoa as $con) {
      $con->setParent($cha);
    }

    
    $objectManager->remove($termTaxonomy);
    $objectManager->flush();

    $repository = $objectManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
    $queryBuilder = $repository->createQueryBuilder('t');
    $queryBuilder->add('where','t.termId='.$termIdCanXoa);
    $query = $queryBuilder->getQuery();
    $kiemTraSuDungTermId = $query->execute();  
    if(!$kiemTraSuDungTermId)
    {
      $xoaTerm=$objectManager->getRepository('S3UTaxonomy\Entity\ZfTerm')->find($termIdCanXoa);
      $objectManager->remove($xoaTerm);
      $objectManager->flush();

    }
    //die(var_dump('Tới đây rồi'));

    return $this->redirect()->toRoute('taxonomy/childTaxonomy',array('tax'=>$tax));

 	}
 }
?>