<?php

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\double_field\Plugin\Field\FieldType\DoubleField as DoubleFieldItem;

/**
 * Base class for Double field formatters.
 */
abstract class Base extends FormatterBase {

  /**
   * Subfield types that can be rendered as a link.
   *
   * @var array
   */
  protected static $linkTypes = ['email', 'telephone'];

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [];
    foreach (['first', 'second'] as $subfield) {
      $settings[$subfield] = [
        // Hidden option are useful to display data with Views module.
        'hidden' => FALSE,
        'prefix' => '',
        'suffix' => '',
        'link' => FALSE,
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
    $element = [];

    // General settings.
    foreach (['first', 'second'] as $subfield) {
      $type = $field_settings['storage'][$subfield]['type'];

      $title = $subfield == 'first' ? $this->t('First subfield') : $this->t('Second subfield');
      $title .= ' - ' . $types[$type];

      $element[$subfield] = [
        '#title' => $title,
        '#type' => 'details',
      ];

      if (in_array($type, static::$linkTypes)) {
        $element[$subfield]['link'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Display as link'),
          '#default_value' => $settings[$subfield]['link'],
          '#weight' => -10,
        ];
      }
      $element[$subfield]['hidden'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Hidden'),
        '#default_value' => $settings[$subfield]['hidden'],
      ];
      $element[$subfield]['prefix'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Prefix'),
        '#size' => 30,
        '#default_value' => $settings[$subfield]['prefix'],
      ];
      $element[$subfield]['suffix'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Suffix'),
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

    $summary = [];
    foreach (['first', 'second'] as $subfield) {
      $subfield_type = $field_settings['storage'][$subfield]['type'];
      $summary[] = new FormattableMarkup(
        '<b>@subfield - @subfield_type</b>',
        [
          '@subfield' => ($subfield == 'first' ? $this->t('First subfield') : $this->t('Second subfield')),
          '@subfield_type' => strtolower($subfield_types[$subfield_type]),
        ]
      );

      if (in_array($subfield_type, static::$linkTypes)) {
        $summary[] = $this->t('Link: %value', ['%value' => $settings[$subfield]['link'] ? $this->t('yes') : $this->t('no')]);
      }
      $summary[] = $this->t('Hidden: %value', ['%value' => $settings[$subfield]['hidden'] ? $this->t('yes') : $this->t('no')]);
      $summary[] = $this->t('Prefix: %prefix', ['%prefix' => $settings[$subfield]['prefix']]);
      $summary[] = $this->t('Suffix: %suffix', ['%suffix' => $settings[$subfield]['suffix']]);
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

          // Replace the value with its label if possible.
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

          if (!empty($settings[$subfield]['link'])) {
            if ($field_settings['storage'][$subfield]['type'] == 'email') {
              $item->{$subfield} = [
                '#type' => 'link',
                '#title' => $item->{$subfield},
                '#url' => Url::fromUri('mailto:' . $item->{$subfield}),
              ];
            }
            elseif ($field_settings['storage'][$subfield]['type'] == 'telephone') {
              $item->{$subfield} = [
                '#type' => 'link',
                '#title' => $item->{$subfield},
                '#url' => Url::fromUri('tel:' . rawurlencode(preg_replace('/\s+/', '', $item->{$subfield}))),
                '#options' => ['external' => TRUE],
              ];
            }
          }

        }

      }
      $items[$delta] = $item;
    }
  }

}
