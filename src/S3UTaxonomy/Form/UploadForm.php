<?php
namespace S3UTaxonomy\Form;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

class UploadForm extends Form
{
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        // File Input
        $file = new Element\File('image-file');
        $file->setLabel('Image Upload')
             ->setAttribute('id', 'image-file')
        	 ->setAttribute('multiple', true);   // That's it
        $this->add($file);  
    }

    // input filter
    public function addInputFilter()
    {
        $inputFilter = new \Zend\InputFilter\InputFilter();

        // File Input
        $fileInput = new \Zend\InputFilter\FileInput('image-file');
        $fileInput->setRequired(true);

        // You only need to define validators and filters
        // as if only one file was being uploaded. All files
        // will be run through the same validators and filters
        // automatically.
        $fileInput->getValidatorChain()
            ->attachByName('filesize',      array('max' => 200000000))
            ->attachByName('filemimetype',  array('mimeType' => 'image/png,image/x-png,image/jpg,image/jpeg'))
            ->attachByName('fileimagesize', array('maxWidth' => 10000, 'maxHeight' => 10000));

        // All files will be renamed, i.e.:
        //   ./data/tmpuploads/avatar_4b3403665fea6.png,
        //   ./data/tmpuploads/avatar_5c45147660fb7.png
        $fileInput->getFilterChain()->attachByName(
            'filerenameupload',
            array(
                'target'    => './public/img/.jpg',
                'randomize' => true,
            )
        );
        $inputFilter->add($fileInput);

        $this->setInputFilter($inputFilter);
    }
}
?>