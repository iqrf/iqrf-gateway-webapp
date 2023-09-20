# Copyright 2017-2023 IQRF Tech s.r.o.
# Copyright 2019-2023 MICRORISC s.r.o.
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
DB_DIR=${DESTDIR}/var/lib/iqrf-gateway-webapp
LOG_DIR=${DESTDIR}/var/log/iqrf-gateway-webapp
VENDOR_DIR=${DATA_DIR}/vendor
SBIN_DIR=${DESTDIR}/usr/sbin
SYSTEMD_DIR=${DESTDIR}/lib/systemd/system

CC_IGNORE=-i "coverage.*" -i "docs/" -i "tests/temp/" -i "www/dist/" -i ".vscode/" -i "tests/iqrf-gateway-webapp.postman_collection.json" -i "tests/data/gatewayInfo/syntax_error/iqrf-gateway.json"

WEBAPP_USER ?= www-data
WEBAPP_GROUP ?= www-data

.PHONY: build clean coverage cc fix-cc cs deb-package deps qa install lint phpstan rector reset-db test

build:
	$(COMPOSER) install --no-dev
	npm --prefix packages/frontend/ install --legacy-peer-deps
	npm --prefix packages/frontend/ run build
	cp -ru packages/frontend/dist www/

all: qa phpstan cc test

clean:
	rm -rf log/*.html log/*.log temp/cache/ tests/tmp/

cache-purge:
	rm -rf temp/cache/

coverage: deps
	vendor/bin/tester -c ./tests/php-coverage.ini --coverage ./coverage.html --coverage-src ./app ./tests

cc: temp/code-checker
	php temp/code-checker/code-checker -l --no-progress --strict-types $(CC_IGNORE)

fix-cc: temp/code-checker
	php temp/code-checker/code-checker -f -l --no-progress --strict-types $(CC_IGNORE)

cs: deps
	vendor/bin/codesniffer --runtime-set php_version 80100 app bin tests

deb-package:
	debuild -b -uc -us

deps:
	composer install

qa: cs

install:
	install -d -o $(WEBAPP_USER) $(CACHE_DIR)
	install -d -o $(WEBAPP_USER) $(CONFIG_DIR)
	cp app/config/* $(CONFIG_DIR)
	install -d $(CONFIG_DIR)/certs
	install -d $(DATA_DIR)
	cp -r api/ $(DATA_DIR)
	cp -r app/ $(DATA_DIR)
	rm -rf $(DATA_DIR)/app/config/
	cp -r bin/ $(DATA_DIR)
	cp -r db/ $(DATA_DIR)
	cp -r iqrf/ $(DATA_DIR)
	cp version.json $(DATA_DIR)
	cp -r vendor/ $(DATA_DIR)
	cp -r www/ $(DATA_DIR)
	install -d $(SYSTEMD_DIR)
	install -m 0644 install/config/systemd/* $(SYSTEMD_DIR)
	install -d $(CONFIG_DIR)/nginx
	install -m 0644 install/config/nginx/* $(CONFIG_DIR)/nginx
	install -d -o $(WEBAPP_USER) $(LOG_DIR)
	install -d -o $(WEBAPP_USER) $(DB_DIR)
	# Fix ownership
	chown -R $(WEBAPP_USER):$(WEBAPP_GROUP) $(CACHE_DIR)
	chown -R $(WEBAPP_USER):$(WEBAPP_GROUP) $(CONFIG_DIR)
	chown -R $(WEBAPP_USER):$(WEBAPP_GROUP) $(DB_DIR)
	chown -R $(WEBAPP_USER):$(WEBAPP_GROUP) $(LOG_DIR)
	# Delete documentation
	find $(VENDOR_DIR) -type f -name "AUTHORS*" -delete
	find $(VENDOR_DIR) -type f -name "CHANGELOG*" -delete
	find $(VENDOR_DIR) -type f -name "CONTRIBUTING.md" -delete
	find $(VENDOR_DIR) -type f -name "contributing.md" -delete
	find $(VENDOR_DIR) -type f -name "COPYRIGHT*" -delete
	find $(VENDOR_DIR) -type f -name "LICENSE*" -delete
	find $(VENDOR_DIR) -type f -name "license.md" -delete
	find $(VENDOR_DIR) -type f -name "README*" -delete
	find $(VENDOR_DIR) -type f -name "readme.md" -delete
	find $(VENDOR_DIR) -type f -name "SECURITY.md" -delete
	find $(VENDOR_DIR) -type f -name "STABILITY.md" -delete
	find $(VENDOR_DIR) -type f -name "UPGRADE*.md" -delete
	find $(VENDOR_DIR) -type f -name "UPGRADING.md" -delete
	find $(VENDOR_DIR) -type d -regextype sed -regex ".*/\(\.\)\?[Dd]oc\(s\)\?" -print0 | xargs -0 rm -rf
	# Delete composer files
	find $(VENDOR_DIR) -type f -name "composer.json" -delete
	# Delete bower files
	find $(VENDOR_DIR) -type f -name "bower.json" -delete
	# Delete NPM files
	find $(VENDOR_DIR) -type f -name ".npmignore" -delete
	find $(VENDOR_DIR) -type f -name "package.json" -delete
	# Delete ESLint files
	find $(VENDOR_DIR) -type f -name ".eslintrc.js" -delete
	# Delete git files
	find $(VENDOR_DIR) -type d -name ".git" -print0 | xargs -0 rm -rf
	find $(VENDOR_DIR) -type f -name ".gitattributes" -delete
	find $(VENDOR_DIR) -type f -name ".gitignore" -delete
	find $(VENDOR_DIR) -type f -name ".gitmodules" -delete
	# Delete CI files
	find $(VENDOR_DIR) -type f -regex ".*/\(appveyor\|\.gitlab-ci\|\.scrutinizer\|\.travis\)\.yml" -delete
	# Delete PHP Coding Standards Fixer files
	find $(VENDOR_DIR) -type f -regex ".*/\.php_cs\(\.dist\)?" -delete
	# Delete PHPStan files
	find $(VENDOR_DIR) -type f -name "phpstan.neon" -delete
	# Delete PHP_CodeSniffer files
	find $(VENDOR_DIR) -type f -name "ruleset.xml" -delete
	# Delete PHPUnit files
	find $(VENDOR_DIR) -type f -regex ".*/phpunit\.xml\(\.dist\)?" -delete
	# Delete examples
	find $(VENDOR_DIR) -type d -name "demo" -print0 | xargs -0 rm -rf
	find $(VENDOR_DIR) -type d -regex ".*/[Ee]xample\(s\)?/*" -print0 | xargs -0 rm -rf
	# Delete tests
	find $(VENDOR_DIR) -type d -regex ".*/[Tt]est\(s\)?/*" -print0 | xargs -0 rm -rf
	# Delete binaries
	find $(VENDOR_DIR) -type d -name "bin" -print0 | xargs -0 rm -rf
	find $(VENDOR_DIR) -type d -name "tools" -print0 | xargs -0 rm -rf
	find $(VENDOR_DIR) -type f -name "*.sh" -delete
	find $(VENDOR_DIR) -type f -name "Makefile" -delete
	find $(VENDOR_DIR) -type f -print0 | xargs -0 chmod -x
	# Delete empty directories
	find $(VENDOR_DIR) -type d -empty -delete
	# Patch files
	patch $(DATA_DIR)/app/Kernel.php install/patches/kernel-fix-dir-paths.patch
	patch $(CONFIG_DIR)/config.neon install/patches/nettrine-fix-db-path.patch
	patch $(CONFIG_DIR)/config.neon install/patches/config-fix-log-path.patch
	patch $(DATA_DIR)/app/GatewayModule/Models/DiagnosticsManager.php install/patches/diagnostics-fix-dir-path.patch

phpstan: deps
	NETTE_TESTER_RUNNER=1 php vendor/bin/phpstan analyse -c phpstan.neon

rector: deps
	NETTE_TESTER_RUNNER=1 vendor/bin/rector process --dry-run

reset-db:
	rm -f app/config/database.db
	bin/manager database:create
	bin/manager migrations:migrate --no-interaction
	bin/manager doctrine:fixtures:load --append --no-interaction
	bin/manager iqrf-os:import-patches

run:
	php -S [::]:8080 -t www/

temp/code-checker:
	composer create-project nette/code-checker temp/code-checker --no-interaction

test: deps
	vendor/bin/tester -p phpdbg -c ./tests/php.ini ./tests
