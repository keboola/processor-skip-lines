#!/bin/sh

cd $KBC_DATADIR/in/files/
# create folders
find . ! -iname "*.manifest" ! -name "." -type d | xargs -n1 -I {} sh -c "mkdir $KBC_DATADIR/out/files/\"{}\""
