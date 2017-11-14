#!/bin/sh
set -e

rm -rf /tmp/tests
mkdir /tmp/tests
cp -r /code/tests/data /tmp/tests

echo "Running tests"
export KBC_DATADIR=/tmp/tests/data
export KBC_PARAMETER_LINES=2

/code/run.sh

diff -w $KBC_DATADIR/out/files/radio.csv /code/tests/sample/radio.csv
diff -w $KBC_DATADIR/out/files/text.csv /code/tests/sample/text.csv
diff -w $KBC_DATADIR/out/files/sliced/text.csv /code/tests/sample/sliced/text.csv
diff -w $KBC_DATADIR/out/files/sliced/radio.csv /code/tests/sample/sliced/radio.csv

echo "Tests finished"
