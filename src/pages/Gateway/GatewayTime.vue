<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm @submit.prevent='saveChanges'>
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

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
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
	 * @var {Array<string>} timezoneOptions Array of available timezones at Gateway
	 */
	private timezoneOptions: Array<string> = []

	/**
	 * @var {string} timezone Currently selected timezone
	 */
	private timezone = ''

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
		this.getTimezones();
	}

	/**
	 * Retrieve list of available timezones
	 */
	private getTimezones(): void {
		TimeService.getTimezones()
			.then((response: AxiosResponse) => this.timezoneOptions = response.data.timezones)
			.catch((error: AxiosError) => console.error(error));
	}
}
</script>
