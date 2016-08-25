#!/bin/sh

cd $KBC_DATADIR/in/files/
find . ! -iname "*.manifest" ! -name "." | xargs -n1 -I {} sh -c "tail -n +$KBC_PARAMETER_LINES \"{}\" > $KBC_DATADIR/out/files/\"{}\""
