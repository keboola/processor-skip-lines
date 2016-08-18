#!/bin/bash

docker login -e="." -u="$QUAY_USERNAME" -p="$QUAY_PASSWORD" quay.io
docker tag keboola/processor-skip-lines quay.io/keboola/processor-skip-lines:$TRAVIS_TAG
docker tag keboola/processor-skip-lines quay.io/keboola/processor-skip-lines:latest
docker images
docker push quay.io/keboola/processor-skip-lines:$TRAVIS_TAG
docker push quay.io/keboola/processor-skip-lines:latest
