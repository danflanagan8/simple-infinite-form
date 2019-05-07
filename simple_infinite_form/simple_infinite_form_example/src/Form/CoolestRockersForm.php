<?php

namespace Drupal\simple_infinite_form_example\Form;

use Drupal\simple_infinite_form\Form\SimpleInfiniteFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * An example of a form that extends the SimpleInfiniteFormBase class.
 * It is intentionally more complicated than the Lucky Numbers form.
 *
 * This example declares a build function that adds select input to
 * the top of the form.
 *
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
