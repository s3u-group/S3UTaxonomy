<?php 
namespace S3UTaxonomy\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;
use S3UTaxonomy\Form\TermTaxonomyFieldset;


class CreateTermTaxonomyForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('create_taxonomy');

        // The form will hydrate an object of type "BlogPost"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the user fieldset, and set it as the base fieldset
        $termTaxonomyFieldset = new TermTaxonomyFieldset($objectManager);
        $termTaxonomyFieldset->setUseAsBaseFieldset(true);
        $this->add($termTaxonomyFieldset);

        // … add CSRF and submit elements …

        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf'
        ));

        $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submitbutton',
             ),
         ));    

        // Optionally set your validation group here
    }
}