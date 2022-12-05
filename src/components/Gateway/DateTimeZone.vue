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
		<CCardBody>
			<CRow v-if='gatewayTime !== null'>
				<CCol md='6'>
					<p>
						<b>
							{{ $t('gateway.datetime.currentTime') }}
						</b>
					</p>
					<p style='font-size: 2em;'>
						{{ timeDisplay }}
					</p>
					<p style='font-size: 1.5em;'>
						{{ timezoneDisplay }}
					</p>
					<div class='form-group'>
						<label for='clockFormatSwitch'>
							{{ $t('gateway.datetime.clockFormat') }}
						</label><br>
						<CSwitch
							id='clockFormatSwitch'
							:checked.sync='hour12'
							color='primary'
							shape='pill'
							size='lg'
							:label-on='$t("forms.on")'
							:label-off='$t("forms.off")'
						/>
					</div>
					<p>
						<b>
							{{ $t('gateway.datetime.ntpSync') }}
						</b>
						<CIcon
							size='xl'
							:class='gatewayTime.ntpSynchronized ? "text-success" : "text-danger"'
							:content='gatewayTime.ntpSynchronized ? icons.sync : icons.notSync'
						/>
					</p>
				</CCol>
				<CCol md='6'>
					<p>
						<b>
							{{ $t('gateway.datetime.currentTimezone') }}
						</b>
					</p>
					<p>
						{{ currentTimezone }}
					</p>
					<CForm @submit.prevent='setTimezone'>
						<div class='form-group'>
							<label>
								<strong>
									{{ $t('gateway.datetime.form.timezone') }}
								</strong>
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
						<CButton color='primary' type='submit'>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</CCol>
			</CRow>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCol, CForm, CIcon, CInputCheckbox, CRow, CSelect, CSwitch} from '@coreui/vue/src';
import vSelect from 'vue-select';

import {cilCheckCircle, cilXCircle} from '@coreui/icons';
import {DateTime} from 'luxon';
import {extendedErrorToast} from '@/helpers/errorToast';
import TimeService from '@/services/TimeService';

import {AxiosError, AxiosResponse} from 'axios';
import {ITime, ITimezone} from '@/interfaces/gatewayTime';
import {IOption} from '@/interfaces/coreui';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCol,
		CForm,
		CIcon,
		CInputCheckbox,
		CRow,
		CSelect,
		CSwitch,
		vSelect,
	}
})

/**
 * Date and time settings component for Gateway
 */
export default class DateTimeZone extends Vue {

	/**
	 * @var {ITime|null} gatewayTime Gateway timezone information
	 */
	private gatewayTime: ITime|null = null;

	/**
	 * @var {boolean} hour12 Use 12-hour clock to display time
	 */
	private hour12 = false;

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
	 * @var {ReturnType<typeof setInterval>} timeRefreshInterval Timestamp refresh interval
	 */
	private timeRefreshInterval: ReturnType<typeof setInterval>|null = null;

	/**
	 * @constant {Record<string, Array<string>>} icons Dictionary of coreui icons
	 */
	private icons: Record<string, Array<string>> = {
		sync: cilCheckCircle,
		notSync: cilXCircle
	};

	/**
	 * Retrieves gateway time and available timezones
	 */
	mounted(): void {
		this.getTime(false);
	}

	/**
	 * Clears time refresh interval
	 */
	beforeDestroy(): void {
		this.clearActiveInterval();
	}

	/**
	 * Computes time string from timestamp
	 * @returns {string} Date/Time string
	 */
	get timeDisplay(): string {
		if (this.gatewayTime === null) {
			return '';
		}
		return DateTime.fromMillis(this.gatewayTime.timestamp * 1000,
			{zone: this.gatewayTime.name}).toFormat(
			'cccc dd.LL.yyyy ' + (this.hour12 ? 'tt': 'TT')
		);
	}

	/**
	 * Computes timezone string from timestamp
	 * @returns {string} Timezone string
	 */
	get timezoneDisplay(): string {
		if (this.gatewayTime === null) {
			return '';
		}
		return DateTime.fromMillis(this.gatewayTime.timestamp * 1000,
			{zone: this.gatewayTime.name}).toFormat('ZZZZZ (ZZZZ)');
	}

	/**
	 * Computes current timezone string
	 * @returns {string} Gateway timezone string
	 */
	get currentTimezone(): string {
		if (this.gatewayTime === null) {
			return '';
		}
		return this.gatewayTime.name + ' (' + this.gatewayTime.code + ', ' + this.gatewayTime.offset + ')';
	}

	/**
	 * Retrieves current gateway date, time and timezone
	 * @param {boolean} sync Indicates whether time fetch was invoked by NTP sync
	 */
	public getTime(sync: boolean): void {
		this.$store.commit('spinner/SHOW');
		this.clearActiveInterval();
		TimeService.getTime()
			.then((response: AxiosResponse) => {
				this.gatewayTime = response.data;
				if (this.gatewayTime === null) {
					return;
				}
				this.timezone = {
					label: this.gatewayTime.name + ' (' + this.gatewayTime.code + ', ' + this.gatewayTime.offset + ')',
					value: this.gatewayTime?.name,
				};
				this.timeRefreshInterval = setInterval(() => {
					this.gatewayTime!.timestamp++;
				}, 1000);
				this.getTimezones(sync);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.datetime.messages.timeGetFailed'));
	}

	/**
	 * Retrieve list of available timezones
	 * @param {boolean} sync Indicates whether time fetch was invoked by NTP sync
	 */
	private getTimezones(sync: boolean): void {
		TimeService.getTimezones()
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.readTimezones(response.data);
				if (sync) {
					this.$toast.success(
						this.$t('gateway.ntp.messages.syncSuccess').toString()
					);
				}
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.datetime.messages.timezoneGetFailed'));
	}

	/**
	 * Reads timezones information and creates select options
	 * @param {Array<ITimezone>} timezones Array of timezones information from REST API
	 */
	private readTimezones(timezones: Array<ITimezone>): void {
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
	 * Sets new gateway timezone
	 */
	private setTimezone(): void {
		this.$store.commit('spinner/SHOW');
		TimeService.setTimezone({timezone: (this.timezone.value as string)})
			.then(() => {
				this.$toast.success(
					this.$t('gateway.datetime.messages.timezoneSetSuccess').toString()
				);
				this.clearActiveInterval();
				this.getTime(false);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.datetime.messages.timezoneSetFailed'));
	}

	/**
	 * Clears active time refresh interval
	 */
	private clearActiveInterval(): void {
		if (this.timeRefreshInterval !== null) {
			clearInterval(this.timeRefreshInterval);
		}
	}
}
</script>
