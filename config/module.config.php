<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'S3UTaxonomy\Controller\Index' => 'S3UTaxonomy\Controller\IndexController',
            'S3UTaxonomy\Controller\Taxonomy' => 'S3UTaxonomy\Controller\TaxonomyController',
		),
	),
    'router' => array(
        'routes' => array(
            's3u_taxonomy' => array(
                'type'    => 'literal', 
                'options' => array(
                    'route'    => '/s3u-taxonomy',                     
                    'defaults' => array(
                       '__NAMESPACE__'=>'S3UTaxonomy\Controller',
                        'controller' => 'Index',
                        'action'     => 'index',
                    ),
                ),                
                'may_terminate' => true,
                'child_routes' => array(            
                    'taxonomys' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:action][/:id]',
                            'constraints' => array(                            
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'=>'[0-9]+',
                            ),                                                     
                        ),    
                    ),                                  
                ),
            ),
            'taxonomy'=>array(
                'type'    => 'literal', 
                'options' => array(
                    'route'    => '/taxonomy',                     
                    'defaults' => array(
                       '__NAMESPACE__'=>'S3UTaxonomy\Controller',
                        'controller' => 'Taxonomy', 
                        'action'     => 'taxonomyIndex',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(            
                    'childTaxonomy' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[/:tax]',
                            'constraints' => array(                            
                                'tax'     => '[a-zA-Z][a-zA-Z0-9_-]*',                                
                            ), 
                        ),
                        'may_terminate' => true,
                        'child_routes' => array( 
                            'crudChildTaxonomy' => array(
                                'type'    => 'Segment',
                                'options' => array(
                                    'route'    => '[/:action][/:id]',
                                    'constraints' => array(
                                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',                                        
                                            'id'=>'[0-9]+',
                                    ), 
                                ),                                   
                            ),  
                        ),  
                    ),                                  
                ),   
            ),
         ),
     ),

	'view_manager' => array(
		'template_path_stack' => array(
			'tax' => __DIR__ . '/../view'
		)
	),

    'view_helpers'=>array(
        'invokables'=>array(
            'makeArrayCollection'=>'S3UTaxonomy\View\Helper\MakeArrayCollection',  

        ),
    ),


      'controller_plugins' => array(
        'invokables' => array(
            'tree_plugin' => 'S3UTaxonomy\Controller\Plugin\TreePlugin', 
        ),
        'factories'=>array(
            'taxonomy_function' => function($sm){
                $entityManager=$sm->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                $taxonomyFunction=new S3UTaxonomy\Controller\Plugin\TaxonomyFunction();
                $taxonomyFunction->setEntityManager($entityManager);
                return $taxonomyFunction;
            },
        ),
        'shared'=>array(
            'taxonomy_function'=>false,
        ),
    ),


	'doctrine' => array(
        'driver' => array(

            's3u_taxonomy_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__.'/../src/S3UTaxonomy/Entity',//Edit
                ),
            ),

            'orm_default' => array(
                'drivers' => array(

                    'S3UTaxonomy\Entity' => 's3u_taxonomy_annotation_driver'//Edit
                )
            )
        )
    ),
);