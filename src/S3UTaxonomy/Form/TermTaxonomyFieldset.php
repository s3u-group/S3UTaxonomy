<?php


namespace S3UTaxonomy\Form;
use S3UTaxonomy\Entity\ZfTermTaxonomy;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use S3UTaxonomy\Form\TermFieldset;

class TermTaxonomyFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('term-taxonomy');

        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new ZfTermTaxonomy());

             $termFieldset = new TermFieldset($objectManager);
             $termFieldset->setUseAsBaseFieldset(true);
             $termFieldset->setName('termId');
             $this->add($termFieldset);
 
 
         $this->add(array(
             'name' => 'termTaxonomyId',
             'type' => 'Hidden',
         ));

         // làm sao lấy được term_id bên bảng term lúc vừa thêm vào


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
             'name' => 'parent',
             'type' => 'Select',
             'options' => array(
                 'label' => 'Cha',
                 'empty_option'=>'--Chọn giá trị--',
             ),
         ));

       
    }


   

    public function getInputFilterSpecification()
    {
        return array(
            'taxonomy' => array(
                'required' => false,
            ),

            'parent' => array(
                'required' => false,
            ),
        );
    }
}