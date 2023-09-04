<?php

/**
 * @file
 * A form to collect an email address for RSVP details.
 */

namespace Drupal\custom_form\Form;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Vocabulary;

class CustomUserDetails extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'custom_user_form';
  }

  /**
   * {@inheritdoc}
   */


  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#maxlength' => 250,
      '#required' => TRUE,
    ];



    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */


  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // mail to admin for submited query.
    $to = \Drupal::config('system.site')->get('mail');
    $params = [
      'message' => $form_state->getValue('message'),
      'full_name' => $form_state->getValue('full_name'),
      'email' => $form_state->getValue('email'),
    ];

    \Drupal::service('plugin.manager.mail')->mail('custom_form', 'admin_notification', $to, $params);

    // thank you email to user.
    $user_email = $form_state->getValue('email');
    $params = [];
    \Drupal::service('plugin.manager.mail')->mail('custom_form', 'user_thank_you', $user_email, $params);

    // submit message to user.
    \Drupal::messenger()->addMessage($this->t('Thank you for your submission'));

  }
}