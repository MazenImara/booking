<?php

namespace Drupal\booking\Form;

/**
 * @file
 * Contains \Drupal\quiz\Form\addServerForm.
 */

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\booking\Functions\functions;

class addServerForm extends FormBase {
    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'addServerForm';
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
        $form['name'] = [
            '#type'        => 'textfield',
            '#placeholder' => t('Name'),
            '#required'    => TRUE,
        ];
        $form['email'] = [
            '#type'        => 'email',
            '#placeholder' => t('Email'),
            '#required'    => TRUE,
        ];
        $form['phone'] = [
            '#type'        => 'textfield',
            '#placeholder' => t('Phone'),
            '#required'    => TRUE,
        ];
        $form['password'] = [
            '#type'        => 'textfield',
            '#placeholder' => t('Password'),
            '#required'    => TRUE,
        ];
        $form['status']['status'] = [
            '#type'  => 'checkbox',
            '#title' => $this->t('status'),
            //'#default_value' => 1,
            '#options'  => [0 => $this->t('False'), 1 => $this->t('True')],
            '#required' => TRUE,
        ];
        $form['serviceId'] = [
            '#type'        => 'hidden',
            '#value' => '222',
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
        functions::addServer($form_state->getValues());
    }

}
