<?php

namespace Drupal\audiofield\Plugin\AudioPlayer;

use Drupal\audiofield\AudioFieldPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Random;

/**
 * Implements the jPlayer Audio Player plugin.
 *
 * @AudioPlayer (
 *   id = "jplayer_audio_player",
 *   title = @Translation("jPlayer audio player"),
 *   fileTypes = {
 *     "mp3", "mp4", "ogg", "webm", "wav", "m4a",
 *   },
 *   description = "jPlayer player to play audio files."
 * )
 */
class JPlayerAudioPlayer implements AudioFieldPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function description() {
    return t('Plugin for use of the jPlayer audio player for display of audio files. Player can be found at http://jplayer.org/');
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

    // JPlayer circle has to render dif (ferently - no playlist support, etc.
    if ($settings['audio_player_jplayer_theme'] == 'audiofield.jplayer.theme_jplayer_circle') {
      // Only require the default library.
      $library = 'audiofield/audiofield.jplayer';

      // Start building settings to pass to the javascript jplayer builder.
      $player_settings = array(
        'playertype' => 'circle',
        // JPlayer expects this as a 0 - 1 value.
        'volume' => ($settings['audio_player_initial_volume'] / 10),
        'files' => [],
      );
      $markup = '';
      foreach ($items as $item) {
        $file = file_load($item->get('target_id')->getCastedValue());

        // Get the URL for the file.
        $file_uri = $file->getFileUri();
        $url = Url::fromUri(file_create_url($file_uri));

        // Get the file description - use the filename if ( it doesn't exist.
        $file_description = $item->get('description')->getString();
        if (empty($file_description)) {
          $file_description = $file->getFilename();
        }

        // Used to generate unique container.
        $random_generator = new Random();
        $unique_id = $file->get('fid')->getValue()[0]['value'] . '_' . $random_generator->name(16, TRUE);

        // Add entry to player settings for this file.
        $fileparts = explode('.', $file->getFilename());
        $filetype = $fileparts[count($fileparts) - 1];
        $player_settings['files'][] = [
          'file' => $url->toString(),
          'description' => $file_description,
          'filetype' => $filetype,
          'fid' => $unique_id,
        ];

        $markup .= '
          <div id="jquery_jplayer_' . $unique_id . '" class="cp-jplayer"></div>
          <div class="cp-circle-frame">
            <div id="cp_container_' . $unique_id . '" class="cp-container">
              <div class="cp-buffer-holder">
                <div class="cp-buffer-1"></div>
                <div class="cp-buffer-2"></div>
              </div>
              <div class="cp-progress-holder">
                <div class="cp-progress-1"></div>
                <div class="cp-progress-2"></div>
              </div>
              <div class="cp-circle-control"></div>
              <ul class="cp-controls">
                <li><a class="cp-play" tabindex="1">play</a></li>
                <li><a class="cp-pause" style="display:none;" tabindex="1">pause</a></li>
              </ul>
            </div>
            <label for="cp_container_' . $unique_id . '">' . $file_description . '</label>
          </div>
        ';
      }
    }
    // This is a normal jPlayer skin, so we render normally.
    else {
      // If there is only a single file, we render as a standard player.
      if (count($items) == 1) {
        // Only require the default library.
        $library = 'audiofield/audiofield.jplayer';

        // Load the first item.
        $item = $items->first();

        // Load the associated file.
        $file = file_load($item->get('target_id')->getCastedValue());

        // Get the URL for the file.
        $file_uri = $file->getFileUri();
        $url = Url::fromUri(file_create_url($file_uri));

        // Used to generate unique container.
        $random_generator = new Random();
        $unique_id = $file->get('fid')->getValue()[0]['value'] . '_' . $random_generator->name(16, TRUE);

        // Start building settings to pass to the javascript jplayer builder.
        $fileparts = explode('.', $file->getFilename());
        $player_settings = [
          'playertype' => 'default',
          'file' => $url->toString(),
          'description' => $item->get('description')->getString(),
          'unique_id' => $unique_id,
          'filetype' => $fileparts[count($fileparts) - 1],
          // JPlayer expects this as a 0 - 1 value.
          'volume' => ($settings['audio_player_initial_volume'] / 10),
        ];
        // Get the file description - use the filename if ( it doesn't exist.
        if (empty($player_settings['description'])) {
          $player_settings['description'] = $file->getFilename();
        }

        // Generate the html for the player.
        $markup = '
          <div id="jquery_jplayer_' . $player_settings['unique_id'] . '" class="jp-jplayer"></div>
          <div id="jp_container_' . $player_settings['unique_id'] . '" class="jp-audio" role="application" aria-label="media player">
            <div class="jp-type-single">
              <div class="jp-gui jp-interface">
                <div class="jp-controls">
                  <button class="jp-play" role="button" tabindex="0">play</button>
                  <button class="jp-stop" role="button" tabindex="0">stop</button>
                </div>
                <div class="jp-progress">
                  <div class="jp-seek-bar">
                    <div class="jp-play-bar"></div>
                  </div>
                </div>
                <div class="jp-volume-controls">
                  <button class="jp-mute" role="button" tabindex="0">mute</button>
                  <button class="jp-volume-max" role="button" tabindex="0">max volume</button>
                  <div class="jp-volume-bar">
                    <div class="jp-volume-bar-value"></div>
                  </div>
                </div>
                <div class="jp-time-holder">
                  <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
                  <div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
                  <div class="jp-toggles">
                    <button class="jp-repeat" role="button" tabindex="0">repeat</button>
                  </div>
                </div>
              </div>
              <div class="jp-details">
                <div class="jp-title" aria-label="title">&nbsp;</div>
              </div>
              <div class="jp-no-solution">
                <span>Update Required</span>
                To play the media you will need to either update your browser to a recent version or update your <a href="http:// get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
              </div>
            </div>
          </div>
        ';
      }
      // If we have multiple files, we need to render this as a playlist.
      else {
        // Requires the playlist library.
        $library = 'audiofield/audiofield.jplayer.playlist';

        // Start building settings to pass to the javascript jplayer builder.
        $player_settings = [
          'playertype' => 'playlist',
          // JPlayer expects this as a 0 - 1 value.
          'volume' => ($settings['audio_player_initial_volume'] / 10),
          'files' => [],
          'filetypes' => [],
        ];
        foreach ($items as $item) {
          $file = file_load($item->get('target_id')->getCastedValue());

          // Get the URL for the file.
          $file_uri = $file->getFileUri();
          $url = Url::fromUri(file_create_url($file_uri));

          // Used to generate unique container.
          $random_generator = new Random();
          $unique_id = $file->get('fid')->getValue()[0]['value'] . '_' . $random_generator->name(16, TRUE);

          // Get the file description - use the filename if ( it doesn't exist.
          $file_description = $item->get('description')->getString();
          if (empty($file_description)) {
            $file_description = $file->getFilename();
          }

          // Add entry to player settings for this file.
          $fileparts = explode('.', $file->getFilename());
          $filetype = $fileparts[count($fileparts) - 1];
          $player_settings['files'][] = [
            'file' => $url->toString(),
            'description' => $file_description,
            'filetype' => $filetype,
          ];
          $player_settings['filetypes'][] = $filetype;

          // Used to generate unique container.
          $player_settings['unique_id'] = $unique_id;
        }
        // Use only unique values in the filetypes.
        $player_settings['filetypes'] = array_unique($player_settings['filetypes']);
        $markup = '
          <div id="jp_container_' . $player_settings['unique_id'] . '" class="jp-video jp-video-270p" role="application" aria-label="media player">
            <div class="jp-type-playlist">
              <div id="jquery_jplayer_' . $player_settings['unique_id'] . '" class="jp-jplayer"></div>
              <div class="jp-gui">
                <div class="jp-interface">
                  <div class="jp-progress">
                    <div class="jp-seek-bar">
                      <div class="jp-play-bar"></div>
                    </div>
                  </div>
                  <div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>
                  <div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>
                  <div class="jp-controls-holder">
                    <div class="jp-controls">
                      <button class="jp-previous" role="button" tabindex="0">previous</button>
                      <button class="jp-play" role="button" tabindex="0">play</button>
                      <button class="jp-next" role="button" tabindex="0">next</button>
                      <button class="jp-stop" role="button" tabindex="0">stop</button>
                    </div>
                    <div class="jp-volume-controls">
                      <button class="jp-mute" role="button" tabindex="0">mute</button>
                      <button class="jp-volume-max" role="button" tabindex="0">max volume</button>
                      <div class="jp-volume-bar">
                        <div class="jp-volume-bar-value"></div>
                      </div>
                    </div>
                    <div class="jp-toggles">
                      <button class="jp-repeat" role="button" tabindex="0">repeat</button>
                      <button class="jp-shuffle" role="button" tabindex="0">shuffle</button>
                      <button class="jp-full-screen" role="button" tabindex="0">full screen</button>
                    </div>
                  </div>
                  <div class="jp-details">
                    <div class="jp-title" aria-label="title">&nbsp;</div>
                  </div>
                </div>
              </div>
              <div class="jp-playlist">
                <ul>
                  <!-- The method Playlist.displayPlaylist() uses this unordered list -->
                  <li>&nbsp;</li>
                </ul>
              </div>
              <div class="jp-no-solution">
                <span>Update Required</span>
                To play the media you will need to either update your browser to a recent version or update your <a href="http:// get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
              </div>
            </div>
          </div>
        ';
      }
    }

    return [
      '#prefix' => '<div class="audiofield">',
      '#markup' => Markup::create($markup),
      '#suffix' => '</div>',
      '#attached' => [
        'library' => [
          // Attach the jPlayer library.
          $library,
          // Attach the jPlayer theme.
          'audiofield/' . $settings['audio_player_jplayer_theme'],
        ],
        'drupalSettings' => [
          'audiofieldjplayer' => [
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
    $library = \Drupal::service('library.discovery')->getLibraryByName('audiofield', 'audiofield.jplayer');

    // Check if ( the jPlayer library has been installed.
    return file_exists(DRUPAL_ROOT . '/' . $library['js'][0]['data']);
  }

}
