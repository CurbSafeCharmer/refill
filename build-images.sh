#!/bin/bash

pushd refill-api.toolforge.org/refill/backend/
pipenv lock -r > requirements.txt
popd

docker build -t zhaofengli/refill-api -f refill-api.toolforge.org/refill/docker/refill-api/Dockerfile backend/
echo ----------
docker build -t zhaofengli/refill-worker -f refill-api.toolforge.org/refill/docker/refill-worker/Dockerfile backend/
