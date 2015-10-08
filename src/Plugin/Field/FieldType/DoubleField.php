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

/**
 * Plugin implementation of the 'double_field' field type.
 *
 * @FieldType(
 *   id = "double_field",
 *   label = @Translation("Double field"),
 *   description = @Translation("Double field."),
 *   default_widget = "double_field",
 *   default_formatter = "unformatted_list"
 * )
 */
class DoubleField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {

    $settings = [];
    foreach (['first', 'second'] as $subfield) {
      $settings['storage'][$subfield] = [
        'type' => 'varchar',
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
  public function storageSettingsForm(array &$form  , FormStateInterface $form_state, $has_data) {

    $element = [];
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
        '#required' => TRUE,
        '#options' => $this->subfieldTypes() ,
        '#disabled' => $has_data,
      ];

      $element['storage'][$subfield]['maxlength'] = [
        '#type' => 'number',
        '#title' => t('Maximum length'),
        '#default_value' => $settings['storage'][$subfield]['maxlength'],
        '#required' => TRUE,
        '#description' => t('The maximum length of the subfield in characters.'),
        '#disabled' => $has_data,
        '#min' => 1,
        '#states' => [
          'visible' => [":input[name='settings[storage][$subfield][type]']" => ['value' => 'int']],
        ],
      ];

      $element['storage'][$subfield]['precision'] = [
        '#type' => 'select',
        '#title' => t('Precision'),
        '#options' => array_combine(range(10, 32), range(10, 32)),
        '#default_value' => $settings['storage'][$subfield]['precision'],
        '#description' => t('The total number of digits to store in the database, including those to the right of the decimal.'),
        '#disabled' => $has_data,
        '#states' => [
          'visible' => [":input[name='settings[storage][$subfield][type]']" => ['value' => 'numeric']],
        ],
      ];

      $element['storage'][$subfield]['scale'] = [
        '#type' => 'select',
        '#title' => t('Scale'),
        '#options' => array_combine(range(0, 10), range(0, 10)),
        '#default_value' => $settings['storage'][$subfield]['scale'],
        '#description' => t('The number of digits to the right of the decimal.'),
        '#disabled' => $has_data,
        '#states' => [
          'visible' => [":input[name='settings[[storage][$subfield][type]']" => ['value' => 'numeric']],
        ],
      ];

    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {

    $settings = [];
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

    $element = array();
    $settings = $this->getSettings();

	$types = self::subfieldTypes();
    foreach (['first', 'second'] as $subfield) {

	  $type = $settings['storage'][$subfield]['type'];

	  $title =  $subfield == 'first' ? t('First subfield') : t('Second subfield');
	  $title .= ' - ' .  $types[$type];

      $element[$subfield] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => FALSE,
        '#tree' => TRUE,
      ];

	  $element[$subfield]['required'] = array(
		'#type' => 'checkbox',
		'#title' => t('Required'),
		'#default_value' => $settings[$subfield]['required'],
	  );

	  $element[$subfield]['min'] = array(
		'#type' => 'number',
		'#title' => t('Minimum'),
		'#default_value' => $settings[$subfield]['min'],
		'#description' => t('The minimum value that should be allowed in this field. Leave blank for no minimum.'),
		'#access' => in_array($type, ['int', 'float', 'numeric']),
	  );

	  $element[$subfield]['max'] = array(
		'#type' => 'number',
		'#title' => t('Maximum'),
		'#default_value' => $settings[$subfield]['max'],
		'#description' => t('The maximum value that should be allowed in this field. Leave blank for no maximum.'),
		'#access' => in_array($type, ['int', 'float', 'numeric']),
	  );

	  $element[$subfield]['list'] = [
		'#type' => 'checkbox',
		'#title' => t('Limit allowed values'),
		'#default_value' => $settings[$subfield]['list'],
		'#access' => $type != 'boolean',
	  ];

	  $element[$subfield]['allowed_values'] = [
		'#type' => 'textarea',
		'#title' => t('Allowed values list'),
		'#default_value' => $this->allowedValuesString($settings[$subfield]['allowed_values']),
		'#rows' => 10,
		'#element_validate' => [[get_class($this), 'validateAllowedValues']],
		'#field_name' => $this->getFieldDefinition()->getName(),
		'#entity_type' => $this->getEntity()->getEntityTypeId(),
		'#allowed_values' => $settings[$subfield]['allowed_values'],
		'#states' => [
		  'invisible' => [
			[":input[name='field[settings][$subfield][list]']" => ['checked' => FALSE]],
		  ],
		],
		'#description' => $this->allowedValuesDescription(),
		'#access' => $type != 'boolean',
	  ];

	  $element[$subfield]['on_label'] = array(
		'#type' => 'textfield',
		'#title' => t('"On" label'),
		'#default_value' => $settings[$subfield]['on_label'],
		'#access' => $type == 'boolean',
	  );
	  $element[$subfield]['off_label'] = array(
		'#type' => 'textfield',
		'#title' => t('"Off" label'),
		'#default_value' => $settings[$subfield]['off_label'],
		'#access' => $type == 'boolean',
	  );

    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {

    $settings = $this->getSettings();

    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    $subconstrains = [];
    foreach (['first', 'second'] as $subfield) {
      if ($settings['storage'][$subfield]['type'] != 'boolean' && $settings[$subfield]['list'] && $settings[$subfield]['allowed_values']) {
		$allowed_values = array_keys($settings[$subfield]['allowed_values']);
		$allowed_values[] = '';
        $subconstrains[$subfield]['AllowedValues'] = $allowed_values;
      }
      if ($settings['storage'][$subfield]['type'] == 'varchar') {
        $subconstrains[$subfield]['Length'] = ['max' => $settings['storage'][$subfield]['maxlength']];
      }
      if ($settings[$subfield]['required']) {
        $subconstrains[$subfield]['NotBlank'] = [];
      }
	  if ($settings['storage'][$subfield]['type'] == 'boolean') {
		$subconstrains[$subfield]['AllowedValues'] = [0, 1];
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
        'type' => $type == 'boolean' ? 'int' : $type,
        'not null' => FALSE,
        'description' => ucfirst($subfield) . ' subfield value.',
      ];

      switch ($settings['storage'][$subfield]['type']) {
        case 'varchar':
          $columns[$subfield]['length'] = $settings['storage'][$subfield]['maxlength'];
          break;

        case 'text':
          $columns[$subfield]['size'] = 'big';
          break;

        case 'int':
        case 'float':
          $columns[$subfield]['size'] = 'normal';
          break;

        case 'boolean':
          $columns[$subfield]['size'] = 'tiny';
          break;

        case 'decimal':
          $columns[$subfield]['precision'] = $settings['storage'][$subfield]['precision'];
          $columns[$subfield]['scale'] = $settings['storage'][$subfield]['scale'];
          break;
      }
    }

    return ['columns' => $columns];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $primitive_types = self::subfieldPrimiriveTypes();

    $settings = $field_definition->getSettings();
    foreach (['first', 'second'] as $subfield) {
      $subfield_type = $settings['storage'][$subfield]['type'];
      $properties[$subfield] = DataDefinition::create($primitive_types[$subfield_type][0])
        ->setLabel($primitive_types[$subfield_type][1]);
    }


    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected function allowedValuesDescription() {
    $description[] = t('The possible values this field can contain. Enter one value per line, in the format key|label.');
    $description[] = t('The key is the stored value, and must be numeric. The label will be used in displayed values and edit forms.');
    $description[] = t('The label is optional: if a line contains a single number, it will be used as key and label.');
    $description[] = t('Lists of labels are also accepted (one label per line), only if the field does not hold any values yet. Numeric keys will be automatically generated from the positions in the list.');
    return implode('<br/>', $description);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
	$is_empty = TRUE;
	foreach (['first', 'second'] as $subfield) {
	  $is_empty = $is_empty && ($this->{$subfield} === NULL || $this->{$subfield} === '');
	}
    return $is_empty;
  }

  /**
   * #element_validate callback for options field allowed values.
   *
   * @param $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param $form_state
   *   The current state of the form for the form this element belongs to.
   *
   * @see \Drupal\Core\Render\Element\FormElement::processPattern()
   */
  public static function validateAllowedValues($element, FormStateInterface $form_state) {
    $values = static::extractAllowedValues($element['#value']);

    if (!is_array($values)) {
      $form_state->setError($element, t('Allowed values list: invalid input.'));
    }
    else {
      // Check that keys are valid for the field type.
      foreach ($values as $key => $value) {
        if ($error = static::validateAllowedValue($key)) {
          $form_state->setError($element, $error);
          break;
        }
      }

      $form_state->setValueForElement($element, $values);
    }
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListTextItem::allowedValuesString()
   */
  protected static function extractAllowedValues($string) {

    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generated_keys = $explicit_keys = FALSE;
    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
        $explicit_keys = TRUE;
      }
      // Otherwise see if we can use the value as the key.
      elseif (!static::validateAllowedValue($text)) {
        $key = $value = $text;
        $explicit_keys = TRUE;
      }
      // Otherwise see if we can generate a key from the position.
      else {
        $key = (string) $position;
        $value = $text;
        $generated_keys = TRUE;
      }
      $values[$key] = $value;
    }

    // We generate keys only if the list contains no explicit key at all.
    if ($explicit_keys && $generated_keys) {
      return;
    }

    return $values;
  }

  /**
   * Checks whether a candidate allowed value is valid.
   *
   * @param string $option
   *   The option value entered by the user.
   *
   * @return string
   *   The error message if the specified value is invalid, NULL otherwise.
   */
  protected static function validateAllowedValue($option) {
    return FALSE;
    return t('Allowed values list: keys must be integers.');
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
  protected function allowedValuesString($values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }


  /**
   *
   */
  public  static function subfieldTypes() {
    $type_options = [
      'boolean' => t('Boolean'),
      'varchar' => t('Text'),
      'text' => t('Text (long)'),
      'int' => t('Integer'),
      'float' => t('Float'),
      'numeric' => t('Decimal'),
    ];
    return $type_options;
  }

  /**
   *
   */
  protected static function subfieldPrimiriveTypes() {
    $type_options = [
      'boolean' => ['integer', t('Integer')],
      'varchar' => ['string', t('String')],
      'text' => ['string', t('String')],
      'int' => ['integer', t('Integer')],
      'float' => ['float', t('FLoat')],
      // What is proper primitive type
      // for decimal subfields?
      'numeric' => ['float', t('FLoat')],
    ];
    return $type_options;
  }

}
