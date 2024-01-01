<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-card>
		<v-card-text>
			<h4>{{ $t('maintenance.mender.update.control') }}</h4>
			<v-btn
				class='mr-1'
				color='primary'
				@click='reboot()'
			>
				{{ $t('gateway.power.reboot') }}
			</v-btn>
			<v-btn
				v-if='$store.getters["features/isEnabled"]("remount")'
				class='mr-1'
				color='primary'
				@click='remount(MountModes.RO)'
			>
				{{ $t('maintenance.mender.update.remountRo') }}
			</v-btn>
			<v-btn
				v-if='$store.getters["features/isEnabled"]("remount")'
				color='primary'
				@click='remount(MountModes.RW)'
			>
				{{ $t('maintenance.mender.update.remountRw') }}
			</v-btn>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>

import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {MountModes} from '@/enums/Maintenance/Mender';

import GatewayService from '@/services/GatewayService';
import MenderService from '@/services/MenderService';

import {AxiosError, AxiosResponse} from 'axios';

/**
 * Mender filesystem control component
 */
@Component({
	data: () => ({
		MountModes,
	}),
})
export default class MenderFilesystemControl extends Vue {
	/**
	 * Performs reboot after commit
	 */
	private reboot(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.performReboot()
			.then((response: AxiosResponse) => {
				const time = new Date(response.data.timestamp * 1000).toLocaleTimeString();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'gateway.power.messages.rebootSuccess',
						{time: time},
					).toString()
				);
			})
			.catch((error: AxiosError) =>extendedErrorToast(error, 'gateway.power.messages.rebootFailed'));
	}

	/**
	 * Remounts filesystem
	 * @param {MountModes} mode Filesystem mount mode
	 */
	private remount(mode: MountModes): void {
		const conf = {mode: mode};
		this.$store.commit('spinner/SHOW');
		MenderService.remount(conf)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('maintenance.mender.update.messages.remountSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'maintenance.mender.update.messages.remountFailed',
			));
	}
}
</script>
