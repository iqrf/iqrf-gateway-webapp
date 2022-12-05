<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
	<CCard>
		<CCardHeader>
			{{ $t('gateway.ntp.title') }}
		</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
					<div class='form-group'>
						<b>
							<label>
								{{ $t('gateway.ntp.form.setServer') }}
							</label>
						</b><br>
						<CSwitch
							:checked.sync='useCustomServers'
							size='lg'
							shape='pill'
							color='primary'
							label-on='ON'
							label-off='OFF'
						/>
					</div>
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
									required: "gateway.ntp.errors.serverMissing",
									server: "gateway.ntp.errors.serverInvalid"
								}'
							>
								<CInput
									v-model='pools[idx]'
									:label='$t("gateway.ntp.form.server")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CButton
								v-if='pools.length > 1'
								color='danger'
								@click='removeServer(idx)'
							>
								{{ $t('gateway.ntp.form.remove') }}
							</CButton> <CButton
								v-if='idx === (pools.length -1)'
								color='success'
								@click='addServer'
							>
								{{ $t('gateway.ntp.form.add') }}
							</CButton>
						</div>
					</div>
					<CButton
						color='primary'
						:disabled='invalid'
						@click='saveConfig'
					>
						{{ $t('forms.save') }}
					</CButton> <CButton
						color='info'
						@click='syncTime'
					>
						Sync time
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CForm, CInput, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';

import GatewayService from '@/services/GatewayService';
import ip from 'ip-regex';
import isFQDN from 'is-fqdn';

import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CSwitch,
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
			return ip.v4({exact: true}).test(addr) || isFQDN(addr);
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
			.then((response: AxiosResponse) => {
				const pools: Array<string> = response.data;
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
