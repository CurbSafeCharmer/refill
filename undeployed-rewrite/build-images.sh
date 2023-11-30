#!/bin/bash

pushd backend/
pipenv lock -r > requirements.txt
popd

docker build -t zhaofengli/refill-api -f docker/refill-api/Dockerfile backend/
echo ----------
docker build -t zhaofengli/refill-worker -f docker/refill-worker/Dockerfile backend/
