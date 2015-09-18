<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Make your Resources Form Types inherit from this one
 *
 * @package GTheron\RestBundle\Form\Type;
 * @author Gabriel Théron <gabriel.theron90@gmail.com>
*/
abstract class ResourceFormType extends AbstractType
{
    /**
     * By default, the only attribute we want to be writable is "deleted"
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deleted', 'boolean', array(
                'required' => false
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        // Empty string to map all fields at top level
        return '';
    }
}