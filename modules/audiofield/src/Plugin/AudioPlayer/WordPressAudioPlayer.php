<?php

namespace Drupal\audiofield\Plugin\AudioPlayer;

use Drupal\audiofield\AudioFieldPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Random;

/**
 * Implements the WordPress Audio Player plugin.
 *
 * @AudioPlayer (
 *   id = "wordpress_audio_player",
 *   title = @Translation("WordPress audio player"),
 *   fileTypes = {
 *     "mp3",
 *   },
 *   description = "WordPress player to play audio files."
 * )
 */
class WordPressAudioPlayer implements AudioFieldPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function description() {
    return t('Plugin for use of the WordPress audio player for display of audio files. Player can be found at http://wpaudioplayer.com');
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

    // Start building settings to pass to the javascript WordPress builder.
    $player_settings = array(
      // WordPress expects this as a 0 - 100 range.
      'volume' => ($settings['audio_player_initial_volume'] * 10),
      'animate' => ($settings['audio_player_wordpress_animation'] ? 'yes' : 'no'),
      'files' => [],
    );

    // Create an array to hold the markup for each player.
    $player_html_markup = [];
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

      // Pass settings for the file.
      $player_settings['files'][] = [
        'file' => $url->toString(),
        'title' => $file_description,
        'unique_id' => $unique_id,
      ];

      // Generate HTML markup.
      $player_html_markup[] = '<div class="wordpressaudio_frame"><div class="wordpressaudioplayer" id="wordpressaudioplayer_' . $unique_id . '">' . $file_description . '</div></div>';
    }

    // If we are combining into a single player, make some modifications.
    if ($settings['audio_player_wordpress_combine_files']) {
      // Wordpress expects comma-deliminated lists
      // when using multiple files in a single player.
      $wp_files = [];
      $wp_titles = [];
      foreach ($player_settings['files'] as $wp_file) {
        $wp_files[] = $wp_file['file'];
        $wp_titles[] = $wp_file['title'];
      }

      // Redeclare settings with only a single (combined) file.
      $player_settings['files'] = [
        [
          'file' => implode(',', $wp_files),
          'title' => implode(',', $wp_titles),
          'unique_id' => $player_settings['files'][0]['unique_id'],
        ],
      ];

      // Only need the first player to be rendered for markup.
      $markup = $player_html_markup[0];
    }
    else {
      // We need all markup so just combine them.
      $markup = implode('', $player_html_markup);
    }

    return [
      '#type' => 'markup',
      '#prefix' => '<div class="audiofield">',
      '#markup' => Markup::create($markup),
      '#suffix' => '</div>',
      '#attached' => [
        'library' => [
          // Attach the WordPress library.
          'audiofield/audiofield.wordpress',
        ],
        'drupalSettings' => [
          'audiofieldwordpress' => [
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
    $library = \Drupal::service('library.discovery')->getLibraryByName('audiofield', 'audiofield.wordpress');

    // Check if the WordPress library has been installed.
    return file_exists(DRUPAL_ROOT . '/' . $library['js'][0]['data']);
  }

}
