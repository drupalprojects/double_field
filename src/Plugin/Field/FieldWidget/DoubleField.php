<?php

namespace Drupal\double_field\Plugin\Field\FieldWidget;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\double_field\Plugin\Field\FieldType\DoubleField as DoubleFieldItem;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'double_field' widget.
 *
 * @FieldWidget(
 *   id = "double_field",
 *   label = @Translation("Double field"),
 *   field_types = {"double_field"}
 * )
 */
class DoubleField extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {

    foreach (['first', 'second'] as $subfield) {
      $settings[$subfield] = [
        'type' => 'textfield',
        'prefix' => '',
        'suffix' => '',
        'size' => 10,
        'placeholder' => '',
        'label' => t('Ok'),
        'cols' => 10,
        'rows' => 5,
      ];
    }
    $settings['inline'] = FALSE;

    return $settings + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $settings = $this->getSettings();
    $field_settings = $this->getFieldSettings();

    $types = DoubleFieldItem::subfieldTypes();

    $field_name = $this->fieldDefinition->getName();

    $element['inline'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display as inline element'),
      '#default_value' => $settings['inline'],
    ];

    foreach (['first', 'second'] as $subfield) {

      $type = $field_settings['storage'][$subfield]['type'];

      $title = $subfield == 'first' ? $this->t('First subfield') : $this->t('Second subfield');
      $title .= ' - ' . $types[$type];

      $element[$subfield] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => FALSE,
      ];

      $element[$subfield]['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Widget'),
        '#default_value' => $settings[$subfield]['type'],
        '#required' => TRUE,
        '#options' => $this->getSubwidgets($type, $field_settings[$subfield]['list']),
      ];

      $type_selector = "select[name='fields[$field_name][settings_edit_form][settings][$subfield][type]'";
      $element[$subfield]['size'] = [
        '#type' => 'number',
        '#title' => $this->t('Size'),
        '#default_value' => $settings[$subfield]['size'],
        '#min' => 1,
        '#states' => [
          'visible' => [
            [$type_selector => ['value' => 'textfield']],
            [$type_selector => ['value' => 'tel']],
          ],
        ],
      ];

      $element[$subfield]['placeholder'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Placeholder attribute'),
        '#description' => $this->t('Pre-filled value that serves as a hint for the user regarding what to type.'),
        '#default_value' => $settings[$subfield]['placeholder'],
        '#states' => [
          'visible' => [
            [$type_selector => ['value' => 'textfield']],
            [$type_selector => ['value' => 'tel']],
            [$type_selector => ['value' => 'textarea']],
          ],
        ],
      ];

      $element[$subfield]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $settings[$subfield]['label'],
        '#required' => TRUE,
        '#states' => [
          'visible' => [$type_selector => ['value' => 'checkbox']],
        ],
      ];

      $element[$subfield]['cols'] = [
        '#type' => 'number',
        '#title' => $this->t('Columns'),
        '#default_value' => $settings[$subfield]['cols'],
        '#min' => 1,
        '#description' => $this->t('How many columns wide the textarea should be'),
        '#states' => [
          'visible' => [$type_selector => ['value' => 'textarea']],
        ],
      ];

      $element[$subfield]['rows'] = [
        '#type' => 'number',
        '#title' => $this->t('Rows'),
        '#default_value' => $settings[$subfield]['rows'],
        '#min' => 1,
        '#description' => $this->t('How many rows high the textarea should be.'),
        '#states' => [
          'visible' => [$type_selector => ['value' => 'textarea']],
        ],
      ];

      $element[$subfield]['prefix'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Prefix'),
        '#default_value' => $settings[$subfield]['prefix'],
      ];

      $element[$subfield]['suffix'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Suffix'),
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
    if ($settings['inline']) {
      $summary[] = $this->t('Display as inline element');
    }

    foreach (['first', 'second'] as $subfield) {
      $subfield_type = $subfield_types[$field_settings['storage'][$subfield]['type']];

      $summary[] = new FormattableMarkup(
        '<b>@subfield - @subfield_type</b>',
        [
          '@subfield' => ($subfield == 'first' ? $this->t('First subfield') : $this->t('Second subfield')),
          '@subfield_type' => strtolower($subfield_type),
        ]
      );
      $summary[] = $this->t('Widget: %type', ['%type' => $settings[$subfield]['type']]);
      switch ($settings[$subfield]['type']) {
        case 'textfield':
        case 'tel':
          $summary[] = $this->t('Size: %size', ['%size' => $settings[$subfield]['size']]);
          $summary[] = $this->t('Placeholder: %placeholder', ['%placeholder' => $settings[$subfield]['placeholder']]);
          break;

        case 'checkbox':
          $summary[] = $this->t('Label: %label', ['%label' => $settings[$subfield]['label']]);
          break;

        case 'select':
          break;

        case 'textarea':
          $summary[] = $this->t('Columns: %cols', ['%cols' => $settings[$subfield]['cols']]);
          $summary[] = $this->t('Rows: %rows', ['%rows' => $settings[$subfield]['rows']]);
          $summary[] = $this->t('Placeholder: %placeholder', ['%placeholder' => $settings[$subfield]['placeholder']]);
          break;
      }
      $summary[] = $this->t('Prefix: %prefix', ['%prefix' => $settings[$subfield]['prefix']]);
      $summary[] = $this->t('Suffix: %suffix', ['%suffix' => $settings[$subfield]['suffix']]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $field_settings = $this->getFieldSettings();
    $settings = $this->getSettings();

    $widget = [
      '#theme_wrappers' => ['container', 'form_element'],
      '#attributes' => ['class' => ['double-field-elements']],
    ];

    if ($settings['inline']) {
      $widget['#attributes']['class'][] = 'container-inline';
    }

    foreach (['first', 'second'] as $subfield) {
      $widget[$subfield] = [
        '#type' => $settings[$subfield]['type'],
        '#prefix' => $settings[$subfield]['prefix'],
        '#suffix' => $settings[$subfield]['suffix'],
        '#default_value' => isset($items[$delta]->{$subfield}) ? $items[$delta]->{$subfield} : NULL,
        '#subfield_settings' => $settings[$subfield],
      ];

      switch ($settings[$subfield]['type']) {

        case 'textfield':
        case 'tel':
          $widget[$subfield]['#maxlength'] = $field_settings['storage'][$subfield]['maxlength'];
          if ($settings[$subfield]['size']) {
            $widget[$subfield]['#size'] = $settings[$subfield]['size'];
          }
          if ($settings[$subfield]['placeholder']) {
            $widget[$subfield]['#placeholder'] = $settings[$subfield]['placeholder'];
          }
          break;

        case 'checkbox':
          $widget[$subfield]['#title'] = $settings[$subfield]['label'];
          break;

        case 'select':
          $label = $field_settings[$subfield]['required'] ? $this->t('- Select a value -') : $this->t('- None -');
          $widget[$subfield]['#options'] = ['' => $label];
          if ($field_settings[$subfield]['list']) {
            $widget[$subfield]['#options'] += $field_settings[$subfield]['allowed_values'];
          }
          break;

        case 'radios':
          $label = $field_settings[$subfield]['required'] ? $this->t('- Select a value -') : $this->t('- None -');
          $widget[$subfield]['#options'] = ['' => $label];
          if ($field_settings[$subfield]['list']) {
            $widget[$subfield]['#options'] += $field_settings[$subfield]['allowed_values'];
          }
          break;

        case 'textarea':
          if ($settings[$subfield]['cols']) {
            $widget[$subfield]['#cols'] = $settings[$subfield]['cols'];
          }
          if ($settings[$subfield]['rows']) {
            $widget[$subfield]['#rows'] = $settings[$subfield]['rows'];
          }
          if ($settings[$subfield]['placeholder']) {
            $widget[$subfield]['#placeholder'] = $settings[$subfield]['placeholder'];
          }
          break;

        case 'number':

          if (in_array($field_settings['storage'][$subfield]['type'], [
            'integer',
            'float',
            'numeric',
          ])) {

            if ($field_settings[$subfield]['min']) {
              $widget[$subfield]['#min'] = $field_settings[$subfield]['min'];
            }
            if ($field_settings[$subfield]['max']) {
              $widget[$subfield]['#max'] = $field_settings[$subfield]['max'];
            }
          }
          break;

      }

    }

    return $element + $widget;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $settings = $this->getSettings();

    foreach ($values as $delta => $value) {
      foreach (['first', 'second'] as $subfield) {
        if ($settings[$subfield]['type'] == 'select' && $value[$subfield] === '') {
          $values[$delta][$subfield] = NULL;
        }
      }
    }

    return $values;
  }

  /**
   * Returns available subwidgets.
   */
  protected function getSubwidgets($subfield_type, $list) {
    $subwidgets = [];

    if ($list) {
      $subwidgets['select'] = $this->t('Select list');
      $subwidgets['radios'] = $this->t('Radio buttons');
    }

    switch ($subfield_type) {

      case 'boolean':
        $subwidgets['checkbox'] = $this->t('Checkbox');
        break;

      case 'string':
      case 'email':
      case 'telephone':
        $subwidgets['textfield'] = $this->t('Textfield');
        $subwidgets['email'] = $this->t('Email');
        $subwidgets['number'] = $this->t('Number');
        $subwidgets['tel'] = $this->t('Telephone');
        break;

      case 'text':
        $subwidgets['textarea'] = $this->t('Text area');
        break;

      case 'integer':
      case 'float':
      case 'numeric':
        $subwidgets['number'] = $this->t('Number');
        $subwidgets['textfield'] = $this->t('Textfield');
        break;

    }

    return $subwidgets;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    return isset($violation->arrayPropertyPath[0]) ? $element[$violation->arrayPropertyPath[0]] : FALSE;
  }

}
