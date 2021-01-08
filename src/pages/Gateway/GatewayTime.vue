<template>
	<CCard>
		<CCardBody>
			<CAlert v-if='dateTime !==""' color='primary'>
				{{ $t('gateway.datetime.current', {dateTime: dateTime}) }}
			</CAlert>
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
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import TimeService from '../../services/TimeService';
import {AxiosError, AxiosResponse} from 'axios';
import {Datetime} from 'vue-datetime';
import ToastClear from '../../helpers/ToastClear';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CSelect,
		Datetime,
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
	 * @var {string} dateTime Current date, time and timezone
	 */
	private dateTime = ''

	/**
	 * @var {string} timezone Currently selected timezone
	 */
	private timezone = ''

	/**
	 * @var {Array<string>} timezoneOptions Array of available timezones at Gateway
	 */
	private timezoneOptions: Array<string> = []
	
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
	 * Retrieves current gateway date, time and timezone
	 */
	private getTime(): void {
		this.$store.commit('spinner/SHOW');
		TimeService.getTime()
			.then((response: AxiosResponse) => {
				this.dateTime = response.data;
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
				this.timezoneOptions = response.data.timezones;
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				console.error(error);
			});
	}

	/**
	 * Sets new gateway timezone
	 */
	private setTimezone(): void {
		TimeService.setTimezone(this.timezone.split(' ')[0])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				ToastClear.success('gateway.datetime.messages.timezoneSuccess');
			})
			.catch((error: AxiosError) => console.error(error));
	}
}
</script>
