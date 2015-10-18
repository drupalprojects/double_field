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
    return [
      'style' => 'inline',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $settings = $this->getSettings();

    $element['style'] = [
      '#type' => 'select',
      '#title' => t('Style'),
      '#options' => [
        'inline' => t('Inline'),
        'block' => t('Block'),
      ],
      '#default_value' => isset($settings['style']) ? $settings['style'] : 'inline',
    ];

    return $element + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = [];
    if ($this->getSetting('list_type') != 'dl') {
      $summary[] = t('Display style: %value', ['%value' => $this->getSetting('style')]);
    }

    return array_merge($summary, parent::settingsSummary());
  }

}
