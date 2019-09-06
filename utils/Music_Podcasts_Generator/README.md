Audio Podcasts Generator
========================

This util searches for audio links posted on the forum and generates audio playlists for given period (e.g. monthly, weekly, e.t.c.)

Compile all python modules before starting to use or after any update:

    python -m compileall src/

All scripts supposed to run from CRON.

Examples
--------

    src/podcastgenerator config/monthly.json

This will generate json and m3u playlists based on the given config file.

    src/liquidsoap-queue config/liquidsoap-monthly.json

This will add audio files from playlist mentioned in the given config to streaming queue.
