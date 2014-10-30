<?php
namespace S3UTaxonomy\Controller\Plugin;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceManager;
use S3UTaxonomy\Controller\Plugin\TreePlugin;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
 
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


    // hàm lấy cấp của taxonomy
    private $level=0;
    private $mangTam=array();
    public function outputTree($tree, $root = null) {          
        foreach($tree as $i=>$child) {           
            $parent = $child->getParent();
            if($parent == $root) {
                unset($tree[$i]);
                $child->setCap($this->level);           
                $this->mangTam[]=$child;                
                $this->level++;
                $this->outputTree($tree, $child->getTermTaxonomyId());
                $this->level--;
            } 
            
        }
        return $this->mangTam;
 
    }

    // lấy danh sách các taxonomy    
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


    // lấy  id của một termtaxonomy
    public function getIdTermTaxonomy($taxonomy, $name, $slug)// đưa taxonomy dưới dạng slug
    {
        $id=NULL;
        $entityManager=$this->getEntityManager();

        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTerm');
        $queryBuilder = $repository->createQueryBuilder('t');
        $queryBuilder->add('where','t.name=\''.$name.'\''.' and t.slug=\''.$slug.'\'');
        $query = $queryBuilder->getQuery();
        $zfTerm = $query->execute();
        //die(var_dump($zfTerm));
        if(!$zfTerm)
        {
            return $id;
        }

        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.termId='.(int)$zfTerm[0]->getTermId().' and tt.taxonomy=\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery();
        $zfTermTaxonomy = $query->execute();
        if(!$zfTermTaxonomy)
        {
            return $id;
        }
        $id=(int)$zfTermTaxonomy[0]->getTermTaxonomyId();
        return $id;
    }


    // lấy toàn bộ dữ liệu trong một taxonomy
    // đưa taxonomy dưới dạng slug
    public function getListChildTaxonomy($taxonomy)
    {
        $listChildTaxonomys = array();
        $entityManager=$this->getEntityManager();
        $hydrator = new DoctrineHydrator($entityManager);

        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.taxonomy=\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery();
        $zfTermTaxonomys = $query->execute(); 
        if(!$zfTermTaxonomys)
        {
            return $listChildTaxonomys;
        }

        
        $zfTermTaxonomys=$this->outputTree($zfTermTaxonomys, $root = null); 
        if(!$zfTermTaxonomys)
        {
            return $listChildTaxonomys;
        }

        foreach ($zfTermTaxonomys as $zfTermTaxonomy) {
            $cap=$zfTermTaxonomy->getCap();
            $dataArray = $hydrator->extract($zfTermTaxonomy);
            $dataArray['termId']=$hydrator->extract($dataArray['termId']);
            $dataArray['cap']=$cap;
            $listChildTaxonomys[]=$dataArray;
        }
        return $listChildTaxonomys;
    }


    // lấy toàn bộ dữ liệu trong một taxonomy và sắp xếp theo id
    // tham số taxonomy là tham số dạng slug
    public function getListChildTaxonomyOrderById($taxonomy)
    {
        $listChildTaxonomys = array();
        $entityManager=$this->getEntityManager();
        $hydrator = new DoctrineHydrator($entityManager);

        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.taxonomy=\''.$taxonomy.'\''.'order by tt.termTaxonomyId');
        $query = $queryBuilder->getQuery();
        $zfTermTaxonomys = $query->execute(); 
        if(!$zfTermTaxonomys)
        {
            return $listChildTaxonomys;
        }
        
        $zfTermTaxonomys=$this->outputTree($zfTermTaxonomys, $root = null); 
        if(!$zfTermTaxonomys)
        {
            return $listChildTaxonomys;
        }

        foreach ($zfTermTaxonomys as $zfTermTaxonomy) {
            $cap=$zfTermTaxonomy->getCap();
            $dataArray = $hydrator->extract($zfTermTaxonomy);
            $dataArray['termId']=$hydrator->extract($dataArray['termId']);
            $dataArray['cap']=$cap;
            $listChildTaxonomys[]=$dataArray;
        }
        return $listChildTaxonomys;
    }



    // lấy phần tử và các con của nó theo id chuyền vào
    // tham số taxonomy là tham số dưới dạng slug và id là dạng số
    public function getChildTaxonomy($taxonomy, $id=null)
    {
        
        $entityManager=$this->getEntityManager();
        $hydrator = new DoctrineHydrator($entityManager);
        $listChildTaxonomys = array();

        if($id)
        {
            $root = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy')->find($id);
            $root=$root->getTermTaxonomyId();
        }
        else
        {
            $root=null;
        }
        

        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where','tt.taxonomy=\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery();
        $zfTermTaxonomys = $query->execute(); 
        if(!$zfTermTaxonomys)
        {
            return $listChildTaxonomys;
        }

        
        $zfTermTaxonomys=$this->outputTree($zfTermTaxonomys, $root); 
        if(!$zfTermTaxonomys)
        {
            return $listChildTaxonomys;
        }

        foreach ($zfTermTaxonomys as $zfTermTaxonomy) {
            $cap=$zfTermTaxonomy->getCap();
            $dataArray = $hydrator->extract($zfTermTaxonomy);
            $dataArray['termId']=$hydrator->extract($dataArray['termId']);
            $dataArray['cap']=$cap;
            $listChildTaxonomys[]=$dataArray;
        }
        return $listChildTaxonomys;
    }



    // lấy theo điều kiện loại trừ
    // tham số taxonomy là tham số dạng slug, array codition là mảng các id của taxonomy. 
    //ví dụ muốn bỏ ra termtaxonomy có id= 4 và 12 thì sẽ $condition sẽ là:
    //$condition=array(4,12);
    public function getListChildTaxonomyCondition($taxonomy, array $conditions)
    {
        $listChildTaxonomys = array();
        $entityManager=$this->getEntityManager();
        $hydrator = new DoctrineHydrator($entityManager);
        //die(var_dump($condition));
        $condit='';
        if($conditions)
        {
            
            foreach ($conditions as $condition) {
                $condit.=' tt.termTaxonomyId!='.$condition.' and';
                
            }
            //die(var_dump($condit));
        }
        $repository = $entityManager->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        $queryBuilder = $repository->createQueryBuilder('tt');
        $queryBuilder->add('where', $condit.' tt.taxonomy=\''.$taxonomy.'\'');
        $query = $queryBuilder->getQuery();
        $zfTermTaxonomys = $query->execute(); 
        if(!$zfTermTaxonomys)
        {
            return $listChildTaxonomys;
        }

       
        $zfTermTaxonomys=$this->outputTree($zfTermTaxonomys, $root = null); 
        if(!$zfTermTaxonomys)
        {
            return $listChildTaxonomys;
        }

        foreach ($zfTermTaxonomys as $zfTermTaxonomy) {
            $cap=$zfTermTaxonomy->getCap();
            $dataArray = $hydrator->extract($zfTermTaxonomy);
            $dataArray['termId']=$hydrator->extract($dataArray['termId']);
            $dataArray['cap']=$cap;
            $listChildTaxonomys[]=$dataArray;
        }
        return $listChildTaxonomys;
    }

}
?>
