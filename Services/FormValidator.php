<?php

namespace WH\LibBundle\Services;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;

/**
 * Class FormValidator
 *
 * @package WH\LibBundle\Services
 */
class FormValidator
{

    /**
     * @param Form  $form
     * @param array $constraints
     *
     * @return Form
     */
    public function validateFormConstraints(Form $form, $constraints = array())
    {
        $data = $form->getData();

        foreach ($constraints as $constraintField => $constraintFieldConstraints) {
            $fieldValue = $data[$constraintField];

            foreach ($constraintFieldConstraints as $constraintFieldConstraintType) {
                switch ($constraintFieldConstraintType) {
                    case 'NotNull':
                        if ($fieldValue == null) {
                            $form->get($constraintField)->addError(
                                new FormError('Ce champ est requis')
                            );
                        }
                        break;

                    case 'Email':
                        if ($fieldValue !== null && !preg_match('#(.*)@(.*)\.(.*)#', $fieldValue)) {
                            $form->get($constraintField)->addError(
                                new FormError('Email invalide')
                            );
                        }
                        break;
                }
            }
        }

        return $form;
    }

}
