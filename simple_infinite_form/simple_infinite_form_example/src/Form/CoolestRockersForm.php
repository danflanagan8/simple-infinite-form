<?php

namespace Drupal\simple_infinite_form_example\Form;

use Drupal\simple_infinite_form\Form\SimpleInfiniteFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * An example of a form that extends the SimpleInfiniteFormBase class.
 * You definitely want your extension to declare getFormId and getEditableConfigNames.
 * If the default versions of getConfigKeyName and getInputType don't work
 * for your purposes, declare those. In this case, the defaul textfield is perfect.
 * This example declares a build function that adds markup and a select list to
 * the top of the form. (You don't necessarily need to do that. The parent
 * build function might be all you need.) Since the select input was added
 * to the top of the form, a new submitForm function needed to be declared.
 */

class CoolestRockersForm extends SimpleInfiniteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'coolest_rockers_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simple_infinite_form_example.coolest_rockers',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function getConfigKeyName() {
    return 'non-beatles';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config($this->getEditableConfigNames()[0]);

    $form['custom_intro'] = [
      '#markup' => '<div><p>Configure your favorite rockers. Select your favorite Beatle in the first field. Then enter as many other cool rockers as you want. This is an example of how to extend the SimpleInfiniteFormBase class.</p></div>'
    ];

    $form['beatle'] = [
      '#type' => 'select',
      '#options' => [
        'John' => 'John',
        'Paul' => 'Paul',
        'George' => 'George',
        'Ringo' => 'Ringo',
      ],
      '#title' => 'Coolest Beatle',
      '#default_value' => $config->get('beatle') ? $config->get('beatle') : 'Ringo',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable($this->getEditableConfigNames()[0]);
    $config->set('beatle', $form_state->getValue(['beatle']));
    $config->save();
    parent::submitForm($form, $form_state);
  }
  
}
