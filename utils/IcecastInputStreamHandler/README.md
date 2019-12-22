IcecastInputStreamHandler
=========================

This daemon polls specified icecast URL and relays it to specified server.

If input and output codecs mismatch it will do reencoding, otherwise it streams input as is.

It helpful when your streaming users mess with codec setup.

Requirements
------------

* ffmpeg
* ffprobe

Usage
-----

Create two icecast mountpoints: `inputstream` and `stream.ogg`

Configure icecastinputstreamhandler to poll `inputstream` and stream to `stream.ogg`.

Your listeners should connect to `stream.ogg` and streaming users should stream to `inputstream`.
