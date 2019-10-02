# Copyright 2017 MICRORISC s.r.o.
# Copyright 2017-2019 IQRF Tech s.r.o.
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

.PHONY: all

all: qa phpstan cc tests

cache-purge:
	rm -rf temp/cache/

coverage: vendor
	vendor/bin/tester -p phpdbg -c ./tests/php.ini --coverage ./coverage.html --coverage-src ./app ./tests

cc: temp/code-checker
	php temp/code-checker/code-checker -l --no-progress --strict-types -i "coverage.*" -i "docs/" -i "tests/temp/" -i "www/dist/"

fix-cc: temp/code-checker
	php temp/code-checker/code-checker -f -l --no-progress --strict-types -i "coverage.*" -i "docs/" -i "tests/temp/" -i "www/dist/"

cs: vendor
	vendor/bin/codesniffer app bin tests

deb-package:
	debuild -b -uc -us

qa: lint cs

lint: vendor
	vendor/bin/linter app bin tests

phpstan: vendor
	NETTE_TESTER_RUNNER=1 vendor/bin/phpstan analyse -c phpstan.neon

temp/code-checker:
	composer create-project nette/code-checker temp/code-checker --no-interaction

tests: vendor
	vendor/bin/tester -p phpdbg -c ./tests/php.ini ./tests

vendor: composer.json
	composer install

webpack:
	webpack --mode production
