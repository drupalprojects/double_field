<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\Details.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementations for 'details' formatter.
 *
 * @FieldFormatter(
 *   id = "double_field_details",
 *   label = @Translation("Details"),
 *   field_types = {"double_field"}
 * )
 */
class Details extends Base {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'open' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $settings = $this->getSettings();

    $element['open'] = array(
      '#type' => 'checkbox',
      '#title' => t('Open'),
      '#default_value' => $settings['open'],
    );

    $element += parent::settingsForm($form, $form_state);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $open = $this->getSetting('open');
    $summary[] = t('Open: %open', ['%open' => $open ? t('yes') : t('no')]);
    return array_merge($summary, parent::settingsSummary());
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $element = [];
    $this->prepareItems($items);
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      $element[$delta] = array(
        '#title' => $settings['first']['prefix'] . $item->first . $settings['first']['suffix'],
        '#value' => $settings['second']['prefix'] . $item->second . $settings['second']['suffix'],
        '#type' => 'details',
        '#open' => $settings['open'],
        '#attributes' => ['class' => ['double-field-details']],
      );

    }
    return $element;

  }

}
