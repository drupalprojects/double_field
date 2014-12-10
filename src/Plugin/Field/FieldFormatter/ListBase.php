<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\ListBase.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
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
        'link' => t('Link'),
        'dialog' => t('Dialog'),
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


  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
	$this->prepareItems($items);
	$element = [];

	if ($this->getSetting('style') == 'dialog') {
	  $element['#attached']['library'] = ['core/jquery.ui.tabs'];
	  $element['#attached']['library'] = ['core/jquery.ui.effects.explode'];
	  $element['#attached']['library'] = ['double_field/dialog'];
	  //$element['#attached']['js'][] = drupal_get_path('module', 'double_field') . '/js/dialog.js';
	}

	return $element;
  }


}
