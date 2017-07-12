<?php

namespace Drupal\audiofield\Plugin\AudioPlayer;

use Drupal\audiofield\AudioFieldPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Random;

/**
 * Implements the audio.js Audio Player plugin.
 *
 * @AudioPlayer (
 *   id = "audiojs_audio_player",
 *   title = @Translation("audio.js audio player"),
 *   fileTypes = {
 *     "mp3", "mp4", "ogg", "webm", "wav", "m4a",
 *   },
 *   description = "audio.js player to play audio files."
 * )
 */
class AudioJsAudioPlayer implements AudioFieldPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function description() {
    return t('Plugin for use of the audio.js audio player for display of audio files. Player can be found at http://kolber.github.io/audiojs/');
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

    // Start building settings to pass to the javascript audio.js builder.
    $player_settings = array(
      // Audio.js expects this as a 0 - 1 range.
      'volume' => ($settings['audio_player_initial_volume'] / 10),
      'element' => '',
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

      // Used to generate unique container.
      $player_settings['element'] = 'audiofield_audiojs_' . $unique_id;

      // Generate HTML markup for the player.
      $markup .= '<li><a href="#" data-src="' . $url->toString() . '">' . $file_description . '</a></li>';
    }

    // Add the HTML framework to the track listing.
    $markup = '<div id="' . $player_settings['element'] . '" class="audiofield-audiojs-frame">
      <div class="audiofield-audiojs">
        <audio preload="auto"></audio>
        <div class="play-pauseZ">
          <p class="playZ"></p>
          <p class="pauseZ"></p>
          <p class="loadingZ"></p>
          <p class="errorZ"></p>
        </div>
        <div class="scrubberZ">
          <div class="progressZ"></div>
          <div class="loadedZ"></div>
        </div>
        <div class="timeZ">
          <em class="playedZ">00:00</em>/<strong class="durationZ">00:00</strong>
        </div>
        <div class="error-messageZ"></div>
      </div>
      <ol>' . $markup . '</ol>
    </div>
    ';

    // Get the file ID of the last file for a unique identifier.
    $file->get('fid')->getValue()[0]['value'];

    return [
      '#prefix' => '<div class="audiofield">',
      '#markup' => Markup::create($markup),
      '#suffix' => '</div>',
      '#attached' => [
        'library' => [
          // Attach the audio.js library.
          'audiofield/audiofield.audiojs',
        ],
        'drupalSettings' => [
          'audiofieldaudiojs' => [
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
    $library = \Drupal::service('library.discovery')->getLibraryByName('audiofield', 'audiofield.audiojs');

    // Check if ( the audio.js library has been installed.
    return file_exists(DRUPAL_ROOT . '/' . $library['js'][0]['data']);
  }

}
