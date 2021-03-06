<?php

namespace Drupal\booking\Form;

/**
 * @file
 * Contains \Drupal\booking\Form\addWorkDaysForm.
 */

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\booking\Functions\functions;

class addWorkDaysForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'addWorkDaysForm';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state, $serviceId = NULL) {
        $form['quantity'] = [
            '#type'        => 'textfield',
            //'#value' => 1,
            '#placeholder' => t('Quantity'),
            '#description' => t('Quantity'),
            '#required'    => TRUE,
        ];
        $form['serviceId'] = [
            '#type'        => 'hidden',
            '#value' => $serviceId,
            '#placeholder' => t('questionnaireId'),
            '#required'    => TRUE,
        ];
        $form['actions']['#type']  = 'actions';
        $form['actions']['submit'] = [
            '#type'        => 'submit',
            '#value'       => $this->t('Create'),
            '#button_type' => 'primary',
        ];
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array&$form, FormStateInterface $form_state) {

    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array&$form, FormStateInterface $form_state) {
        functions::addWorkDays($form_state->getValues());
    }

}
