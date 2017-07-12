
Audiofield Module Readme
----------------------

<strong>Project URL</strong> - https://www.drupal.org/project/audiofield

This module creates a new formatter to play audio files.

It introduces a new plugin manager <strong>AudioPlayer</strong>


Installation
------------

To install this module, place it in your modules folder and enable it on the
modules page.

This module will play audio using the HTML5 built-in audio player by default.

Each plugin (other than the default HTML5 audio player) requires installation
of additional files. You can check the current status of installation at
/admin/reports/status or read below for further details on how to install
each player

Audio.js Audio Player
  Audio.js requires installation of the audio.js library located at
  http://kolber.github.io/audiojs/ - these files should be downloaded and the
  subfolder titled "audiojs" should be placed in /libraries/audiojs such that
  the file audio.min.js is found at /libraries/audiojs/.

  Audio.js does not offer any theme options, but can be fully styled by custom
  CSS.

jPlayer Audio Player
  jPlayer requires installation of the jPlayer library located at
  http://jplayer.org/ - these files should be downloaded and placed
  in /libraries/jplayer. It should be installed such that the file
  jquery.jplayer.min.js can be found at /libraries/jplayer/dist/jplayer/.

  jPlayer comes bundled with multiple skins by default. You can install
  additional skins by placing them in /libraries/jplayer/dist/skin/ and
  flushing the drupal cache. The module will automatically detect the
  skins and allow for their selection during field configuration.
  Alternatively, you can choose "none" for your skin during configuration.
  This will provide you baseline audio-player functionality with no
  built-in styling so that you can style your player using CSS of your
  choosing.

MediaElement Audio Player
  MediaElement requires installation of the MediaElement library located at
  http://mediaelementjs.com/ - these files should be downloaded and placed
  in /libraries/mediaelement such that the file mediaelement-and-player.min.js
  is found at /libraries/mediaelement/build/..

SoundManager Audio Player
  WordPress Audio Player requires installation of the SoundManager library
  located at http://www.schillmania.com/projects/soundmanager2
  These files should be downloaded and placed in /libraries/soundmanager such
  that the file soundmanager2-nodebug-jsmin.js can be found at
  /libraries/soundmanager/script.

  SoundManager is a highly configurable library. However, handling and allowing
  for all of the possible configurations and implementations of SoundManager is
  outside the scope of this module. As such, we have included several of the
  "built-in" themes available in SoundManager's demos. Any further customization
  of the player must be done outside of this module.

WordPress Audio Player
  WordPress Audio Player requires installation of the Standalone version of
  the WordPress Audio Player located at http://wpaudioplayer.com/standalone
  These files should be downloaded and placed in /libraries/wordpress-audio such
  that the file audio-player.js can be found at /libraries/wordpress-audio.

  WordPress Audio Player only supports a single skin, but does support a
  single audio player for multiple files. However, the multi-file version of
  the audio player can make it difficult to notice the existence of additional
  tracks, so this module allows the user to opt to use a single player for each
  file.

Configuration
-------------

Configuration for the module is performed by modifying field display settings
for File uploads. You begin by selecting Audiofield as the format for your
files at /admin/structure/types/manage/CONTENT_TYPE/display . You can then
modify the format settings - select your audio player from the list and
you will be presented with additional configuration options for your selected
audio player.

Maintainers
------

Tamer Zoubi - <tamerzg@gmail.com>
Daniel Moberly - <daniel.moberly@gmail.com>
