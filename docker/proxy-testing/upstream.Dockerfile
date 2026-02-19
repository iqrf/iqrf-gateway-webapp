# Copyright 2017-2026 IQRF Tech s.r.o.
# Copyright 2019-2026 MICRORISC s.r.o.
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

FROM python:3.12-slim

COPY docker/proxy-testing/upstream-requirements.txt /app/requirements.txt
COPY docker/proxy-testing/upstream/* /app
WORKDIR /app

RUN pip install --no-cache-dir -r requirements.txt
RUN apt-get update && apt-get install -y iproute2

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ss -tln | grep ':9500' || exit 1

EXPOSE 9500

CMD ["python3", "server.py"]
