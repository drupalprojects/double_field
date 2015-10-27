<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldType\DoubleField.
 */

namespace Drupal\double_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Render\Element\Email;

/**
 * Plugin implementation of the 'double_field' field type.
 *
 * @FieldType(
 *   id = "double_field",
 *   label = @Translation("Double field"),
 *   description = @Translation("Double field."),
 *   default_widget = "double_field",
 *   default_formatter = "double_field_unformatted_list"
 * )
 */
class DoubleField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {

    foreach (['first', 'second'] as $subfield) {
      $settings['storage'][$subfield] = [
        'type' => 'string',
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
      ];
    }

    return $settings + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {

    $settings = $this->getSettings();

    foreach (['first', 'second'] as $subfield) {
      $element['storage'][$subfield] = [
        '#type' => 'details',
        '#title' => $subfield == 'first' ? t('First subfield') : t('Second subfield'),
        '#open' => TRUE,
      ];

      $element['storage'][$subfield]['type'] = [
        '#type' => 'select',
        '#title' => t('Field type'),
        '#default_value' => $settings['storage'][$subfield]['type'],
        '#disabled' => $has_data,
        '#required' => TRUE,
        '#options' => $this->subfieldTypes(),
      ];

      $element['storage'][$subfield]['maxlength'] = [
        '#type' => 'number',
        '#title' => t('Maximum length'),
        '#description' => t('The maximum length of the subfield in characters.'),
        '#default_value' => $settings['storage'][$subfield]['maxlength'],
        '#disabled' => $has_data,
        '#required' => TRUE,
        '#min' => 1,
        '#states' => [
          'visible' => [":input[name='settings[storage][$subfield][type]']" => ['value' => 'string']],
        ],
      ];

      $element['storage'][$subfield]['precision'] = [
        '#type' => 'number',
        '#title' => t('Precision'),
        '#description' => t('The total number of digits to store in the database, including those to the right of the decimal.'),
        '#default_value' => $settings['storage'][$subfield]['precision'],
        '#disabled' => $has_data,
        '#required' => TRUE,
        '#min' => 10,
        '#max' => 32,
        '#states' => [
          'visible' => [":input[name='settings[storage][$subfield][type]']" => ['value' => 'numeric']],
        ],
      ];

      $element['storage'][$subfield]['scale'] = [
        '#type' => 'number',
        '#title' => t('Scale'),
        '#description' => t('The number of digits to the right of the decimal.'),
        '#default_value' => $settings['storage'][$subfield]['scale'],
        '#disabled' => $has_data,
        '#required' => TRUE,
        '#min' => 0,
        '#max' => 10,
        '#states' => [
          'visible' => [":input[name='settings[storage][$subfield][type]']" => ['value' => 'numeric']],
        ],
      ];

    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {

    foreach (['first', 'second'] as $subfield) {
      $settings[$subfield] = [
        'min' => '',
        'max' => '',
        'list' => FALSE,
        'allowed_values' => [],
        'required' => TRUE,
        'on_label' => t('On'),
        'off_label' => t('Off'),
      ];
    }

    return $settings + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {

    $settings = $this->getSettings();

    $types = self::subfieldTypes();
    foreach (['first', 'second'] as $subfield) {

      $type = $settings['storage'][$subfield]['type'];

      $title = $subfield == 'first' ? t('First subfield') : t('Second subfield');
      $title .= ' - ' . $types[$type];

      $element[$subfield] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => FALSE,
        '#tree' => TRUE,
      ];

      $element[$subfield]['required'] = [
        '#type' => 'checkbox',
        '#title' => t('Required'),
        '#default_value' => $settings[$subfield]['required'],
      ];

      if (in_array($type, ['string', 'integer', 'float', 'numeric', 'email'])) {
        $element[$subfield]['list'] = [
          '#type' => 'checkbox',
          '#title' => t('Limit allowed values'),
          '#default_value' => $settings[$subfield]['list'],
        ];

        $description[] = t('The possible values this field can contain. Enter one value per line, in the format key|label.');
        $description[] = t('The label will be used in displayed values and edit forms.');
        $description[] = t('The label is optional: if a line contains a single item, it will be used as key and label.');

        $element[$subfield]['allowed_values'] = [
          '#type' => 'textarea',
          '#title' => t('Allowed values list'),
          '#description' => implode('<br/>', $description),
          '#default_value' => $this->allowedValuesString($settings[$subfield]['allowed_values']),
          '#rows' => 10,
          '#element_validate' => [[get_class($this), 'validateAllowedValues']],
          // @see: DoubleField::validateAllowedValues()
          '#storage_type' => $type,
          '#storage_maxlength' => $settings['storage'][$subfield]['maxlength'],
          '#field_name' => $this->getFieldDefinition()->getName(),
          '#entity_type' => $this->getEntity()->getEntityTypeId(),
          '#allowed_values' => $settings[$subfield]['allowed_values'],
          '#states' => [
            'invisible' => [":input[name='settings[$subfield][list]']" => ['checked' => FALSE]],
          ],
        ];
      }
      else {
        $element[$subfield]['list'] = [
          '#type' => 'value',
          '#default_value' => FALSE,
        ];
        $element[$subfield]['allowed_values'] = [
          '#type' => 'value',
          '#default_value' => [],
        ];
      }

      if (in_array($type, ['integer', 'float', 'numeric'])) {
        $element[$subfield]['min'] = [
          '#type' => 'number',
          '#title' => t('Minimum'),
          '#description' => t('The minimum value that should be allowed in this field. Leave blank for no minimum.'),
          '#default_value' => isset($settings[$subfield]['min']) ? $settings[$subfield]['min'] : NULL,
          '#states' => [
            'visible' => [":input[name='settings[$subfield][list]']" => ['checked' => FALSE]],
          ],
        ];
        $element[$subfield]['max'] = [
          '#type' => 'number',
          '#title' => t('Maximum'),
          '#description' => t('The maximum value that should be allowed in this field. Leave blank for no maximum.'),
          '#default_value' => isset($settings[$subfield]['max']) ? $settings[$subfield]['max'] : NULL,
          '#states' => [
            'visible' => [":input[name='settings[$subfield][list]']" => ['checked' => FALSE]],
          ],
        ];
      }
      else {
        $element[$subfield]['min'] = $element[$subfield]['max'] = [
          '#type' => 'value',
          '#default_value' => ''
        ];
      }

      if ($type == 'boolean') {
        $element[$subfield]['on_label'] = [
          '#type' => 'textfield',
          '#title' => t('"On" label'),
          '#default_value' => $settings[$subfield]['on_label'],
        ];
        $element[$subfield]['off_label'] = [
          '#type' => 'textfield',
          '#title' => t('"Off" label'),
          '#default_value' => $settings[$subfield]['off_label'],
        ];
      }
      else {
        $element[$subfield]['on_label'] = [
          '#type' => 'value',
          '#default_value' => $settings[$subfield]['on_label'],
        ];
        $element[$subfield]['off_label'] = [
          '#type' => 'value',
          '#default_value' => $settings[$subfield]['off_label'],
        ];
      }

    }

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @TODO: Find a way to disable constraints for default field values.
   */
  public function getConstraints() {

    $constraint_manager = \Drupal::typedDataManager()
      ->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $settings = $this->getSettings();

    $subconstrains = [];
    foreach (['first', 'second'] as $subfield) {

      $subfield_type = $settings['storage'][$subfield]['type'];

      if (!in_array($subfield_type, ['boolean', 'text']) && $settings[$subfield]['list'] && $settings[$subfield]['allowed_values']) {
        $allowed_values = array_keys($settings[$subfield]['allowed_values']);
        $subconstrains[$subfield]['AllowedValues'] = $allowed_values;
      }

      if ($subfield_type == 'string') {
        $subconstrains[$subfield]['Length']['max'] = $settings['storage'][$subfield]['maxlength'];
      }

      // Allowed values take precedence over the range constraints.
      if (!$settings[$subfield]['list'] && in_array($subfield_type, ['integer', 'float', 'numeric'])) {
        if (is_numeric($settings[$subfield]['min'])) {
          $subconstrains[$subfield]['Range']['min'] = $settings[$subfield]['min'];
        }
        if (is_numeric($settings[$subfield]['max'])) {
          $subconstrains[$subfield]['Range']['max'] = $settings[$subfield]['max'];
        }
      }

      if ($subfield_type == 'email') {
        $subconstrains[$subfield]['Length']['max'] = Email::EMAIL_MAX_LENGTH;
      }

      // This is applicable to all types.
      if ($settings[$subfield]['required']) {
        $subconstrains[$subfield]['NotBlank'] = [];
      }
    }

    $constraints[] = $constraint_manager->create('ComplexData', $subconstrains);

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $settings = $field_definition->getSettings();

    $columns = [];
    foreach (['first', 'second'] as $subfield) {

      $type = $settings['storage'][$subfield]['type'];

      $columns[$subfield] = [
        'not null' => FALSE,
        'description' => ucfirst($subfield) . ' subfield value.',
      ];

      switch ($type) {
        case 'string':
          $columns[$subfield]['type'] = 'varchar';
          $columns[$subfield]['length'] = $settings['storage'][$subfield]['maxlength'];
          break;

        case 'text':
          $columns[$subfield]['type'] = 'text';
          $columns[$subfield]['size'] = 'big';
          break;

        case 'integer':
          $columns[$subfield]['type'] = 'int';
          $columns[$subfield]['size'] = 'normal';
          break;

        case 'float':
          $columns[$subfield]['type'] = 'float';
          $columns[$subfield]['size'] = 'normal';
          break;

        case 'boolean':
          $columns[$subfield]['type'] = 'int';
          $columns[$subfield]['size'] = 'tiny';
          break;

        case 'numeric':
          $columns[$subfield]['type'] = 'numeric';
          $columns[$subfield]['precision'] = $settings['storage'][$subfield]['precision'];
          $columns[$subfield]['scale'] = $settings['storage'][$subfield]['scale'];
          break;

        case 'email':
          $columns[$subfield]['type'] = 'varchar';
          $columns[$subfield]['length'] = Email::EMAIL_MAX_LENGTH;
          break;
      }
    }

    return ['columns' => $columns];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $subfield_types = self::subfieldTypes();

    $settings = $field_definition->getSettings();
    foreach (['first', 'second'] as $subfield) {

      $subfield_type = $settings['storage'][$subfield]['type'];
      // Typed data are slightly different from schema the definition.
      if ($subfield_type == 'text') {
        $subfield_type = 'string';
      }
      elseif ($subfield_type == 'numeric') {
        $subfield_type = 'float';
      }

      $properties[$subfield] = DataDefinition::create($subfield_type)
        ->setLabel($subfield_types[$subfield_type]);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    foreach (['first', 'second'] as $subfield) {
      if ($this->{$subfield} !== NULL && $this->{$subfield} !== '') {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Element validate callback for subfield allowed values.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param FormStateInterface $form_state
   *   The current state of the form for the form this element belongs to.
   *
   * @see \Drupal\Core\Render\Element\FormElement::processPattern()
   */
  public static function validateAllowedValues(array $element, FormStateInterface $form_state) {
    $values = static::extractAllowedValues($element['#value']);

    // Check if keys are valid for the field type.
    foreach ($values as $key => $value) {
      switch ($element['#storage_type']) {

        case 'string':
          // @see \Drupal\options\Plugin\Field\FieldType\ListStringItem::validateAllowedValue()
          if (Unicode::strlen($key) > $element['#storage_maxlength']) {
            $error_message = t(
              'Allowed values list: each key must be a string at most @maxlength characters long.',
              ['@maxlength' => $element['#storage_maxlength']]
            );
            $form_state->setError($element, $error_message);
          }
          break;

        case 'integer':
          // @see \Drupal\options\Plugin\Field\FieldType\ListIntegerItem::validateAllowedValue()
          if (!preg_match('/^-?\d+$/', $key)) {
            $form_state->setError($element, ('Allowed values list: keys must be integers.'));
          }
          break;

        case 'float':
        case 'numeric':
          // @see \Drupal\options\Plugin\Field\FieldType\ListFloatItem::validateAllowedValue()
          if (!is_numeric($key)) {
            $form_state->setError($element, ('Allowed values list: each key must be a valid integer or decimal.'));
          }
          break;

      }
    }

    $form_state->setValueForElement($element, $values);
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array
   *   The array of extracted key/value pairs.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListTextItem::extractAllowedValues()
   */
  protected static function extractAllowedValues($string) {

    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    foreach ($list as $text) {
      // Check for an explicit key.
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
      }
      else {
        $key = $value = $text;
      }
      $values[$key] = $value;
    }

    return $values;
  }

  /**
   * Generates a string representation of an array of 'allowed values'.
   *
   * This string format is suitable for edition in a textarea.
   *
   * @param array $values
   *   An array of values, where array keys are values and array values are
   *   labels.
   *
   * @return string
   *   The string representation of the $values array:
   *    - Values are separated by a carriage return.
   *    - Each value is in the format "value|label" or "value".
   */
  protected function allowedValuesString(array $values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

  /**
   * Returns available subfield storage types.
   */
  public static function subfieldTypes() {
    $type_options = [
      'boolean' => t('Boolean'),
      'string' => t('Text'),
      'text' => t('Text (long)'),
      'integer' => t('Integer'),
      'float' => t('Float'),
      'numeric' => t('Decimal'),
      'email' => t('Email'),
    ];
    return $type_options;
  }

  /**
   * {@inheritdoc}
   */
  public static function fieldSettingsToConfigData(array $settings) {
    foreach (['first', 'second'] as $subfield) {
      $structured_values = array();
      foreach ($settings[$subfield]['allowed_values'] as $value => $label) {
        $structured_values[] = array(
          'value' => $value,
          'label' => $label,
        );
      }
      $settings[$subfield]['allowed_values'] = $structured_values;
    }
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function fieldSettingsFromConfigData(array $settings) {
    foreach (['first', 'second'] as $subfield) {
      $structured_values = array();
      foreach ($settings[$subfield]['allowed_values'] as $item) {
        $structured_values[$item['value']] = $item['label'];
      }
      $settings[$subfield]['allowed_values'] = $structured_values;

    }
    return $settings;
  }

}
