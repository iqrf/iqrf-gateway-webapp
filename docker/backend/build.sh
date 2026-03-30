#!/bin/bash

# Copyright 2017-2025 IQRF Tech s.r.o.
# Copyright 2019-2025 MICRORISC s.r.o.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

set -ex

TAG="latest"
TAGS=""
ARCHS=("amd64" "armel" "armhf" "arm64" "i386" "ppc64le")
DOCKER_ARCHS=("amd64" "arm32v5" "arm32v7" "arm64v8" "386" "ppc64le")
DOCKER_PLATFORMS=("linux/amd64" "linux/arm/v5" "linux/arm/v7" "linux/arm64" "linux/386" "linux/ppc64le")
REPO="iqrftech/iqrf-gateway-webapp-backend"

DIR=${PWD}

cd ../../

for i in "${!ARCHS[@]}"; do
	ARCH="${ARCHS[$i]}"
	docker build --no-cache -f "${DIR}/Dockerfile" -t "${REPO}:${TAG}-${ARCH}" \
		--build-arg ARCH="${DOCKER_ARCHS[$i]}" \
		--build-arg PLATFORM="${DOCKER_PLATFORMS[$i]}" \
		.
	docker push "${REPO}:${TAG}-${ARCH}"
	TAGS="${TAGS} ${REPO}:${TAG}-${ARCH}"
done

export DOCKER_CLI_EXPERIMENTAL="enabled"
docker manifest create "${REPO}:${TAG}" ${TAGS}
docker manifest annotate "${REPO}:${TAG}" "${REPO}:${TAG}-armel" --os=linux --arch=arm --variant=v6
docker manifest push --purge "$REPO":"$TAG"
