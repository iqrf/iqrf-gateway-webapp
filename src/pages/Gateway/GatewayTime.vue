<template>
	<CCard>
		<CCardBody>
			<CRow>
				<CCol>
					<p v-if='gatewayTime !== null'>
						<b>
							{{ $t('gateway.datetime.currentTime') }}
						</b>
					</p>
					<p style='font-size: 3em'>
						{{ timeDisplay }}
					</p>
				</CCol>
				<CCol>
					<p>
						<b>
							{{ $t('gateway.datetime.currentTimezone') }}
						</b>
					</p>
					<p>
						{{ timezoneDisplay }}
					</p>
					<ValidationObserver v-slot='{invalid}'>
						<CForm @submit.prevent='setTimezone'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: "gateway.datetime.errors.timezone"
								}'
							>
								<CSelect
									:value.sync='timezone'
									:options='timezoneOptions'
									:label='$t("gateway.datetime.form.timezone")'
									:placeholder='$t("gateway.datetime.form.timezonePlaceholder")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<CButton color='primary' type='submit' :disabled='invalid'>
								{{ $t('forms.save') }}
							</CButton>
						</CForm>
					</ValidationObserver>
				</CCol>
			</CRow>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCol, CForm, CInputCheckbox, CRow, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import TimeService from '../../services/TimeService';
import {ITime, ITimezone} from '../../interfaces/gatewayTime';
import {AxiosError, AxiosResponse} from 'axios';
import ToastClear from '../../helpers/ToastClear';
import {IOption} from '../../interfaces/coreui';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCol,
		CForm,
		CInputCheckbox,
		CRow,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'gateway.datetime.title'
	}
})

/**
 * Date and time settings component for Gateway
 */
export default class GatewayTime extends Vue {

	/**
	 * @var {ITime|null} gatewayTime Gateway timezone information
	 */
	private gatewayTime: ITime|null = null

	/**
	 * @var {string} timezone Currently selected timezone
	 */
	private timezone = ''

	/**
	 * @var {Array<IOption>} timezoneOptions Array of available timezones at Gateway
	 */
	private timezoneOptions: Array<IOption> = []

	/**
	 * @var {ReturnType<typeof setInterval>} timeRefreshInterval Timestamp refresh interval
	 */
	private timeRefreshInterval: ReturnType<typeof setInterval>|null = null; 
	
	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Retrieves gateway time and available timezones
	 */
	mounted(): void {
		this.getTime();
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
		return new Date(this.gatewayTime.timestamp * 1000).toLocaleString('en-GB', {
			timeZone: this.gatewayTime.name,
			timeZoneName: 'long',
			hour12: true,
		});
	}

	/**
	 * Computes current timezone string
	 * @returns {string} Gateway timezone string
	 */
	get timezoneDisplay(): string {
		if (this.gatewayTime === null) {
			return '';
		}
		return this.gatewayTime.name + ' (' + this.gatewayTime.code + ', ' + this.gatewayTime.offset + ')';
	}

	/**
	 * Retrieves current gateway date, time and timezone
	 */
	private getTime(): void {
		this.$store.commit('spinner/SHOW');
		TimeService.getTime()
			.then((response: AxiosResponse) => {
				this.gatewayTime = response.data.time;
				this.timezone = this.gatewayTime!.name;
				this.timeRefreshInterval = setInterval(() => {
					this.gatewayTime!.timestamp++;
				}, 1000);
				this.getTimezones();
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				console.error(error);
			});
	}

	/**
	 * Retrieve list of available timezones
	 */
	private getTimezones(): void {
		TimeService.getTimezones()
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.readTimezones(response.data.timezones);
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				console.error(error);
			});
	}

	/**
	 * Reads timezones information and creates select options
	 * @param {Array<ITimezone>} timezones Array of timezones information from REST API
	 */
	private readTimezones(timezones: Array<ITimezone>): void {
		let timezoneArray: Array<IOption> = [];
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
		TimeService.setTimezone({timezone: this.timezone})
			.then(() => {
				ToastClear.success('gateway.datetime.messages.timezoneSuccess');
				this.clearActiveInterval();
				this.getTime();
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				console.error(error);
			});
	}

	private clearActiveInterval(): void {
		if (this.timeRefreshInterval !== null) {
			clearInterval(this.timeRefreshInterval);
		}
	}
}
</script>
