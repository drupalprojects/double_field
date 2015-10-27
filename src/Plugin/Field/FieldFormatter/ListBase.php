<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\ListBase.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
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

    $settings = $this->getSettings();

    $element['inline'] = [
      '#type' => 'checkbox',
      '#title' => t('Display as inline element'),
      '#default_value' => $settings['inline'],
    ];

    return $element + parent::settingsForm($form, $form_state);
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
