#!/bin/bash

set -ex

TAG="latest"
TAGS=""
ARCHS=("amd64" "armel" "armhf" "arm64" "i386" "ppc64le")
REPO="iqrftech/iqrf-gateway-webapp-frontend"

DIR=${PWD}

cd ../../

for ARCH in "${ARCHS[@]}"
do
   docker build --no-cache -f "${DIR}/${ARCH}.Dockerfile" -t "${REPO}:${TAG}-${ARCH}" .
   docker push "${REPO}:${TAG}-${ARCH}"
   TAGS="${TAGS} ${REPO}:${TAG}-${ARCH}"
done

export DOCKER_CLI_EXPERIMENTAL="enabled"
docker manifest rm "${REPO}:${TAG}"
docker manifest create "${REPO}:${TAG}" ${TAGS}
docker manifest annotate "${REPO}:${TAG}" "${REPO}:${TAG}-armel" --os=linux --arch=arm --variant=v6
docker manifest push "$REPO":"$TAG"
