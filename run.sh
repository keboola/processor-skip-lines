#!/bin/sh

cd $KBC_DATADIR/in/files/
find . -iname "*.csv" | xargs -n1 -I {} sh -c "tail -n +$KBC_PARAMETER_LINES \"{}\" > $KBC_DATADIR/out/tables/\"{}\""
