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
	<CCard>
		<CCardHeader>
			{{ $t('service.systemd-journald.config.title') }}
		</CCardHeader>
		<CCardBody>
			<CElementCover
				v-if='failed'
				style='z-index: 1;'
				:opacity='0.85'
			>
				{{ $t('service.systemd-journald.config.messages.fetchFailed') }}
			</CElementCover>
			<ValidationObserver
				v-if='config !== null'
				v-slot='{invalid}'
			>
				<CForm @submit.prevent='saveConfig'>
					<CInputCheckbox
						:checked.sync='config.forwardToSyslog'
						:label='$t("service.systemd-journald.config.form.forwardToSyslog").toString()'
					/>
					<CRow form>
						<CCol sm='12' lg='4'>
							<CSelect
								:value.sync='config.persistence'
								:label='$t("service.systemd-journald.config.form.storage").toString()'
								:options='storageOptions'
							/>
						</CCol>
						<CCol sm='12' lg='4'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|integer|min:0'
								:custom-messages='{
									required: $t("service.systemd-journald.config.errors.maxDisk"),
									integer: $t("service.systemd-journald.config.errors.maxDiskInvalid"),
									min: $t("service.systemd-journald.config.errors.maxDiskInvalid"),
								}'
							>
								<CInput
									v-model.number='config.maxDiskSize'
									type='number'
									min='0'
									:label='$t("service.systemd-journald.config.form.maxDisk").toString()'
									:description='$t("service.systemd-journald.config.form.defaultNote")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ").toString()'
								/>
							</ValidationProvider>
						</CCol>
						<CCol sm='12' lg='4'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|integer|min:1'
								:custom-messages='{
									required: $t("service.systemd-journald.config.errors.maxFiles"),
									integer: $t("service.systemd-journald.config.errors.maxFilesInvalid"),
									min: $t("service.systemd-journald.config.errors.maxFilesInvalid"),
								}'
							>
								<CInput
									v-model.number='config.maxFiles'
									type='number'
									min='1'
									:label='$t("service.systemd-journald.config.form.maxFiles").toString()'
									:description='$t("service.systemd-journald.config.form.maxFilesNote")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ").toString()'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
					<div class='form-group'>
						<label>
							<strong>{{ $t('service.systemd-journald.config.form.sizeRotation') }}</strong>
						</label><br>
						<CSwitch
							:checked.sync='sizeRotation'
							color='primary'
							shape='pill'
							size='lg'
							label-on='ON'
							label-off='OFF'
						/>
					</div>
					<CRow v-if='sizeRotation'>
						<CCol sm='12' lg='4'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|integer|min:0'
								:custom-messages='{
									required: $t("service.systemd-journald.config.errors.maxFileSize"),
									integer: $t("service.systemd-journald.config.errors.maxFileSizeInvalid"),
									min: $t("service.systemd-journald.config.errors.maxFileSizeInvalid"),
								}'
							>
								<CInput
									v-model.number='config.sizeRotation.maxFileSize'
									type='number'
									min='0'
									:label='$t("service.systemd-journald.config.form.maxFileSize").toString()'
									:description='$t("service.systemd-journald.config.form.defaultNote")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ").toString()'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
					<div class='form-group'>
						<label>
							<strong>{{ $t('service.systemd-journald.config.form.timeRotation') }}</strong>
						</label><br>
						<CSwitch
							:checked.sync='timeRotation'
							color='primary'
							shape='pill'
							size='lg'
							label-on='ON'
							label-off='OFF'
						/>
					</div>
					<CRow v-if='timeRotation'>
						<CCol sm='12' lg='4'>
							<CSelect
								:value.sync='config.timeRotation.unit'
								:label='$t("service.systemd-journald.config.form.unit").toString()'
								:options='unitOptions'
							/>
						</CCol>
						<CCol sm='12' lg='4'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required|integer|min:1'
								:custom-messages='{
									required: $t("service.systemd-journald.config.errors.count"),
									integer: $t("service.systemd-journald.config.errors.countInvalid"),
									min: $t("service.systemd-journald.config.errors.countInvalid"),
								}'
							>
								<CInput
									v-model.number='config.timeRotation.count'
									type='number'
									min='1'
									:label='$t("service.systemd-journald.config.form.count").toString()'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ").toString()'
								/>
							</ValidationProvider>
						</CCol>
					</CRow>
					<CButton
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput, CInputCheckbox, CSelect, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {Persistence, TimeUnit} from '@/enums/Gateway/Journal';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import JournalService from '@/services/JournalService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/Coreui';
import {IJournal} from '@/interfaces/Gateway/Journal';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		CInputCheckbox,
		CSelect,
		CSwitch,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Gateway systemd journal configuration component
 */
export default class JournaldConfig extends Vue {

	/**
	 * @var {IJournal|null} config Systemd journal configuration
	 */
	private config: IJournal|null = null;

	/**
	 * @var {boolean} sizeRotation Use size based log rotation
	 */
	private sizeRotation = false;

	/**
	 * @var {boolean} timeRotation Use time based log rotation
	 */
	private timeRotation = false;

	/**
	 * @constant {Array<IOption>} storageOptions Array of CoreUI select storage method options
	 */
	private storageOptions: Array<IOption> = [
		{
			label: this.$t('service.systemd-journald.config.form.storageMethods.volatile').toString(),
			value: Persistence.VOLATILE,
		},
		{
			label: this.$t('service.systemd-journald.config.form.storageMethods.persistent').toString(),
			value: Persistence.PERSISTENT,
		},
	];

	/**
	 * @constant {Array<IOption>} unitOptions Array of CoreUI select unit options
	 */
	private unitOptions: Array<IOption> = [
		{
			label: this.$t('service.systemd-journald.config.form.units.seconds').toString(),
			value: TimeUnit.SECONDS,
		},
		{
			label: this.$t('service.systemd-journald.config.form.units.minutes').toString(),
			value: TimeUnit.MINUTES,
		},
		{
			label: this.$t('service.systemd-journald.config.form.units.hours').toString(),
			value: TimeUnit.HOURS,
		},
		{
			label: this.$t('service.systemd-journald.config.form.units.days').toString(),
			value: TimeUnit.DAYS,
		},
		{
			label: this.$t('service.systemd-journald.config.form.units.weeks').toString(),
			value: TimeUnit.WEEKS,
		},
		{
			label: this.$t('service.systemd-journald.config.form.units.months').toString(),
			value: TimeUnit.MONTHS,
		},
		{
			label: this.$t('service.systemd-journald.config.form.units.years').toString(),
			value: TimeUnit.YEAR,
		},
	];

	/**
	 * @var {boolean} failed Indicates whether configuration fetch failed
	 */
	private failed = false;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

	/**
	 * Retrieves systemd journal configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves systemd journal configuration
	 */
	private getConfig(): Promise<void> {
		return JournalService.getConfig()
			.then((response: AxiosResponse) => {
				const config: IJournal = response.data;
				if (config.sizeRotation.maxFileSize !== 0) {
					this.sizeRotation = true;
				}
				if (config.timeRotation.unit !== TimeUnit.MONTHS ||
					config.timeRotation.count !== 1) {
					this.timeRotation = true;
				}
				this.config = config;
			})
			.catch((error: AxiosError) => {
				this.failed = true;
				extendedErrorToast(error, 'service.systemd-journald.config.messages.fetchFailedError');
			});
	}

	/**
	 * Saves systemd journal configuration
	 */
	private saveConfig(): void {
		if (this.config === null) {
			return;
		}
		const config: IJournal = JSON.parse(JSON.stringify(this.config));
		if (!this.sizeRotation) {
			config.sizeRotation.maxFileSize = 0;
		}
		if (!this.timeRotation) {
			config.timeRotation.unit = TimeUnit.MONTHS;
			config.timeRotation.count = 1;
		}
		this.$store.commit('spinner/SHOW');
		JournalService.saveConfig(config)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('service.systemd-journald.config.messages.saveSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'service.systemd-journald.config.messages.saveFailed'));
	}
}
</script>
