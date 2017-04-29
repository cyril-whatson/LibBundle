<?php

namespace WH\LibBundle\Services;

use Symfony\Component\Form\FormInterface;

/**
 * Class FormErrorCollector
 *
 * @package WH\LibBundle\Services
 */
class FormErrorCollector
{
    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function getErrors(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if ($child instanceof FormInterface) {
                if ($childErrors = $this->getErrors($child)) {
                    $errors[$child->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
