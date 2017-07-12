<?php

namespace Drupal\audiofield\Plugin\AudioPlayer;

use Drupal\audiofield\AudioFieldPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Random;

/**
 * Implements the MediaElement Audio Player plugin.
 *
 * @AudioPlayer (
 *   id = "mediaelement_audio_player",
 *   title = @Translation("MediaElement audio player"),
 *   fileTypes = {
 *     "mp3", "webm", "mp4",
 *   },
 *   description = "MediaElement player to play audio files."
 * )
 */
class MediaElementAudioPlayer implements AudioFieldPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function description() {
    return t('Plugin for use of the MediaElement audio player for display of audio files. Player can be found at http://mediaelementjs.com/');
  }

  /**
   * {@inheritdoc}
   */
  public function renderPlayer(FieldItemListInterface $items, $langcode, $settings) {
    // Check to make sure we're installed.
    if (!$this->checkInstalled()) {
      drupal_set_message(t('Error: audiofield library is not currently installed! See the @status_report for more information.', [
        '@status_report' => \Drupal::l(t('status report'), Url::fromRoute('system.status')),
      ]), 'error');

      // Simply return the default rendering so the files are still displayed.
      $default_player = new DefaultMp3Player();
      return $default_player->renderPlayer($items, $langcode, $settings);
    }

    // Start building settings to pass to the javascript MediaElement builder.
    $player_settings = array(
      // MediaElement expects this as a 0 - 1 range.
      'volume' => ($settings['audio_player_initial_volume'] / 10),
      'elements' => [],
    );

    $markup = '';
    foreach ($items as $item) {
      // Load the associated file.
      $file = file_load($item->get('target_id')->getCastedValue());

      // Get the URL for the file.
      $file_uri = $file->getFileUri();
      $url = Url::fromUri(file_create_url($file_uri));

      // Get the file description - use the filename if it doesn't exist.
      $file_description = $item->get('description')->getString();
      if (empty($file_description)) {
        $file_description = $file->getFilename();
      }

      // Used to generate unique container.
      $random_generator = new Random();
      $unique_id = $file->get('fid')->getValue()[0]['value'] . '_' . $random_generator->name(16, TRUE);

      // Pass the element name for the player so we know what to render.
      $player_settings['elements'][] = '#mediaelement_player_' . $unique_id;

      // Generate HTML markup for the player.
      $markup .= '<div class="mediaelementaudio_frame">
          <audio id="mediaelement_player_' . $unique_id . '" controls>
            <source src="' . $url->toString() . '" type="' . $file->getMimeType() . '">
            Your browser does not support the audio element.
          </audio>
        </div>
        <label for="mediaelement_player_' . $unique_id . '">' . $file_description . '</label>';
    }

    // Include the proper library.
    $library = 'audiofield.mediaelement';

    return [
      '#prefix' => '<div class="audiofield">',
      '#markup' => Markup::create($markup),
      '#suffix' => '</div>',
      '#attached' => [
        'library' => [
          // Attach the MediaElement library.
          'audiofield/' . $library,
        ],
        'drupalSettings' => [
          'audiofieldmediaelement' => [
            $unique_id => $player_settings,
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function checkInstalled() {
    // Load the library.
    $library = \Drupal::service('library.discovery')->getLibraryByName('audiofield', 'audiofield.mediaelement');

    // Check if the MediaElement library has been installed.
    return file_exists(DRUPAL_ROOT . '/' . $library['js'][0]['data']);
  }

}
