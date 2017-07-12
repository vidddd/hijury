<?php

namespace Drupal\audiofield\Plugin\AudioPlayer;

use Drupal\audiofield\AudioFieldPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Implements the SoundManager Audio Player plugin.
 *
 * @AudioPlayer (
 *   id = "soundmanager_audio_player",
 *   title = @Translation("SoundManager audio player"),
 *   fileTypes = {
 *     "mp3", "mp4", "ogg", "opus", "wav",
 *   },
 *   description = "SoundManager player to play audio files."
 * )
 */
class SoundManagerAudioPlayer implements AudioFieldPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function description() {
    return t('Plugin for use of the SoundManager audio player for display of audio files. Player can be found at http://www.schillmania.com/projects/soundmanager2');
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

    // Start building settings to pass to the javascript SoundManager builder.
    $player_settings = array(
      // SoundManager expects this as a 0 - 100 range.
      'volume' => ($settings['audio_player_initial_volume'] * 10),
      'swfpath' => '/libraries/soundmanager/swf/',
    );

    $markup = '';
    // Loop over each item.
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

      // Generate HTML markup for the player (different for each theme).
      switch ($settings['audio_player_soundmanager_theme']) {
        case 'default':
          $markup .= '<div class="soundmanageraudio_frame"><a href="' . $url->toString() . '" class="sm2_button">' . $file_description . '</a> <label>' . $file_description . '</label></div>';
          break;

        case 'player360':
          $markup .= '<div class="ui360"><a href="' . $url->toString() . '">' . $file_description . '</a></div>';
          break;

        case 'barui':
          $markup .= '<li><a href="' . $url->toString() . '">' . $file_description . '</a></li>';
          break;

        case 'inlineplayer':
          $markup .= '<ul class="graphic"><li><a href="' . $url->toString() . '">' . $file_description . '</a></li></ul>';
          break;
      }
    }

    // These themes require additional markup.
    if ($settings['audio_player_soundmanager_theme'] == 'barui') {
      $markup = '
        <div class="sm2-bar-ui ' . ((count($items) > 1) ? 'playlist-open' : '') . '">
          <div class="bd sm2-main-controls">
            <div class="sm2-inline-texture"></div>
            <div class="sm2-inline-gradient"></div>
            <div class="sm2-inline-element sm2-button-element">
              <div class="sm2-button-bd">
                <a href="#play" class="sm2-inline-button play-pause">Play / pause</a>
              </div>
            </div>
            <div class="sm2-inline-element sm2-inline-status">
              <div class="sm2-playlist">
                <div class="sm2-playlist-target"><noscript><p>JavaScript is required.</p></noscript></div>
              </div>
              <div class="sm2-progress">
                <div class="sm2-row">
                  <div class="sm2-inline-time">0:00</div>
                  <div class="sm2-progress-bd">
                    <div class="sm2-progress-track">
                      <div class="sm2-progress-bar"></div>
                      <div class="sm2-progress-ball"><div class="icon-overlay"></div></div>
                    </div>
                  </div>
                  <div class="sm2-inline-duration">0:00</div>
                </div>
              </div>
            </div>
            <div class="sm2-inline-element sm2-button-element sm2-volume">
              <div class="sm2-button-bd">
                <span class="sm2-inline-button sm2-volume-control volume-shade"></span>
                <a href="#volume" class="sm2-inline-button sm2-volume-control">volume</a>
              </div>
            </div>
            <div class="sm2-inline-element sm2-button-element sm2-menu">
              <div class="sm2-button-bd">
                <a href="#menu" class="sm2-inline-button menu">menu</a>
              </div>
            </div>
          </div>
          <div class="bd sm2-playlist-drawer sm2-element">
            <div class="sm2-playlist-wrapper">
              <ul class="sm2-playlist-bd">' . $markup . '</ul>
            </div>
            <div class="sm2-extra-controls">
              <div class="bd">
                <div class="sm2-inline-element sm2-button-element">
                  <a href="#prev" title="Previous" class="sm2-inline-button previous">&lt; previous</a>
                </div>
                <div class="sm2-inline-element sm2-button-element">
                  <a href="#next" title="Next" class="sm2-inline-button next">&gt; next</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      ';
    }

    return [
      '#prefix' => '<div class="audiofield">',
      '#markup' => Markup::create($markup),
      '#suffix' => '</div>',
      '#attached' => [
        'library' => [
          // Attach the SoundManager library.
          'audiofield/audiofield.soundmanager',
          // Attach the skin library.
          'audiofield/audiofield.soundmanager.' . $settings['audio_player_soundmanager_theme'],
        ],
        'drupalSettings' => [
          'audiofieldsoundmanager' => $player_settings,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function checkInstalled() {
    // Load the library.
    $library = \Drupal::service('library.discovery')->getLibraryByName('audiofield', 'audiofield.soundmanager');

    // Check if the SoundManager library has been installed.
    return file_exists(DRUPAL_ROOT . '/' . $library['js'][0]['data']);
  }

}
