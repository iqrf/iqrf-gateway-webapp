<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<v-card>
			<v-card-text>
				<v-btn
					color='error'
					@click='powerOff()'
				>
					<v-icon>mdi-power</v-icon>
					{{ $t('gateway.power.powerOff') }}
				</v-btn> <v-btn
					color='primary'
					@click='reboot()'
				>
					<v-icon>mdi-reload</v-icon>
					{{ $t('gateway.power.reboot') }}
				</v-btn>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import { PowerService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { PowerActionResponse } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { AxiosError } from 'axios';
import { MetaInfo } from 'vue-meta';
import { Component, Vue } from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	metaInfo(): MetaInfo {
		return {
			title: this.$t('gateway.power.title').toString(),
		};
	}
})

/**
 * Power control component
 */
export default class PowerControl extends Vue {

	/**
	 * @property {PowerService} service Power service
	 */
	private service: PowerService = useApiClient().getGatewayServices().getPowerService();

	/**
	 * Performs power off
	 */
	private powerOff(): void {
		this.service.powerOff()
			.then((response: PowerActionResponse) =>
				this.$toast.info(
					this.$t('gateway.power.messages.powerOffSuccess', {
						time: response.timestamp.toJSDate().toLocaleTimeString()
					}).toString()
				)
			).catch((error: AxiosError) =>
				extendedErrorToast(error, 'gateway.power.messages.powerOffFailed')
			);
	}

	/**
	 * Performs reboot
	 */
	private reboot(): void {
		this.service.reboot()
			.then((response: PowerActionResponse) =>
				this.$toast.info(
					this.$t('gateway.power.messages.rebootSuccess', {
						time: response.timestamp.toJSDate().toLocaleTimeString()
					}).toString()
				)
			).catch((error: AxiosError) =>
				extendedErrorToast(error, 'gateway.power.messages.rebootFailed')
			);
	}

}
</script>
