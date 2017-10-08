<?php

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for list formatters.
 */
abstract class ListBase extends Base {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['inline' => TRUE] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $settings = $this->getSettings();

    $element['inline'] = [
      '#type' => 'checkbox',
      '#title' => t('Display as inline element'),
      '#default_value' => $settings['inline'],
      '#weight' => -10,
    ];

    $storage_settings = $this->getFieldSetting('storage');
    foreach (['first', 'second'] as $subfield) {
      if ($storage_settings[$subfield]['type'] == 'telephone') {
        $element[$subfield]['link'] = [
          '#type' => 'checkbox',
          '#title' => t('Display as link'),
          '#default_value' => $settings[$subfield]['link'],
          '#weight' => -10,
        ];
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('inline')) {
      $summary[] = t('Display as inline element');
    }
    return array_merge($summary, parent::settingsSummary());
  }

}
