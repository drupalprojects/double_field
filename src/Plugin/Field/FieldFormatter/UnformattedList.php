<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\UnformattedList.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Plugin implementations for 'double_field' formatter.
 *
 * @FieldFormatter(
 *  id = "unformatted_list",
 *  label = @Translation("Unformatted list"),
 *  field_types = {"double_field"}
 * )
 */
class UnformattedList extends ListBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $this->prepareItems($items);

    $element['#attributes']['class'][] = 'double-field-unformatted-list';

    $settings = $this->getSettings();
    foreach ($items as $delta => $item) {
      if ($settings['style'] == 'inline') {
        $item->_attributes = ['class' => 'container-inline'];
      }
      $element[$delta] = [
        '#settings' => $this->getSettings(),
        '#item' => $item,
        '#theme' => 'double_field_item',
      ];
    }

    return $element;
  }

}
