<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2018 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
declare(strict_types = 1);

namespace App\IqrfAppModule\Model;

/**
 * The exception that indicates that the action is aborted
 */
class AbortedException extends DpaErrorException {

}

/**
 * The exception that indicates bad request
 */
class BadRequestException extends DpaErrorException {

}

/**
 * The exception that indicates bad response
 */
class BadResponseException extends DpaErrorException {

}

/**
 * The exception that indicates the interface data is consumed by Custom DPA handler
 */
class CustomHandlerConsumedInterfaceDataException extends DpaErrorException {

}

/**
 * The exception that indicates DPA error
 */
class DpaErrorException extends \Exception {

}

/**
 * The exception that indicates empty JSON DPA or DPA response
 */
class EmptyResponseException extends \Exception {

}

/**
 * The exception that indicates DPA exclusive access is used
 */
class ExclusiveAccessException extends DpaErrorException {

}

/**
 * The exception that indicates general failure
 */
class GeneralFailureException extends DpaErrorException {

}

/**
 * The exception that indicates an incorrect address value when addressing memory type peripherals
 */
class IncorrectAddressException extends DpaErrorException {

}

/**
 * The exception that indicates an incorrect data
 */
class IncorrectDataException extends DpaErrorException {

}

/**
 * The exception that indicates an incorrect data length
 */
class IncorrectDataLengthException extends DpaErrorException {

}

/**
 * The exception that indicates an incorrect HWPID id used
 */
class IncorrectHwpidUsedException extends DpaErrorException {

}

/**
 * The exception that indicates an incorrect NADR
 */
class IncorrectNadrException extends DpaErrorException {

}

/**
 * The exception that incicates an incorrect PCMD
 */
class IncorrectPcmdException extends DpaErrorException {

}

/**
 * The exception that indicates an incorrect PNUM
 */
class IncorrectPnumException extends DpaErrorException {

}

/**
 * The exception indicates that the interface is busy
 */
class InterfaceBusyException extends DpaErrorException {

}

/**
 * The exception that indicates the interface error
 */
class InterfaceErrorException extends DpaErrorException {

}

/**
 * The exception that indicates taht the interface queue is full
 */
class InterfaceQueueFullException extends DpaErrorException {

}

/**
 * The exception that indicates invalid gateway operational mode
 */
class InvalidOperationModeException extends \Exception {

}

/**
 * The exception that indicates invalid RF channel type
 */
class InvalidRfChannelTypeException extends \Exception {

}

/**
 * The exception that indicates invalid RF LP timeout
 */
class InvalidRfLpTimeoutException extends \Exception {

}

/**
 * The exception that indicates invalid RF output power
 */
class InvalidRfOutputPowerException extends \Exception {

}

/**
 * The exception that indicates invalid RF signal filter
 */
class InvalidRfSignalFilterException extends \Exception {

}

/**
 * The exception that indicates a missing Custom DPA handler
 */
class MissingCustomDpaHandlerException extends DpaErrorException {

}

/**
 * The exception that indicates timeout
 */
class TimeoutException extends DpaErrorException {

}

/**
 * The exception that indicates unsupported input format
 */
class UnsupportedInputFormatException extends \Exception {

}

/**
 * The exception that indicates unsupported security type
 */
class UnsupportedSecurityTypeException extends \Exception {

}

/**
 * The exception that indicates DPA user error code
 */
class UserErrorException extends DpaErrorException {

}
