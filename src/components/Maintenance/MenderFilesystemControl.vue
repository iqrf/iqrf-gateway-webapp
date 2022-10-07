<template>
	<v-card>
		<v-card-text>
			<h4>{{ $t('maintenance.mender.update.control') }}</h4>
			<v-btn
				color='primary'
				@click='reboot()'
			>
				{{ $t('gateway.power.reboot') }}
			</v-btn> <v-btn
				v-if='$store.getters["features/isEnabled"]("remount")'
				color='primary'
				@click='remount("ro")'
			>
				{{ $t('maintenance.mender.update.remountRo') }}
			</v-btn> <v-btn
				v-if='$store.getters["features/isEnabled"]("remount")'
				color='primary'
				@click='remount("rw")'
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
@Component
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
