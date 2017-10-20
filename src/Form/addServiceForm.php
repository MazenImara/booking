<?php

namespace Drupal\booking\Form;

/**
 * @file
 * Contains \Drupal\quiz\Form\addQuizForm.
 */

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\booking\Functions\functions;

class addServiceForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'addServiceForm';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

        $form['title'] = [
            '#type'        => 'textfield',
            '#placeholder' => t('Title'),
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
        functions::addService($form_state->getValues()['title']);
    }

}
