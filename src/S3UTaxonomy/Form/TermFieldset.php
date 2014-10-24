<?php

namespace S3UTaxonomy\Form;
use S3UTaxonomy\Entity\ZfTerm;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class TermFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('term');

        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new ZfTerm());

         $this->add(array(
             'name' => 'termId',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'slug',
             'type' => 'Hidden',
         ));
                
         $this->add(array(
             'name' => 'name',
             'type' => 'Text',
             'options' => array(
                 'label' => 'Tên term',
             ),
             'attributes'=>array('required'=>'required'),
         ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'name' => array(
                'required' => true
            ),
        );
    }
}