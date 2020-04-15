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

COMPOSER ?= composer

CACHE_DIR=${DESTDIR}/var/cache/iqrf-gateway-webapp
CONFIG_DIR=${DESTDIR}/etc/iqrf-gateway-webapp
DATA_DIR=${DESTDIR}/usr/share/iqrf-gateway-webapp
LOG_DIR=${DESTDIR}/var/log/iqrf-gateway-webapp
VENDOR_DIR=${DATA_DIR}/vendor
SBIN_DIR=${DESTDIR}/usr/sbin

.PHONY: build coverage cc fix-cc cs deb-package deps qa install lint phpstan rector test webpack

build:
	$(COMPOSER) install --no-dev

all: qa phpstan cc test

cache-purge:
	rm -rf temp/cache/

coverage: deps
	vendor/bin/tester -p phpdbg -c ./tests/php.ini --coverage ./coverage.html --coverage-src ./app ./tests

cc: temp/code-checker
	php temp/code-checker/code-checker -l --no-progress --strict-types -i "coverage.*" -i "docs/" -i "tests/temp/" -i "www/dist/"

fix-cc: temp/code-checker
	php temp/code-checker/code-checker -f -l --no-progress --strict-types -i "coverage.*" -i "docs/" -i "tests/temp/" -i "www/dist/"

cs: deps
	vendor/bin/codesniffer app bin tests

deb-package:
	debuild -b -uc -us

deps:
	composer install

qa: lint cs

install:
	install -d -o www-data $(CACHE_DIR)
	install -d $(CONFIG_DIR)
	cp app/config/* $(CONFIG_DIR)
	install -d $(CONFIG_DIR)/certs
	install -d $(DATA_DIR)
	cp -r app/ $(DATA_DIR)
	cp -r bin/ $(DATA_DIR)
	cp -r iqrf/ $(DATA_DIR)
	cp version.json $(DATA_DIR)
	cp -r vendor/ $(DATA_DIR)
	cp -r www/ $(DATA_DIR)
	install -d -o www-data $(LOG_DIR)
	install -d $(SBIN_DIR)
	ln -s ${DATA_DIR}/bin/manager $(SBIN_DIR)/iqrf-gateway-webapp-manager
	# Delete documentation
	find ${VENDOR_DIR} -type f -name "AUTHORS*" -delete
	find ${VENDOR_DIR} -type f -name "LICENSE*" -delete
	find ${VENDOR_DIR} -type f -name "README*" -delete
	find ${VENDOR_DIR} -type d -name -regex ".*/\(\.\)?[Dd]oc\(s\)?" -print0 | xargs -0 rm -rf
	# Delete composer files
	find ${VENDOR_DIR} -type f -name "composer.json" -delete
	# Delete bower files
	find ${VENDOR_DIR} -type f -name "bower.json" -delete
	# Delete NPM files
	find ${VENDOR_DIR} -type f -name ".npmignore" -delete
	find ${VENDOR_DIR} -type f -name "package.json" -delete
	# Delete ESLint files
	find ${VENDOR_DIR} -type f -name ".eslintrc.js" -delete
	# Delete git files
	find ${VENDOR_DIR} -type d -name ".git" -print0 | xargs -0 rm -rf
	find ${VENDOR_DIR} -type f -name ".gitattributes" -delete
	find ${VENDOR_DIR} -type f -name ".gitignore" -delete
	find ${VENDOR_DIR} -type f -name ".gitmodules" -delete
	# Delete CI files
	find ${VENDOR_DIR} -type f -regex ".*/\(appveyor\|\.gitlab-ci\|\.scrutinizer\|\.travis\)\.yml" -delete
	# Delete PHP Coding Standards Fixer files
	find ${VENDOR_DIR} -type f -regex ".*/\.php_cs\(\.dist\)?" -delete
	# Delete PHPStan files
	find ${VENDOR_DIR} -type f -name "phpstan.neon" -delete
	# Delete PHP_CodeSniffer files
	find ${VENDOR_DIR} -type f -name "ruleset.xml" -delete
	# Delete PHPUnit files
	find ${VENDOR_DIR} -type f -regex ".*/phpunit\.xml\(\.dist\)?" -delete
	# Delete examples
	find ${VENDOR_DIR} -type d -name "demo" -print0 | xargs -0 rm -rf
	find ${VENDOR_DIR} -type d -regex ".*/[Ee]xample\(s\)?/*" -print0 | xargs -0 rm -rf
	# Delete tests
	find ${VENDOR_DIR} -type d -regex ".*/[Tt]est\(s\)?/*" -print0 | xargs -0 rm -rf
	# Delete binaries
	find ${VENDOR_DIR} -type d -name "bin" -print0 | xargs -0 rm -rf
	find ${VENDOR_DIR} -type d -name "tools" -print0 | xargs -0 rm -rf
	find ${VENDOR_DIR} -type f -name "*.sh" -delete
	find ${VENDOR_DIR} -type f -name "Makefile" -delete
	find ${VENDOR_DIR} -type f -print0 | xargs -0 chmod -x
	# Delete empty directories
	find ${VENDOR_DIR} -type d -empty -delete

lint: deps
	vendor/bin/linter app bin tests

phpstan: deps
	NETTE_TESTER_RUNNER=1 vendor/bin/phpstan analyse -c phpstan.neon

rector: deps
	vendor/bin/rector process

temp/code-checker:
	composer create-project nette/code-checker temp/code-checker --no-interaction

test: deps
	vendor/bin/tester -p phpdbg -c ./tests/php.ini ./tests

webpack:
	webpack --mode production
