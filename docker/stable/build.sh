#!/bin/bash

set -x

TAG="stable"
TAGS=""
ARCHS=("amd64" "armel" "armhf" "arm64" "i386" "ppc64le")
REPO="iqrftech/iqrf-gateway-webapp"

for ARCH in "${ARCHS[@]}"
do
   docker build --no-cache -f "${ARCH}.Dockerfile" -t "${REPO}:${TAG}-${ARCH}" .
   docker push "${REPO}:${TAG}-${ARCH}"
   TAGS="${TAGS} ${REPO}:${TAG}-${ARCH}"
done

export DOCKER_CLI_EXPERIMENTAL="enabled"
docker manifest create "${REPO}:${TAG}" ${TAGS}
docker manifest annotate "${REPO}:${TAG}" "${REPO}:${TAG}-armel" --os=linux --arch=arm --variant=v6
docker manifest push "$REPO":"$TAG"
