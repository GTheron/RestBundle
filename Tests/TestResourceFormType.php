<?php

/**
 * This file contains the TestResourceFormType class
*/

namespace GTheron\RestBundle\Tests;

use GTheron\RestBundle\Form\Type\ResourceFormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * TestResourceFormType
 *
 * @package GTheron\RestBundle\Tests;
 * @author Gabriel Théron
*/
class TestResourceFormType extends ResourceFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('name', 'text', 'array');
    }
}