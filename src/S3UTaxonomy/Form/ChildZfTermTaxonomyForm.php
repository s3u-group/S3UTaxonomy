<?php 
namespace S3UTaxonomy\Form;

use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use S3UTaxonomy\Entity\ZfTerm;
use S3UTaxonomy\Entity\ZfTermTaxonomy;

 class ChildZfTermTaxonomyForm extends Form
 {
     private $om;

     public function __construct(ObjectManager $objectManager,$id)
     {        
         // we want to ignore the name passed
         parent::__construct('s3u_taxonomy');

         $this->om=$objectManager;

         $this->setHydrator(new DoctrineHydrator($objectManager))        
              ->setObject(new ZfTermTaxonomy());
// Định nghĩa các element trong form
         $this->add(array(
             'name' => 'term_taxonomy_id',
             'type' => 'Hidden',
         ));
                
         $this->add(array(
             'name' => 'taxonomy',
             'type' => 'Text',
             'options' => array(
                 'label' => 'Tên taxonomy',
             ),
             'attributes'=>array('required'=>'required'),
         ));

         $this->add(array(
             'name' => 'description',
             'type' => 'Text',
             'options' => array(
                 'label' => 'Mô tả',
             ),             
         ));

         $this->add(array(
             'name' => 'taxonomy',
             'type' => 'Select',
             'options' => array(
                 'label' => 'Cha',
             'empty_option'=>'--Chọn giá trị--',
             'value_options'=>$this->getTaxonomyOption($id),//Thêm $id
             ),
         ));
            
         $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submitbutton',
             ),
         ));         
     }

     public function getTaxonomyOption($id)
     {
        $options=array();
        $txq=$this->om->getRepository('S3UTaxonomy\Entity\ZfTermTaxonomy');
        //die(var_dump($id));
        $queryBuilder=$txq->createQueryBuilder('tax');
        $queryBuilder->add('where', 'tax.term_id ='.$id);
        $query = $queryBuilder->getQuery();
        $taxs = $query->execute();
            
        foreach ($taxs as $tax)
        {
            $options[$tax->getId()]=$tax->getTaxonomy();
        }
        //var_dump($options);
        return $options;
     }        
 }
 ?>