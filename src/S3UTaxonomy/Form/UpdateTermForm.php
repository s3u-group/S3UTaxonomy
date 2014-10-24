<?php
namespace S3UTaxonomy\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class UpdateTermForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('update-term-form');

        // The form will hydrate an object of type "BlogPost"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the user fieldset, and set it as the base fieldset
        $TermFieldset = new TermFieldset($objectManager);
        $TermFieldset->setUseAsBaseFieldset(true);
        $this->add($TermFieldset);

        // … add CSRF and submit elements …

        $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submit',
             ),
         ));    

        // Optionally set your validation group here
    }
}
?>