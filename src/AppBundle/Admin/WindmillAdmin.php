<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Windmill;
use AppBundle\Entity\WindmillBlade;
use Oh\GoogleMapFormTypeBundle\Form\Type\GoogleMapType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class WindmillAdmin
 *
 * @category Admin
 * @package  AppBundle\Admin
 * @author   Anton Serra <aserratorta@gmail.com>
 */
class WindmillAdmin extends AbstractBaseAdmin
{
    protected $classnameLabel = 'Aerogenerador';
    protected $baseRoutePattern = 'windfarms/windmill';
    protected $datagridValues = array(
        '_sort_by'    => 'code',
        '_sort_order' => 'asc',
    );

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General', $this->getFormMdSuccessBoxArray(4))
            ->add(
                'windfarm',
                'sonata_type_model',
                array(
                    'label'    => 'Parc Eòlic',
                    'btn_add'  => false,
                    'required' => true,
                    'query'    => $this->wfr->findAllSortedByNameQ()
                )
            )
            ->add(
                'code',
                null,
                array(
                    'label' => 'Codi',
                )
            )
            ->end()
            ->with('Controls', $this->getFormMdSuccessBoxArray(4))
            ->add(
                'turbine',
                'sonata_type_model',
                array(
                    'label'      => 'Turbina',
                    'btn_add'    => true,
                    'btn_delete' => false,
                    'required'   => true,
                    'query'      => $this->tr->findAllSortedByModelQ()
                )
            )
            ->add(
                'bladeType',
                'sonata_type_model',
                array(
                    'label'      => 'Tipus Pala',
                    'btn_add'    => true,
                    'btn_delete' => false,
                    'required'   => true,
                    'query'      => $this->br->findAllSortedByModelQ()
                )
            )
            ->end();
        if ($this->id($this->getSubject())) { // is edit mode, disable on new subjects
            $formMapper
                ->with('Pales', $this->getFormMdSuccessBoxArray(4))
                ->add(
                    'windmillBlades',
                    'sonata_type_collection',
                    array(
                        'label'              => ' ',
                        'required'           => false,
                        'btn_add'            => false,
                        'cascade_validation' => true,
                        'type_options'       => array(
                            'delete' => false,
                        )
                    ),
                    array(
                        'edit'     => 'inline',
                        'inline'   => 'table',
                        'sortable' => 'position',
                    )
                )
                ->end();
        }
        $formMapper
            ->with('Geolocalització', $this->getFormMdSuccessBoxArray(12))
            ->add(
                'latLng',
                GoogleMapType::class,
                array(
                    'label'    => 'Mapa',
                    'required' => false
                )
            )
            ->end();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'windfarm.customer',
                null,
                array(
                    'label' => 'Client',
                )
            )
            ->add(
                'windfarm',
                null,
                array(
                    'label' => 'Parc Eòlic',
                )
            )
            ->add(
                'code',
                null,
                array(
                    'label' => 'Codi',
                )
            )
            ->add(
                'turbine',
                null,
                array(
                    'label' => 'Turbina',
                )
            )
            ->add(
                'bladeType',
                null,
                array(
                    'label' => 'Tipus Pala',
                )
            )
            ->add(
                'windfarm.manager',
                null,
                array(
                    'label' => 'Administrador',
                )
            );
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);
        $listMapper
            ->add(
                'windfarm',
                null,
                array(
                    'label'    => 'Parc Eòlic',
                    'sortable'                         => true,
                    'sort_field_mapping'               => array('fieldName' => 'name'),
                    'sort_parent_association_mappings' => array(array('fieldName' => 'windfarm')),
                )
            )
            ->add(
                'code',
                null,
                array(
                    'label'    => 'Codi',
                    'editable' => true,
                )
            )
            ->add(
                'turbine',
                null,
                array(
                    'label'    => 'Turbina',
                    'sortable'                         => true,
                    'sort_field_mapping'               => array('fieldName' => 'model'),
                    'sort_parent_association_mappings' => array(array('fieldName' => 'turbine')),
                )
            )
            ->add(
                'bladeType',
                null,
                array(
                    'label'    => 'Tipus Pala',
                    'sortable'                         => true,
                    'sort_field_mapping'               => array('fieldName' => 'model'),
                    'sort_parent_association_mappings' => array(array('fieldName' => 'bladeType')),
                )
            )
            ->add(
                '_action',
                'actions',
                array(
                    'label'   => 'Accions',
                    'actions' => array(
                        'edit'   => array('template' => '::Admin/Buttons/list__action_edit_button.html.twig'),
                        'delete' => array('template' => '::Admin/Buttons/list__action_delete_button.html.twig'),
                    )
                )
            );
    }

    /**
     * Every new Windmill persist 3, and only 3 new linked blades
     *
     * @param Windmill $object
     */
    public function prePersist($object)
    {
        $blade1 = new WindmillBlade();
        $blade1
            ->setWindmill($object)
            ->setCode($object->getCode() . '/1');
        $blade2 = new WindmillBlade();
        $blade2
            ->setWindmill($object)
            ->setCode($object->getCode() . '/2');
        $blade3 = new WindmillBlade();
        $blade3
            ->setWindmill($object)
            ->setCode($object->getCode() . '/3');
        $object
            ->addWindmillBlade($blade1)
            ->addWindmillBlade($blade2)
            ->addWindmillBlade($blade3);
    }
}
