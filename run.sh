#!/bin/sh
set -e

if [ -z "$KBC_PARAMETER_LINES" ]; then
    echo "Parameter 'lines' not defined."
    exit 1
fi

cd $KBC_DATADIR/in/files/
# create folders
find . ! -iname "*.manifest" ! -name "." -type d | xargs -n1 -I {} sh -c "mkdir $KBC_DATADIR/out/files/\"{}\""
# process files
find . ! -iname "*.manifest" ! -name "." -type f | xargs -n1 -I {} sh -c "tail -n +$(expr $KBC_PARAMETER_LINES + 1) \"{}\" > $KBC_DATADIR/out/files/\"{}\""
