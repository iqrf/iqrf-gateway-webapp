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
	<div>
		<h1>{{ $t('gateway.power.title') }}</h1>
		<CCard body-wrapper>
			<CButton
				color='danger'
				@click='powerOff()'
			>
				<CIcon :content='icons.off' />
				{{ $t('gateway.power.powerOff') }}
			</CButton> <CButton
				color='primary'
				@click='reboot()'
			>
				<CIcon :content='icons.reboot' />
				{{ $t('gateway.power.reboot') }}
			</CButton>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CIcon} from '@coreui/vue/src';
import {cilPowerStandby, cilReload} from '@coreui/icons';
import GatewayService from '../../services/GatewayService';
import { MetaInfo } from 'vue-meta';
import { AxiosResponse } from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CIcon,
	},
	metaInfo(): MetaInfo {
		return {
			title: 'gateway.power.title',
		};
	}
})

/**
 * Power control component
 */
export default class PowerControl extends Vue {
	/**
	 * @constant {Record<string, Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Record<string, Array<string>> = {
		off: cilPowerStandby,
		reboot: cilReload
	};

	/**
	 * Performs power off
	 */
	private powerOff(): void {
		GatewayService.performPowerOff()
			.then((response: AxiosResponse) =>
				this.$toast.info(
					this.$t('gateway.power.messages.powerOffSuccess', {time: this.parseActionTime(response.data.timestamp)}).toString()
				)
			);
	}

	/**
	 * Performs reboot
	 */
	private reboot(): void {
		GatewayService.performReboot()
			.then((response: AxiosResponse) =>
				this.$toast.info(
					this.$t('gateway.power.messages.rebootSuccess', {time: this.parseActionTime(response.data.timestamp)}).toString()
				)
			);
	}

	/**
	 * Converts timestamp to time string
	 */
	private parseActionTime(timestamp: number): string {
		return new Date(timestamp * 1000).toLocaleTimeString();
	}
}
</script>
