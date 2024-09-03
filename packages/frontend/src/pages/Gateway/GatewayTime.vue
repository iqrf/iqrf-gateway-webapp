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
	<div>
		<h1>{{ $t('gateway.datetime.title') }}</h1>
		<v-card v-if='time !== null'>
			<v-card-title>
				<h3>{{ $t('gateway.datetime.status') }}</h3>
				<v-spacer />
				<v-btn
					color='primary'
					small
					@click='getTime'
				>
					<v-icon>mdi-refresh</v-icon>
				</v-btn>
			</v-card-title>
			<v-card-text>
				<p style='font-size: 2em;'>
					{{ time.formattedTime }}
				</p>
				<p style='font-size: 1.5em;'>
					{{ time.formattedZone }}
				</p>
				<v-divider class='my-2' />
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<h4>{{ $t('gateway.datetime.form.title') }}</h4>
						<v-autocomplete
							v-model='timezone'
							:label='$t("gateway.datetime.form.zone")'
							:items='timezoneOptions'
							item-text='text'
							item-value='value'
							return-object
						/>
						<v-select
							v-model='timeSet'
							:label='$t("gateway.datetime.form.set")'
							:items='timeSetOptions'
						/>
						<div v-if='timeSet === TimeSetOptions.NTP'>
							<v-switch
								v-model='defaultServers'
								:label='$t("gateway.datetime.form.defaultServers")'
								inset
								dense
							/>
							<div v-if='!defaultServers'>
								<ValidationProvider
									v-for='(server, idx) of time.ntpServers'
									:key='idx'
									v-slot='{errors, touched, valid}'
									rules='required|server'
									:custom-messages='{
										required: $t("gateway.datetime.errors.serverMissing"),
										server: $t("gateway.datetime.errors.serverInvalid"),
									}'
								>
									<v-text-field
										v-model='time.ntpServers[idx]'
										:label='$t("gateway.datetime.form.server")'
										:success='touched ? valid : null'
										:error-messages='errors'
									>
										<template #append-outer>
											<v-btn
												v-if='time.ntpServers.length > 1'
												class='mr-1'
												color='error'
												small
												@click='removeServer(idx)'
											>
												<v-icon>mdi-delete-outline</v-icon>
											</v-btn>
											<v-btn
												v-if='idx === (time.ntpServers.length - 1)'
												color='success'
												small
												@click='addServer'
											>
												<v-icon>mdi-plus</v-icon>
											</v-btn>
										</template>
									</v-text-field>
								</ValidationProvider>
							</div>
						</div>
						<div v-else>
							<DateTimePicker
								ref='datetime'
								:datetime.sync='datetime'
								:from-browser='true'
							/>
						</div>
						<v-btn
							class='mr-1'
							color='primary'
							:disabled='invalid'
							@click='setTime'
						>
							{{ $t('forms.save') }}
						</v-btn>
						<v-btn
							v-if='timeSet === TimeSetOptions.MANUAL'
							color='primary'
							@click='setFromBrowser'
						>
							{{ $t('gateway.datetime.form.browserTime') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {TimeService} from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import {
	TimeConfig,
	TimeSet,
	Timezone
} from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import {AxiosError} from 'axios';
import {DateTime} from 'luxon';
import isFQDN from 'is-fqdn';
import ip from 'ip-regex';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

import DateTimePicker from '@/components/DateTimePicker.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {TimeSetOptions} from '@/enums/Gateway/Time';
import {ISelectItem} from '@/interfaces/Vuetify';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		DateTimePicker,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		TimeSetOptions,
	}),
	metaInfo: {
		title: 'gateway.datetime.title'
	}
})

/**
 * Gateway time component
 */
export default class GatewayTime extends Vue {
	/**
	 * Location and date loading
	 */
	private loading = false;

	/**
	 * @var {TimeConfig|null} time Gateway time and location information
	 */
	private time: TimeConfig|null = null;

	/**
	 * @var {ISelectItem} timezone Currently selected timezone
	 */
	private timezone: ISelectItem = {
		text: '',
		value: '',
	};

	/**
	 * @var {Array<ISelectItem>} timezoneOptions Array of available timezones at Gateway
	 */
	private timezoneOptions: ISelectItem[] = [];

	/**
	 * @constant {Array<ISelectItem>} timeSetOptions Array of time configuration options
	 */
	private timeSetOptions: ISelectItem[] = [
		{
			value: TimeSetOptions.NTP,
			text: this.$t('gateway.datetime.form.setOptions.ntp').toString(),
		},
		{
			value: TimeSetOptions.MANUAL,
			text: this.$t('gateway.datetime.form.setOptions.manual').toString(),
		},
	];

	/**
	 * @var {TimeSetOptions} timeSet Selected time set option
	 */
	private timeSet: TimeSetOptions = TimeSetOptions.NTP;

	/**
	 * @var {boolean} defaultServers Use default servers
	 */
	private defaultServers = true;

	/**
	 * @var {Date} datetime Datetime object
	 */
	private datetime: Date = new Date(0);

	/**
	 * @property {TimeService} service Time service
	 */
	private readonly service: TimeService = useApiClient().getGatewayServices().getTimeService();

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('server', (addr: string) => {
			return ip.v4({exact: true}).test(addr) || ip.v6({exact: true}).test(addr) || isFQDN(addr);
		});
	}

	/**
	 * Retrieves gateway time and available timezones
	 */
	mounted(): void {
		this.getTime();
	}

	/**
	 * Retrieves current gateway date, time and timezone
	 */
	public getTime(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		this.loading = true;
		return this.service.getTime()
			.then((response: TimeConfig) => {
				this.time = response;
				if (this.time.ntpSync) {
					if (this.time.ntpServers.length === 0) {
						this.time.ntpServers.push('');
					}
				} else {
					this.timeSet = TimeSetOptions.MANUAL;
				}
				this.timezone = {
					text: this.time.formattedZone,
					value: this.time.zoneName,
				};
				this.datetime = DateTime.fromSeconds(this.time.utcTimestamp, {zone: this.time.zoneName}).toJSDate();
				this.getTimezones();
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'gateway.datetime.messages.timeGetFailed');
			});
	}

	/**
	 * Retrieve list of available timezones
	 */
	private getTimezones(): void {
		this.service.listTimezones()
			.then((response: Timezone[]) => {
				this.$store.commit('spinner/HIDE');
				this.parseTimezones(response);
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'gateway.datetime.messages.timezoneGetFailed');
			});
	}

	/**
	 * Reads timezones information and creates select options
	 * @param {Array<Timezone>} timezones Array of timezones information from REST API
	 */
	private parseTimezones(timezones: Array<Timezone>): void {
		const timezoneArray: ISelectItem[] = [];
		for (const timezone of timezones) {
			timezoneArray.push({
				value: timezone.name,
				text: timezone.name + ' (' + timezone.code + ', ' + timezone.offset + ')',
			});
		}
		this.timezoneOptions = timezoneArray;
	}

	/**
	 * Builds time set request parameters and sets time configuration
	 */
	private setTime(): void {
		const params: TimeSet = {
			ntpSync: this.timeSet === TimeSetOptions.NTP,
		};
		if (this.timezone.value !== this.time?.zoneName) {
			params.zoneName = (this.timezone.value as string);
		}
		if (params.ntpSync) {
			params.ntpServers = this.defaultServers ? [] : this.time?.ntpServers;
		} else {
			const luxonDate = DateTime.fromJSDate(this.datetime, {zone: (this.timezone.value as string)});
			params.datetime = luxonDate.toISO();
		}
		this.$store.commit('spinner/SHOW');
		this.service.updateTime(params)
			.then(() => {
				this.getTime().then(() => {
					this.$toast.success(
						this.$t('gateway.datetime.messages.timeSetSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'gateway.datetime.messages.timeSetFailed');
			});
	}

	/**
	 * Sets datetime from browser with timezone
	 */
	private setFromBrowser(): void {
		this.datetime = DateTime.fromJSDate(new Date()).toJSDate();
	}

	/**
	 * Adds another server entry
	 */
	private addServer(): void {
		if (this.time === null) {
			return;
		}
		this.time.ntpServers.push('');
	}

	/**
	 * Removes a server entry specified by index
	 * @param {number} idx Index of server to remove
	 */
	private removeServer(idx: number): void {
		if (this.time === null) {
			return;
		}
		this.time.ntpServers.splice(idx, 1);
	}
}
</script>
