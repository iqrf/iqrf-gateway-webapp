# Copyright 2017-2024 IQRF Tech s.r.o.
# Copyright 2019-2024 MICRORISC s.r.o.
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

FROM node:lts AS builder

LABEL maintainer="roman.ondracek@iqrf.com"

WORKDIR /app

COPY . /app
RUN npm install --global npm pnpm
COPY . /app
RUN sed -i "s/\t\"commit\"\: .*/\t\"commit\"\: \"`git rev-parse --verify HEAD`\",/" version.json
RUN pnpm install
RUN pnpm run build

FROM --platform=linux/386 i386/nginx:stable

LABEL maintainer="roman.ondracek@iqrf.com"

COPY --from=builder /app/www/dist /app
COPY docker/frontend/config/nginx.conf /etc/nginx/conf.d/default.conf

CMD ["nginx", "-g", "daemon off;"]

EXPOSE 80 443
