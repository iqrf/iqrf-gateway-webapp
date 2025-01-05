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
		<h1>{{ $t('gateway.datetime.title') }}</h1>
		<CCard v-if='time !== null'>
			<CCardBody>
				<CCardTitle style='display: flex; justify-content: space-between;'>
					<h3>{{ $t('gateway.datetime.status') }}</h3>
					<CButton
						color='primary'
						@click='getTime'
					>
						<CIcon :content='cilReload' />
					</CButton>
				</CCardTitle>
				<p style='font-size: 2em;'>
					{{ time.formattedTime }}
				</p>
				<p style='font-size: 1.5em;'>
					{{ time.formattedZone }}
				</p>
				<hr>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<h4>{{ $t('gateway.datetime.form.title') }}</h4>
						<div class='form-group'>
							<label>
								<strong>{{ $t('gateway.datetime.form.zone') }}</strong>
							</label>
							<vSelect
								v-model='timezone'
								:options='timezoneOptions'
								label='label'
								:autoscroll='true'
								:clearable='false'
								:filterable='true'
							/>
						</div>
						<CSelect
							:value.sync='timeSet'
							:options='timeSetOptions'
							:label='$t("gateway.datetime.form.set")'
						/>
						<div v-if='timeSet === TimeSetOptions.NTP'>
							<div class='form-group'>
								<label>
									<strong>{{ $t('gateway.datetime.form.defaultServers') }}</strong>
								</label><br>
								<CSwitch
									:checked.sync='defaultServers'
									color='primary'
									shape='pill'
								/>
							</div>
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
									<CInput
										v-model='time.ntpServers[idx]'
										:label='$t("gateway.datetime.form.server")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='errors.join(" ")'
									>
										<template #prepend-content>
											<span
												v-if='idx === (time.ntpServers.length - 1)'
												class='text-success'
												@click='addServer'
											>
												<FontAwesomeIcon :icon='["far", "plus-square"]' size='xl' />
											</span>
										</template>
										<template #append-content>
											<span
												v-if='time.ntpServers.length > 1'
												class='text-danger'
												@click='removeServer(idx)'
											>
												<FontAwesomeIcon :icon='["far", "trash-alt"]' size='lg' />
											</span>
										</template>
									</CInput>
								</ValidationProvider>
							</div>
						</div>
						<div v-else>
							<div class='form-group'>
								<label>
									<strong>{{ $t('gateway.datetime.form.datetime') }}</strong>
								</label><br>
								<DatePicker
									v-model='datetime'
									type='datetime'
									value-type='date'
									input-class='form-control'
									:clearable='false'
								/>
							</div>
						</div>
						<CButton
							class='mr-1'
							color='primary'
							:disabled='invalid'
							@click='setTime'
						>
							{{ $t('forms.save') }}
						</CButton>
						<CButton
							v-if='timeSet === TimeSetOptions.MANUAL'
							color='primary'
							@click='setFromBrowser'
						>
							{{ $t('gateway.datetime.form.browserTime') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import vSelect from 'vue-select';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

import DatePicker from 'vue2-datepicker';

import {cilPlus, cilReload, cilTrash} from '@coreui/icons';

import ip from 'ip-regex';
import isFQDN from 'is-fqdn';
import {DateTime} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import {TimeSetOptions} from '@/enums/Gateway/Time';

import TimeService from '@/services/TimeService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {ITime, ITimezone, ITimeSet} from '@/interfaces/Gateway/Time';

@Component({
	components: {
		DatePicker,
		FontAwesomeIcon,
		ValidationObserver,
		ValidationProvider,
		vSelect,
	},
	data: () => ({
		cilPlus,
		cilReload,
		cilTrash,
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
	 * @var {ITime|null} time Gateway time and location information
	 */
	private time: ITime|null = null;

	/**
	 * @var {IOption} timezone Currently selected timezone
	 */
	private timezone: IOption = {
		label: '',
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
			label: this.$t('gateway.datetime.form.setOptions.ntp').toString(),
		},
		{
			value: TimeSetOptions.MANUAL,
			label: this.$t('gateway.datetime.form.setOptions.manual').toString(),
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
					label: this.time.formattedZone,
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
				label: timezone.name + ' (' + timezone.code + ', ' + timezone.offset + ')',
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
