#!/bin/sh

##  GetTORExitNodes - get list of TOR exit nodes IPs.
##  Copyright (C) 2015  BrainFucker <retratserif@gmail.com>
##  
##  This program is free software: you can redistribute it and/or modify
##  it under the terms of the GNU General Public License as published by
##  the Free Software Foundation, either version 3 of the License, or
##  (at your option) any later version.
##  
##  This program is distributed in the hope that it will be useful,
##  but WITHOUT ANY WARRANTY; without even the implied warranty of
##  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
##  GNU General Public License for more details.

set -e

INFO="Get list of TOR exit nodes.

USAGE
    $0 output_file

DESCRIPTION
    This script supposed to run from CRON.
    
    Output file is a list of IPs each on new line. It may be loaded by your web app to ban TOR users.

COPYRIGHT
    GetTORExitNodes Copyright (C) 2015 BrainFucker
    This program comes with ABSOLUTELY NO WARRANTY.
    This is free software, and you are welcome to redistribute it
    under certain conditions.
"

if [ -z "$1" ] || [ "$1" = '-h' ] || [ "$1" = '--help' ]; then
    echo "$INFO"
    exit 1
fi

RAWNODES="$(mktemp)"
NEWNODES="$(mktemp)"

wget -T 3 -t 2 -O "$RAWNODES" -q 'https://check.torproject.org/exit-addresses'

## If previous command was successful
if [ $? -eq 0 ]
  then
    # get IPs list
    cat "$RAWNODES" | grep ExitAddress | awk '{print $2}' > "$NEWNODES"
fi

mv "$NEWNODES" "$1"
rm "$RAWNODES"
