<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\HtmlList.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementations for 'html_list' formatter.
 *
 * @FieldFormatter(
 *  id = "html_list",
 *  label = @Translation("Html list"),
 *  field_types = {"double_field"}
 * )
 */
class HtmlList extends ListBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['list_type' => 'ul'] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $settings = $this->getSettings();

    $element['list_type'] = [
      '#type' => 'radios',
      '#title' => t('List type'),
      '#options' => [
        'ul' => t('Unordered list'),
        'ol' => t('Ordered list'),
        'dl' => t('Definition list'),
      ],
      '#default_value' => $settings['list_type'],
    ];

    $element += parent::settingsForm($form, $form_state);
    $field_name = $this->fieldDefinition->getName();

    $element['style']['#states']['invisible'] = [":input[name='fields[$field_name][settings_edit_form][settings][list_type]']" => ['value' => 'dl']];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = t('List type: %list_type', ['%list_type' => $this->getSetting('list_type')]);
    return array_merge($summary, parent::settingsSummary());
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $this->prepareItems($items);

    $settings = $this->getSettings();

    if ($settings['list_type'] == 'dl') {
      $element[0] = [
        '#theme' => 'double_field_definition_list',
        '#items' => $items,
        '#settings' => $settings,
      ];
    }
    else {
      foreach ($items as $delta => $item) {

        $list_items[$delta] = [
          '#settings' => $settings,
          '#item' => $item,
          '#theme' => 'double_field_item',
        ];
        if ($settings['style'] == 'inline') {
          $list_items[$delta]['#wrapper_attributes']['class'] = 'container-inline';
        }
      }
      $element[0] = [
        '#theme' => 'item_list',
        '#list_type' => $settings['list_type'],
        '#items' => $list_items,
      ];
    }

    $element[0]['#attributes']['class'][] = 'double-field-list';

    return $element;
  }

}
