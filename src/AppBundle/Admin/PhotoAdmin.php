<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

/**
 * Class PhotoAdmin
 *
 * @category Admin
 * @package  AppBundle\Admin
 * @author   Anton Serra <aserratorta@gmail.com>
 */
class PhotoAdmin extends AbstractBaseAdmin
{
    protected $maxPerPage = 50;
    protected $classnameLabel = 'Foto';
    protected $baseRoutePattern = 'audits/photo';
    protected $datagridValues = array(
        '_sort_by'    => 'imageName',
        '_sort_order' => 'asc',
    );

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General', $this->getFormMdSuccessBoxArray(7))
            ->add(
                'bladeDamage',
                null,
                array(
                    'attr' => array(
                        'hidden' => true,
                    ),
                )
            )
            ->add(
                'imageFile',
                'file',
                array(
                    'label'       => 'Foto',
                    'help'        => $this->getImageHelperFormMapperWithThumbnail(),
                    'sonata_help' => $this->getImageHelperFormMapperWithThumbnail(),
                    'required'    => false,
                )
            )
//            ->end()
//            ->with('Geolocalització', $this->getFormMdSuccessBoxArray(12))
//            ->add(
//                'latLng',
//                GoogleMapType::class,
//                array(
//                    'label'    => 'Mapa',
//                    'required' => false
//                )
//            )
            ->end();
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'imageName',
                null,
                array(
                    'label' => 'Nom',
                )
            )
            ->add(
                'enabled',
                null,
                array(
                    'label'    => 'Actiu',
                    'editable' => true,
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
                'imageName',
                null,
                array(
                    'label'    => 'Nom',
                    'editable' => true,
                )
            )
            ->add(
                'enabled',
                null,
                array(
                    'label'    => 'Actiu',
                    'editable' => true,
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
}
