<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div />
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm} from '@coreui/vue/src';

import {extendedErrorToast} from '../../helpers/errorToast';
import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '../../interfaces/coreui';
import {MetaInfo} from 'vue-meta';
import {NetworkInterface} from '../../interfaces/gatewayInfo';
import NetworkConnectionService from '../../services/NetworkConnectionService';
import { IConnection } from '../../interfaces/network';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as MobileConnectionForm).pageTitle
		};
	}
})

/**
 * Mobile connection form
 */
export default class MobileConnectionForm extends Vue {

	/**
	 * @var {IConnection} connection Connection configuration
	 */
	private connection: IConnection = {
		autoConnect: {
			enabled: true,
			priority: 0,
			retries: -1,
		},
		name: '',
		type: 'gsm',
		ipv4: {
			addresses: [],
			dns: [],
			gateway: '',
			method: 'auto',
		},
		ipv6: {
			addresses: [],
			dns: [],
			gateway: '',
			method: 'ignore',
		}
	}

	/**
	 * @var {Array<IOption>} interfaceOptions Available GSM interfaces
	 */
	private interfaceOptions: Array<IOption> = []

	/**
	 * @property {string} uuid GSM connection ID
	 */
	@Prop({required: false, default: null}) uuid!: string

	/**
	 * Computes page title from url
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$t(
			'network.mobile.' + (this.$route.path.includes('/mobile/add') ? 'add' : 'edit')
		).toString();
	}

	/**
	 *
	 */
	mounted(): void {
		this.getInterfaces();
	}

	/**
	 * Retrieves GSM interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.GSM)
			.then((response: AxiosResponse) => {
				let interfaces: Array<IOption> = [];
				response.data.forEach((item: NetworkInterface) => {
					interfaces.push({label: item.name, value: item.name});
				});
				this.interfaceOptions = interfaces;
				this.$store.commit('spinner/HIDE');
				if (this.uuid !== null) {
					this.getConnection();
				}
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed');
				this.$router.push('/network/mobile');
			});
	}

	/**
	 * Retrieves GSM connection
	 */
	private getConnection(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.get(this.uuid)
			.then((response: AxiosResponse) => {
				console.warn(response.data);
				this.connection = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'network.connection.messages.fetchFailed',
					{connection: this.uuid}
				);
				this.$router.push('/network/mobile');
			});
	}
}
</script>
