<?php

/**
 * @file
 * Contains \Drupal\double_field\Plugin\Field\FieldFormatter\UnformattedList.
 */

namespace Drupal\double_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

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
  public function viewElements(FieldItemListInterface $items) {

	$element = parent::viewElements($items);

    foreach ($items as $delta => $item) {

      $element[$delta] = [
        '#settings' => $this->getSettings(),
        '#item' => $item,
        '#theme' => 'double_field_item',
      ];
    }

    return $element;

  }

}
