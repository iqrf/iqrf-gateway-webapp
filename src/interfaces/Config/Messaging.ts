/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

import {ComponentInstanceBase} from '../daemonComponent';
import {RequiredInterface} from '../requiredInterfaces';

/**
 * MqMessaging instance interface
 */
export interface IMqInstance extends ComponentInstanceBase {
	/**
	 * Accept asynchronous messages
	 */
	acceptAsyncMsg: boolean;

	/**
	 * Server message queue
	 */
	LocalMqName: string;

	/**
	 * Client message queue
	 */
	RemoteMqName: string;
}

/**
 * MqttMessaging instance interface
 */
export interface IMqttInstance extends ComponentInstanceBase {
	acceptAsyncMsg: boolean;
	BrokerAddr: string;
	ClientId: string;
	ConnectTimeout: number;
	EnableServerCertAuth: boolean;
	EnabledCipherSuites: string;
	EnabledSSL: boolean;
	KeepAliveInterval: number;
	KeyStore: string;
	MaxReconnect: number;
	MinReconnect: number;
	Password: string;
	Persistence: number;
	PrivateKey: string;
	PrivateKeyPassword: string;
	Qos: number;
	TopicRequest: string;
	TopicResponse: string;
	TrustStore: string;
	User: string;
}

/**
 * WebsocketInterface interface
 */
export interface IWsInterface {
	/**
	 * Messaging instance
	 */
	messaging: IWsMessaging;

	/**
	 * Service instance
	 */
	service: IWsService;
}

/**
 * WebsocketMessaging interface
 */
export interface IWsMessaging extends ComponentInstanceBase {
	/**
	 * Accept asynchronous messages
	 */
	acceptAsyncMsg: boolean;

	/**
	 * Required interfaces
	 */
	RequiredInterfaces: Array<RequiredInterface>;
}

/**
 * WebsocketService interface
 */
export interface IWsService extends ComponentInstanceBase {
	/**
	 * Websocket port
	 */
	WebsocketPort: number;

	/**
	 * Accept connections only from localhost?
	 */
	acceptOnlyLocalhost: boolean;

	/**
	 * Use TLS?
	 */
	tlsEnabled?: boolean;

	/**
	 * TLS operating mode
	 */
	tlsMode?: string;

	/**
	 * Path to certificate for TLS
	 */
	certificate?: string;

	/**
	 * Path to private key for TLS
	 */
	privateKey?: string;
}

/**
 * UdpMessaging instance interface
 */
export interface IUdpInstance extends ComponentInstanceBase {
	/**
	 * Local service port
	 */
	LocalPort: number;

	/**
	 * Remote service port
	 */
	RemotePort: number;
}

/**
 * Messaging instance interface for modal windows
 */
export interface ModalInstance {
	messaging: string;
	service: string;
}
