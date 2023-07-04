# IQRF Gateway Webapp

[![Build Status](https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/badges/master/build.svg)](https://gitlab.iqrf.org/open-source/iqrf-gateway-webapp/pipelines)
[![Apache License](https://img.shields.io/badge/license-APACHE2-blue.svg)](LICENSE)
[![API documentation](https://img.shields.io/badge/docs-api-brightgreen.svg)](https://apidocs.iqrf.org/iqrf-gateway-webapp/)
[![User documentation](https://img.shields.io/badge/docs-user-blue.svg)](https://docs.iqrf.org/iqrf-gateway-webapp/)

This is web application for managing IQRF Gateway Daemon's configuration.

## Installation

See the installation guide [here](https://docs.iqrf.org/iqrf-gateway/webapp-install.html).

## Requirements

PHP 8.1 or higher. To check whether server configuration meets the minimum requirements for [Nette Framework](https://doc.nette.org/3.0/requirements).

### Web server

For Apache or Nginx, setup a virtual host to point to the `www/` directory of the project and you should be ready to go.

It is *CRITICAL* that whole `app/`, `log/` and `temp/` directories are not accessible directly via a web browser. See [security warning](https://nette.org/security-warning).

## License

This project is licensed under Apache License 2.0:

 > Copyright 2017-2023 IQRF Tech s.r.o.
 >
 > Copyright 2019-2023 MICRORISC s.r.o.
 >
 > Licensed under the Apache License, Version 2.0 (the "License");
 >
 > you may not use this file except in compliance with the License.
 >
 > You may obtain a copy of the License at
 >
 >     http://www.apache.org/licenses/LICENSE-2.0
 >
 > Unless required by applicable law or agreed to in writing, software
 >
 > distributed under the License is distributed on an "AS IS" BASIS,
 >
 > WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 >
 > See the License for the specific language governing permissions and
 >
 > limitations under the License.

 ## Acknowledgement

This project has been made possible with a government grant by means of [the Ministry of Industry and Trade of the Czech Republic](https://www.mpo.cz/) in [the TRIO program](https://starfos.tacr.cz/cs/project/FV40132).
