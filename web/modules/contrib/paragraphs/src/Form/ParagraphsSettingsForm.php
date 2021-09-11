<?php

namespace Drupal\paragraphs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for Paragraphs settings.
 */
class ParagraphsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'paragraphs_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['paragraphs.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('paragraphs.settings');
    $form['show_unpublished'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show unpublished Paragraphs'),
      '#default_value' => $config->get('show_unpublished'),
      '#description' => $this->t('Allow users with "View unpublished paragraphs" permission to see unpublished Paragraphs. Disable this if unpublished paragraphs should be hidden for all users, including super administrators.')
    ];
	$form['allow_reference_changes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow translated Paragraphs'),
      '#default_value' => $config->get('allow_reference_changes'),
      '#description' => $this->t('Allow reference changes for paragraphs.')
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('paragraphs.settings');
    $config->set('show_unpublished', $form_state->getValue('show_unpublished'));
	$config->set('allow_reference_changes', $form_state->getValue('allow_reference_changes'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
