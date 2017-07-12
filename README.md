# iqrf-daemon-webapp

[![Build Status](https://travis-ci.org/iqrfsdk/iqrf-daemon-webapp.svg?branch=master)](https://travis-ci.org/iqrfsdk/iqrf-daemon-webapp)
[![Code Coverage](https://codecov.io/gh/iqrfsdk/iqrf-daemon-webapp/branch/master/graph/badge.svg)](https://codecov.io/gh/iqrfsdk/iqrf-daemon-webapp)
[![Downloads this Month](https://img.shields.io/packagist/dm/iqrfsdk/iqrf-daemon-webapp.svg)](https://packagist.org/packages/iqrfsdk/iqrf-daemon-webapp)
[![Apache License](https://img.shields.io/badge/license-APACHE2-blue.svg)](LICENSE)

This is web application for iqrf-daemon configuration.

## Installation

The best way to install this project is using Composer. If you don't have Composer yet, download it following [the instructions](https://doc.nette.org/composer). Then use command:

### Development version

```bash
git clone https://github.com/iqrfsdk/iqrf-daemon-webapp.git
cd iqrf-daemon-webapp/
composer install
```

Make directories `temp/` and `log/` writable.

### Stable version
```bash
composer create-project iqrfsdk/iqrf-daemon-webapp
```

## Web Server Setup

### Development built-in PHP server

The simplest way to get started is to start the built-in PHP server in the root directory of this project:

```bash
php -S localhost:8000 -t www/
```

Then visit `http://localhost:8000` in your browser to see the welcome page.

### Nginx (automatic instalation)

If you use Debian 8 (jessie), Debian 9 (stretch) or Ubuntu 16.04 (xenial), you can use the installer which is located in install directory of this project:

**Debian 8 (jessie)**
```bash
python3 install.py -d debian -v 8
```

**Debian 9 (stretch)**
```bash
python3 install.py -d debian -v 9
```

**Ubuntu 16.04 (xenial)**
```bash
python3 install.py -d ubuntu -v 16.04
```

Then visit `http://localhost` in your browser to see the welcome page.

### Apache2 or Nginx

For Apache or Nginx, setup a virtual host to point to the `www/` directory of the project and you should be ready to go.

It is *CRITICAL* that whole `app/`, `log/` and `temp/` directories are not accessible directly via a web browser. See [security warning](https://nette.org/security-warning).

## Requirements

PHP 5.6 or higher. To check whether server configuration meets the minimum requirements for [Nette Framework](https://doc.nette.org/2.4/requirements).

## License

This library is licensed under Apache License 2.0:

 > Copyright 2017 MICRORISC s.r.o.
 >
 > Licensed under the Apache License, Version 2.0 (the "License");
 > you may not use this file except in compliance with the License.
 > You may obtain a copy of the License at
 >
 >     http://www.apache.org/licenses/LICENSE-2.0
 >
 > Unless required by applicable law or agreed to in writing, software
 > distributed under the License is distributed on an "AS IS" BASIS,
 > WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 > See the License for the specific language governing permissions and
 > limitations under the License.
