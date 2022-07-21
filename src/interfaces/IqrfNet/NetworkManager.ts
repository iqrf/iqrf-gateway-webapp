/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

import {OtaUploadAction} from '@/iqrfNet/otaUploadAction';

/**
 * OTA upload parameters interface
 */
export interface IOtaUploadParams {
    /**
     * Target device address
     */
    address: number

    /**
     * HWPID
     */
    hwpid: number

    /**
     * OTA upload action
     */
    loadingAction: OtaUploadAction

    /**
     * Filename
     */
    file: string

    /**
     * Start memory address to store data
     */
    startMemAddr: number

    /**
     * Upload EEPROM data
     */
    uploadEeprom: boolean

    /**
     * Upload EEEPROM data
     */
    uploadEeeprom: boolean

}

/**
 * OTA upload action result interface
 */
export interface IOtaUploadResult {
    /**
     * Device address
     */
    address: number

    /**
     * Action result
     */
    result: boolean

    /**
     * Is device online?
     */
    online?: boolean

    /**
     * Is device compatible?
     */
    compatible?: boolean

    /**
     * Was action successful?
     */
    success?: boolean
}
