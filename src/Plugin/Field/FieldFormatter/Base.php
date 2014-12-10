<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\DoubleFieldBase.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 *
 */
abstract class Base extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {

    foreach (['first', 'second'] as $subfield) {
      $settings[$subfield] = [
        // Hidden option especially useful to display data with views module.
        'hidden' => 0,
        'format' => '_none',
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

    // Get a list of formats.
    $options['_none'] = t('- None -');

    /** @var \Drupal\filter\Entity\FilterFormat $format */
    foreach (filter_formats() as $format) {
//      $options[$format->id()] = $format->name;
    }

    // General settings.
    foreach (['first', 'second'] as $subfield) {
      $element[$subfield] = [
        '#title' => $subfield == 'first' ? t('First subfield') : t('Second subfield'),
        '#type' => 'details',
      ];
      $element[$subfield]['hidden'] = [
        '#type' => 'checkbox',
        '#title' => t('Hidden'),
        '#default_value' => $settings[$subfield]['hidden'],
      ];
//      $element[$subfield]['format'] = [
//        '#type' => 'select',
//        '#title' => t('Text format'),
//        '#options' => $options,
//        '#default_value' => $settings[$subfield]['format'],
//        '#description' => t('Warning: This setting may have security implications.'),
//        //'#element_validate' => ['double_field_format_validate'],
//      ];
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

    //$filter_formats = filter_formats();

    // Summary of general settings.
    foreach (['first', 'second'] as $subfield) {
      $summary[] = SafeMarkup::set('<br/><b>' . ($subfield == 'first' ? t('First subfield') : t('Second subfield')) . '</b>');
      $summary[] = t('Hidden: %value', ['%value' => $settings[$subfield]['hidden'] ? t('yes') : t('no')]);
//      $format = isset($filter_formats[$settings[$subfield]['format']]) ? $filter_formats[$settings[$subfield]['format']]->name : '';
//      $format_color = $settings[$subfield]['format'] == 'full_html' ? 'red' : 'auto';
//      if ($format) {
//        $summary[] = SafeMarkup::set('<span style="color: ' . $format_color . '">' . t('Format: %value', ['%value' => $format]) . '</span>');
//      }

	  $summary[] = t('Prefix: %prefix', ['%prefix' => $settings[$subfield]['prefix']]);
	  $summary[] = t('Suffix: %suffix', ['%suffix' => $settings[$subfield]['suffix']]);

    }

    return $summary;
  }

  protected function prepareItems(FieldItemListInterface &$items) {;

    $settings = $this->getSettings();

    $field_settings = $this->getFieldSettings();

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

          // TODO: Check markup here.
          $item->{$subfield} = \Drupal\Component\Utility\String::checkPlain($item->{$subfield});

//          $item[$subfield] = $settings[$subfield]['format'] == '_none' ?
//            check_plain($item[$subfield]) : check_markup($item[$subfield], $settings[$subfield]['format']);

          // Do not display prefix and suffix for empty subfields.
          if ($item->{$subfield} != '') {
            $prefix = $settings[$subfield]['prefix'];
            $suffix = $settings[$subfield]['suffix'];
            $item->{$subfield} = SafeMarkup::set($prefix . $item->{$subfield} . $suffix);
          }

        }

      }
      $items[$delta] = $item;
    }

  }

}
