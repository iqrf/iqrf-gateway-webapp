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
	<v-card>
		<v-card-title>
			{{ $t('gateway.ntp.title') }}
		</v-card-title>
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<form @submit.prevent='saveConfig'>
					<v-switch
						v-model='useCustomServers'
						:label='$t("gateway.ntp.form.setServer")'
						inset
					/>
					<div v-if='useCustomServers'>
						<div
							v-for='(key, idx) of pools'
							:key='idx'
							class='form-group'
						>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|server'
								:custom-messages='{
									required: $t("gateway.ntp.errors.serverMissing"),
									server: $t("gateway.ntp.errors.serverInvalid"),
								}'
							>
								<v-text-field
									v-model='pools[idx]'
									:label='$t("gateway.ntp.form.server")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<v-btn
								v-if='pools.length > 1'
								color='error'
								@click='removeServer(idx)'
							>
								{{ $t('gateway.ntp.form.remove') }}
							</v-btn> <v-btn
								v-if='idx === (pools.length -1)'
								color='success'
								@click='addServer'
							>
								{{ $t('gateway.ntp.form.add') }}
							</v-btn>
						</div>
					</div>
					<v-btn
						color='primary'
						:disabled='invalid'
						type='submit'
					>
						{{ $t('forms.save') }}
					</v-btn> <v-btn
						color='info'
						@click='syncTime'
					>
						Sync time
					</v-btn>
				</form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';

import GatewayService from '@/services/GatewayService';
import ip from 'ip-regex';
import isFQDN from 'is-fqdn';

import {AxiosError} from 'axios';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * NTP configuration component
 */
export default class NtpConfig extends Vue {

	/**
	 * NTP configuration
	 */
	private pools: Array<string> = [''];

	/**
	 * @var {boolean} useCustomServers Controls whether server fields are rendered
	 */
	private useCustomServers = false;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('server', (addr: string) => {
			return ip.v4({exact: true}).test(addr) || ip.v6({exact: true}).test(addr) || isFQDN(addr);
		});
		extend('required', required);
	}

	/**
	 * Fetches configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of local NTP reference clock servers
	 */
	private getConfig(): Promise<void> {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return GatewayService.getNtp()
			.then((pools: Array<string>) => {
				if (pools.length === 0) {
					this.useCustomServers = false;
				} else {
					this.useCustomServers = true;
					this.pools = pools;
				}
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.ntp.messages.fetchFailed'));
	}

	/**
	 * Stores configuration
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		const config = this.useCustomServers ? this.pools : [];
		GatewayService.setNtp(config)
			.then(() => {
				this.getConfig().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('gateway.ntp.messages.saveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.ntp.messages.saveFailed'));
	}

	/**
	 * Attempts to sync time and refresh gateway clock
	 */
	private syncTime(): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('gateway.ntp.messages.syncProgress').toString());
		GatewayService.ntpSync()
			.then(() => {
				this.$emit('refresh-time');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.ntp.messages.syncFailed'));
	}

	/**
	 * Adds another server entry
	 */
	private addServer(): void {
		this.pools.push('');
	}

	/**
	 * Removes a server entry specified by index
	 * @param {number} idx Index of server to remove
	 */
	private removeServer(idx: number): void {
		this.pools.splice(idx, 1);
	}

}
</script>
