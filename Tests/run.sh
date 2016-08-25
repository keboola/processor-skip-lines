#!/bin/sh

/code/Tests/prepare.sh

echo "Running tests"

/code/run.sh

cmp -s $KBC_DATADIR/in/files/radio.csv /code/Tests/sample/radio.csv || (echo "radio is different" && exit 1)
cmp -s $KBC_DATADIR/in/files/text.csv /code/Tests/sample/text.csv || (echo "text is different" && exit 1)

echo "Tests finished"

/code/Tests/cleanup.sh
