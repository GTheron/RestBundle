<?php

/*
* This file is part of the GTheronRestBundle package.
*
* (c) Gabriel Théron <gabriel.theron90@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
 */

namespace GTheron\RestBundle\Event;

use GTheron\RestBundle\Model\ResourceInterface;
use Symfony\Component\Form\FormInterface;

/**
 * ValidationEvent
 *
 * @package GTheron\RestBundle\Event;
 * @author Gabriel Théron
*/
class ValidationEvent extends ResourceEvent
{
    protected $form;

    public function __construct(ResourceInterface $resource, FormInterface $form = null)
    {
        parent::__construct($resource);
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

}