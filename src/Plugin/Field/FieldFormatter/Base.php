<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\DoubleFieldBase.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\double_field\Plugin\Field\FieldType\DoubleField as DoubleFieldItem;

/**
 * Base class for Double field formatters.
 */
abstract class Base extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [];
    foreach (['first', 'second'] as $subfield) {
      $settings[$subfield] = [
        // Hidden option useful to display data with views module.
        'hidden' => FALSE,
        'prefix' => '',
        'suffix' => '',
      ];
    }

    return $settings + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $settings = $this->getSettings();
    $field_settings = $this->getFieldSettings();
    $types = DoubleFieldItem::subfieldTypes();

    // General settings.
    foreach (['first', 'second'] as $subfield) {

      $type = $field_settings['storage'][$subfield]['type'];

      $title = $subfield == 'first' ? t('First subfield') : t('Second subfield');
      $title .= ' - ' . $types[$type];

      $element[$subfield] = [
        '#title' => $title,
        '#type' => 'details',
      ];
      $element[$subfield]['hidden'] = [
        '#type' => 'checkbox',
        '#title' => t('Hidden'),
        '#default_value' => $settings[$subfield]['hidden'],
      ];
      $element[$subfield]['prefix'] = [
        '#type' => 'textfield',
        '#title' => t('Prefix'),
        '#size' => 30,
        '#default_value' => $settings[$subfield]['prefix'],
      ];
      $element[$subfield]['suffix'] = [
        '#type' => 'textfield',
        '#title' => t('Suffix'),
        '#size' => 30,
        '#default_value' => $settings[$subfield]['suffix'],
      ];
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $settings = $this->getSettings();
    $field_settings = $this->getFieldSettings();

    $subfield_types = DoubleFieldItem::subfieldTypes();

    foreach (['first', 'second'] as $subfield) {
      $subfield_type = $subfield_types[$field_settings['storage'][$subfield]['type']];

      $summary[] = new FormattableMarkup(
        '<b>@subfield - @subfield_type</b>',
        [
          '@subfield' => ($subfield == 'first' ? t('First subfield') : t('Second subfield')),
          '@subfield_type' => strtolower($subfield_type),
        ]
      );

      $summary[] = t('Hidden: %value', ['%value' => $settings[$subfield]['hidden'] ? t('yes') : t('no')]);

      $summary[] = t('Prefix: %prefix', ['%prefix' => $settings[$subfield]['prefix']]);
      $summary[] = t('Suffix: %suffix', ['%suffix' => $settings[$subfield]['suffix']]);
    }

    return $summary;
  }

  /**
   * Prepare field items.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   List of field items.
   */
  protected function prepareItems(FieldItemListInterface &$items) {

    $field_settings = $this->getFieldSettings();
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      foreach (['first', 'second'] as $subfield) {

        if ($settings[$subfield]['hidden']) {
          $item->{$subfield} = FALSE;
        }
        else {

          // Show value pair of allowed values on instead of their key value.
          if ($field_settings[$subfield]['list']) {
            if (isset($field_settings[$subfield]['allowed_values'][$item->{$subfield}])) {
              $item->{$subfield} = $field_settings[$subfield]['allowed_values'][$item->{$subfield}];
            }
            else {
              $item->{$subfield} = FALSE;
            }
          }

          if ($field_settings['storage'][$subfield]['type'] == 'boolean') {
            $item->{$subfield} = $field_settings[$subfield][$item->{$subfield} ? 'on_label' : 'off_label'];
          }

        }
      }
      $items[$delta] = $item;
    }

  }

}
