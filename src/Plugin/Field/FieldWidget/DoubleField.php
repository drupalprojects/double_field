<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldWidget\DoubleField.
 */

namespace Drupal\double_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Drupal\Component\Utility\SafeMarkup;

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
        'textfield' => [
          'size' => 10,
          'placeholder' => '',
        ],
        'checkbox' => [
          'label' => t('Ok'),
        ],
        'select' => [
          'allowed_values' => [],
        ],
        'textarea' => [
          'cols' => 10,
          'rows' => 5,
          'resizable' => TRUE,
          'placeholder' => '',
        ],
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

	$types = \Drupal\double_field\Plugin\Field\FieldType\DoubleField::subfieldTypes();

    $field_name = $this->fieldDefinition->getName();

    $element['inline'] = [
      '#type' => 'checkbox',
      '#title' => t('Display as inline element'),
      '#default_value' => $settings['inline'],
    ];

    foreach (['first', 'second'] as $subfield) {

	  $type = $field_settings['storage'][$subfield]['type'];

	  $title =  $subfield == 'first' ? t('First subfield') : t('Second subfield');
	  $title .= ' - ' .  $types[$type];

      $element[$subfield] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => FALSE,
      ];

      $element[$subfield]['type'] = [
        '#type' => 'select',
        '#title' => t('Widget'),
        '#default_value' => $settings[$subfield]['type'],
        '#required' => TRUE,
        '#options' => $this->getSubwidgets($field_settings[$subfield]),
      ];

      $type_selector = "select[name='fields[$field_name][settings_edit_form][settings][$subfield][type]'";
      $element[$subfield]['textfield']['size'] = [
        '#type' => 'number',
        '#title' => t('Size'),
        '#default_value' => $settings[$subfield]['textfield']['size'],
        '#min' => 1,
        '#states' => [
          'visible' => [$type_selector => ['value' => 'textfield']],
        ],
      ];

      $element[$subfield]['textfield']['placeholder'] = [
        '#type' => 'textfield',
        '#title' => t('Placeholder attribute'),
        '#description' => t('Pre-filled value that serves as a hint for the user regarding what to type.'),
        '#default_value' => $settings[$subfield]['textfield']['placeholder'],
        '#states' => [
          'visible' => [$type_selector => ['value' => 'textfield']],
        ],
      ];

      $element[$subfield]['checkbox']['label'] = [
        '#type' => 'textfield',
        '#title' => t('Label'),
        '#default_value' => $settings[$subfield]['checkbox']['label'],
        '#required' => TRUE,
        '#states' => [
          'visible' => [$type_selector => ['value' => 'checkbox']],
        ],
      ];

      $element[$subfield]['textarea']['cols'] = [
        '#type' => 'number',
        '#title' => t('Columns'),
        '#default_value' => $settings[$subfield]['textarea']['cols'],
        '#min' => 1,
        '#description' => t('How many columns wide the textarea should be'),
        '#states' => [
          'visible' => [$type_selector => ['value' => 'textarea']],
        ],
      ];
      $element[$subfield]['textarea']['rows'] = [
        '#type' => 'number',
        '#title' => t('Rows'),
        '#default_value' => $settings[$subfield]['textarea']['rows'],
        '#min' => 1,
        '#description' => t('How many rows high the textarea should be.'),
        '#states' => [
          'visible' => [$type_selector => ['value' => 'textarea']],
        ],
      ];
      $element[$subfield]['textarea']['placeholder'] = [
        '#type' => 'textfield',
        '#title' => t('Placeholder attribute'),
        '#description' => t('Pre-filled value that serves as a hint for the user regarding what to type.'),
        '#default_value' => $settings[$subfield]['textarea']['placeholder'],
        '#states' => [
          'visible' => [$type_selector => ['value' => 'textarea']],
        ],
      ];

      $element[$subfield]['prefix'] = [
        '#type' => 'textfield',
        '#title' => t('Prefix'),
        '#default_value' => $settings[$subfield]['prefix'],
      ];
      $element[$subfield]['suffix'] = [
        '#type' => 'textfield',
        '#title' => t('Suffix'),
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
    $summary = [];

	if ($settings['inline']) {
	  $summary[] = t('Display as inline element');
	}

	foreach (['first', 'second'] as $subfield) {
	  $summary[] = SafeMarkup::set('<br/><b>' . ($subfield == 'first' ? t('First subfield') : t('Second subfield')) . '</b>');
	  $summary[] = t('Widget: %type', ['%type' => $settings[$subfield]['type']]);
	  switch($settings[$subfield]['type']) {
		case 'textfield':
		  $summary[] = t('Size: %size', ['%size' => $settings[$subfield]['textfield']['size']]);
		  $summary[] = t('Placeholder: %placeholder', ['%placeholder' => $settings[$subfield]['textfield']['placeholder']]);
		  break;

		case 'checkbox':
		  $summary[] = t('Label: %label', ['%label' => $settings[$subfield]['checkbox']['label']]);
		  break;

		case 'select':
		  break;

		case 'textarea':
		  $summary[] = t('Columns: %cols', ['%cols' => $settings[$subfield]['textarea']['cols']]);
		  $summary[] = t('Rows: %rows', ['%rows' => $settings[$subfield]['textarea']['rows']]);
		  $summary[] = t('Placeholder: %placeholder', ['%placeholder' => $settings[$subfield]['textarea']['placeholder']]);
		  break;
	  }
	  $summary[] = t('Prefix: %prefix', ['%prefix' => $settings[$subfield]['prefix']]);
	  $summary[] = t('Suffix: %suffix', ['%suffix' => $settings[$subfield]['suffix']]);
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
          if ($settings[$subfield]['textfield']['size']) {
            $widget[$subfield]['#size'] = $settings[$subfield]['textfield']['size'];
          }
          if ($settings[$subfield]['textfield']['placeholder']) {
            $widget[$subfield]['#placeholder'] = $settings[$subfield]['textfield']['placeholder'];
          }
          break;

        case 'checkbox':
          $widget[$subfield]['#title'] = $settings[$subfield]['checkbox']['label'];
          break;

        case 'select':
          $label = $field_settings[$subfield]['required'] ? t('- Select a value -') : t('- None -');
          $widget[$subfield]['#options'] = ['' => $label];
		  if ($field_settings[$subfield]['list']) {
			$widget[$subfield]['#options'] += $field_settings[$subfield]['allowed_values'];
		  }
          break;

        case 'textarea':
          if ($settings[$subfield]['textarea']['cols']) {
            $widget[$subfield]['#cols'] = $settings[$subfield]['textarea']['cols'];
          }
          if ($settings[$subfield]['textarea']['rows']) {
            $widget[$subfield]['#rows'] = $settings[$subfield]['textarea']['rows'];
          }
          if ($settings[$subfield]['textarea']['placeholder']) {
            $widget[$subfield]['#placeholder'] = $settings[$subfield]['textarea']['placeholder'];
          }
          break;

		case 'number':

		  if (in_array($field_settings['storage'][$subfield]['type'], ['int', 'float', 'numeric'])) {
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
   *
   */
  protected function getSubwidgets($subfield_settings) {
    $available_subwidgets = [
      'textfield' => t('Textfield'),
      'select' => t('Select list'),
      'checkbox' => t('Checkbox'),
      'textarea' => t('Text area'),
      'email' => t('Email'),
      'number' => t('Number'),
    ];

    return $available_subwidgets;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {

    if (isset($violation->arrayPropertyPath[0]))  {
      return $element[$violation->arrayPropertyPath[0]];
    }
    else {
      return FALSE;
    }

  }

}
