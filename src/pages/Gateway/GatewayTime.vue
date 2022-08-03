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
		<h1>{{ $t('gateway.datetime.title') }}</h1>
		<v-card v-if='time !== null'>
			<v-card-title>
				<span class='form-section-title mr-2'>{{ $t("gateway.datetime.status") }}</span>
				<v-btn
					color='primary'
					small
					@click='getTime'
				>
					<v-icon>
						mdi-refresh
					</v-icon>
				</v-btn>
			</v-card-title>
			<v-card-text>
				<v-row>
					<v-col>
						<p style='font-size: 2em;'>
							{{ time.formattedTime }}
						</p>
						<p style='font-size: 1.5em;'>
							{{ time.formattedZone }}
						</p>
					</v-col>
				</v-row>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<span class='form-section-title'>{{ $t('gateway.datetime.location.title') }}</span>
						<v-autocomplete
							v-model='timezone'
							item-text='text'
							item-value='value'
							:label='$t("gateway.datetime.location.zone")'
							:items='timezoneOptions'
							:return-object='true'
						/>
						<span class='form-section-title'>{{ $t('gateway.datetime.time.title') }}</span>
						<v-select
							v-model='timeSet'
							:label='$t("gateway.datetime.time.set")'
							:items='timeSetOptions'
						/>
						<div v-show='timeSet === TimeSetOptions.NTP'>
							<v-switch
								v-model='defaultServers'
								:label='$t("gateway.datetime.time.defaultServers")'
								dense
								inset
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
										:label='$t("gateway.datetime.time.server")'
										:success='touched ? valid : null'
										:error-messages='errors'
									>
										<template #append-outer>
											<v-btn
												v-if='time.ntpServers.length > 1'
												color='error'
												small
												@click='removeServer(idx)'
											>
												<v-icon>
													mdi-delete-outline
												</v-icon>
											</v-btn>
											<v-btn
												v-if='idx === (time.ntpServers.length - 1)'
												color='success'
												small
												@click='addServer'
											>
												<v-icon>
													mdi-plus
												</v-icon>
											</v-btn>
										</template>
									</v-text-field>
								</ValidationProvider>
							</div>
						</div>
						<div v-if='timeSet === TimeSetOptions.MANUAL'>
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
							{{ $t('gateway.datetime.time.browserTime') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import DateTimePicker from '@/components/DateTimePicker.vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import ip from 'ip-regex';
import isFQDN from 'is-fqdn';
import {DateTime} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import {TimeSetOptions} from '@/enums/Gateway/Time';

import TimeService from '@/services/TimeService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/coreui';
import {ITime, ITimezone, ITimeSet} from '@/interfaces/gatewayTime';

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
	private loading = false;
	/**
	 * @var {ITime|null} time Gateway time and locaiton information
	 */
	private time: ITime|null = null;

	/**
	 * @var {IOption} timezone Currently selected timezone
	 */
	private timezone: IOption = {
		text: '',
		value: '',
	};

	/**
	 * @var {Array<IOption>} timezoneOptions Array of available timezones at Gateway
	 */
	private timezoneOptions: Array<IOption> = [];

	/**
	 * @constant {Array<IOption>} timeSetOptions Array of time configuration options
	 */
	private timeSetOptions: Array<IOption> = [
		{
			value: TimeSetOptions.NTP,
			text: this.$t('gateway.datetime.time.setOptions.ntp').toString(),
		},
		{
			value: TimeSetOptions.MANUAL,
			text: this.$t('gateway.datetime.time.setOptions.manual').toString(),
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
		return TimeService.getTime()
			.then((response: AxiosResponse) => {
				this.time = response.data;
				if (this.time === null) {
					return;
				}
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
		TimeService.getTimezones()
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.parseTimezones(response.data);
				if (this.timeSet === TimeSetOptions.MANUAL) {
					(this.$refs.datetime as DateTimePicker).setFromDateTime(
						DateTime.fromJSDate(this.datetime, {zone: (this.timezone.value as string)})
					);
				}
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'gateway.datetime.messages.timezoneGetFailed');
			});
	}

	/**
	 * Reads timezones information and creates select options
	 * @param {Array<ITimezone>} timezones Array of timezones information from REST API
	 */
	private parseTimezones(timezones: Array<ITimezone>): void {
		const timezoneArray: Array<IOption> = [];
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
		const params: ITimeSet = {
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
		TimeService.setTime(params)
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
		const date = DateTime.fromJSDate(new Date(), {zone: (this.timezone.value as string)});
		(this.$refs.datetime as DateTimePicker).setFromDateTime(date);
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
