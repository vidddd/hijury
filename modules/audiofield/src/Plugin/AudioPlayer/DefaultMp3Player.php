<?php

namespace Drupal\audiofield\Plugin\AudioPlayer;

use Drupal\audiofield\AudioFieldPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Implements the Default HTML5 Audio Player plugin.
 *
 * @AudioPlayer (
 *   id = "default_mp3_player",
 *   title = @Translation("default HTML5 audio player"),
 *   fileTypes = {
 *     "mp3","mp4","webm","ogg", "m4a","flac"
 *   },
 *   description = "Default html5 player to play audio files."
 * )
 */
class DefaultMp3Player implements AudioFieldPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function description() {
    return t('Plugin for use of the built-in HTML5 audio player for display of audio files.');
  }

  /**
   * {@inheritdoc}
   */
  public function renderPlayer(FieldItemListInterface $items, $langcode, $settings) {
    $render = array();
    foreach ($items as $item) {
      // Load the associated file.
      $file = file_load($item->get('target_id')->getCastedValue());

      // Get the file URL.
      $file_uri = $file->getFileUri();
      $url = Url::fromUri(file_create_url($file_uri));

      // Get the file description - use the filename if it doesn't exist.
      $file_description = $item->get('description')->getString();
      if (empty($file_description)) {
        $file_description = $file->getFilename();
      }

      $markup = "<audio controls>
               <source src='" . $url->toString() . "' type='audio/mpeg'>
               Your browser does not support the audio element.
            </audio>
            <label>" . $file_description . "</label>";
      $render[] = ['#markup' => Markup::create($markup)];
    }
    return $render;
  }

  /**
   * {@inheritdoc}
   */
  public function checkInstalled() {
    // This is built in to HTML5, so it is always "installed".
    return TRUE;
  }

}
