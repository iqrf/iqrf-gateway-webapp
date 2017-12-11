# iqrf-daemon-webapp

[![Build Status](https://travis-ci.org/iqrfsdk/iqrf-daemon-webapp.svg?branch=master)](https://travis-ci.org/iqrfsdk/iqrf-daemon-webapp)
[![Code Coverage](https://codecov.io/gh/iqrfsdk/iqrf-daemon-webapp/branch/master/graph/badge.svg)](https://codecov.io/gh/iqrfsdk/iqrf-daemon-webapp)
[![Downloads this Month](https://img.shields.io/packagist/dm/iqrfsdk/iqrf-daemon-webapp.svg)](https://packagist.org/packages/iqrfsdk/iqrf-daemon-webapp)
[![Join the chat at https://gitter.im/iqrfsdk/iqrf-daemon-webapp](https://badges.gitter.im/iqrfsdk/iqrf-daemon-webapp.svg)](https://gitter.im/iqrfsdk/iqrf-daemon-webapp)
[![Apache License](https://img.shields.io/badge/license-APACHE2-blue.svg)](LICENSE)

This is web application for iqrf-daemon configuration.

## Installation

See the installation guide [here](INSTALL.md).

### Default login credentials

Default login credentials:
- username: `admin`
- password: `iqrf`

You can change it in the [configuration file](app/config/config.neon).

## Requirements

PHP 7.0 or higher. To check whether server configuration meets the minimum requirements for [Nette Framework](https://doc.nette.org/2.4/requirements).

### Web server

For Apache or Nginx, setup a virtual host to point to the `www/` directory of the project and you should be ready to go.

It is *CRITICAL* that whole `app/`, `log/` and `temp/` directories are not accessible directly via a web browser. See [security warning](https://nette.org/security-warning).

## License

This library is licensed under Apache License 2.0:

 > Copyright 2017 MICRORISC s.r.o.
 > Copyright 2017 IQRF Tech s.r.o.
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
