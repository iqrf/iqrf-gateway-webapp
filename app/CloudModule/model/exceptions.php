<?php

/**
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types=1);

namespace App\CloudModule\Model;

/**
 * The exception that indicates an invalid MS Azure IoT Hub connection string
 */
class InvalidConnectionString extends \Exception {

}

/**
 * The exception that indicates an invalid issuer of a Certificate
 */
class InvalidIssuerOfCertificate extends \Exception {

}

/**
 * The exception that indicates an invalid private key for a certificate
 */
class InvalidPrivateKeyForCertificate extends \Exception {

}
