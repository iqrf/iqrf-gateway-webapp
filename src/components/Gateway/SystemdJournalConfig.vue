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
						:label='$t("service.systemd-journald.config.form.forwardToSyslog")'
					/>
					<CSelect
						:value.sync='config.persistence'
						:label='$t("service.systemd-journald.config.form.storage")'
						:options='storageOptions'
					/>
					<div class='form-group'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|min:0'
							:custom-messages='{
								required: "service.systemd-journald.config.errors.maxDisk",
								integer: "service.systemd-journald.config.errors.maxDiskInvalid",
								min: "service.systemd-journald.config.errors.maxDiskInvalid"
							}'
						>
							<CInput
								v-model.number='config.maxDiskSize'
								type='number'
								min='0'
								:label='$t("service.systemd-journald.config.form.maxDisk")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<i>{{ $t('service.systemd-journald.config.form.defaultNote') }}</i>
					</div>
					<div class='form-group'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|min:1'
							:custom-messages='{
								required: "service.systemd-journald.config.errors.maxFiles",
								integer: "service.systemd-journald.config.errors.maxFilesInvalid",
								min: "service.systemd-journald.config.errors.maxFilesInvalid"
							}'
						>
							<CInput
								v-model.number='config.maxFiles'
								type='number'
								min='1'
								:label='$t("service.systemd-journald.config.form.maxFiles")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<i>{{ $t('service.systemd-journald.config.form.maxFilesNote') }}</i>
					</div>
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
					<div
						v-if='sizeRotation'
						class='form-group'
					>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|min:0'
							:custom-messages='{
								required: "service.systemd-journald.config.errors.maxFileSize",
								integer: "service.systemd-journald.config.errors.maxFileSizeInvalid",
								min: "service.systemd-journald.config.errors.maxFileSizeInvalid"
							}'
						>
							<CInput
								v-model.number='config.sizeRotation.maxFileSize'
								type='number'
								min='0'
								:label='$t("service.systemd-journald.config.form.maxFileSize")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<i>{{ $t('service.systemd-journald.config.form.defaultNote') }}</i>
					</div>
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
					<div
						v-if='timeRotation'
						class='form-group'
					>
						<CSelect
							:value.sync='config.timeRotation.unit'
							:label='$t("service.systemd-journald.config.form.unit")'
							:options='unitOptions'
						/>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|min:1'
							:custom-messages='{
								required: "service.systemd-journald.config.errors.count",
								integer: "service.systemd-journald.config.errors.countInvalid",
								min: "service.systemd-journald.config.errors.countInvalid"
							}'
						>
							<CInput
								v-model.number='config.timeRotation.count'
								type='number'
								min='1'
								:label='$t("service.systemd-journald.config.form.count")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
					</div>
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

import {StorageMethod, TimeUnit} from '@/enums/Gateway/SystemdJournal';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import GatewayService from '@/services/GatewayService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/coreui';
import {ISystemdJournal} from '@/interfaces/systemdJournal';

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
export default class SystemdJournaldConfig extends Vue {

	/**
	 * @var {ISystemdJournal|null} config Systemd journal configuration
	 */
	private config: ISystemdJournal|null = null;

	/**
	 * @var {boolean} sizeRotation Use size based log rotation
	 */
	private sizeRotation = false;

	/**
	 * @var {boolean} timeRotation Use time based log rotation
	 */
	private timeRotation = false;

	/**
	 * @constant {Array<IOption>} storageOptions Array of coreui select storage method options
	 */
	private storageOptions: Array<IOption> = [
		{
			label: this.$t('service.systemd-journald.config.form.storageMethods.volatile').toString(),
			value: StorageMethod.VOLATILE,
		},
		{
			label: this.$t('service.systemd-journald.config.form.storageMethods.persistent').toString(),
			value: StorageMethod.PERSISTENT,
		},
	];

	/**
	 * @constant {Array<IOption>} unitOptions Array of coreui select unit options
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
	 * @var {boolean} failed Indicates whether configuraiton fetch failed
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
		return GatewayService.getSystemdJournalConfig()
			.then((response: AxiosResponse) => {
				const config: ISystemdJournal = response.data;
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
		const config: ISystemdJournal = this.config;
		if (!this.sizeRotation) {
			config.sizeRotation.maxFileSize = 0;
		}
		if (!this.timeRotation) {
			config.timeRotation.unit = TimeUnit.MONTHS;
			config.timeRotation.count = 1;
		}
		this.$store.commit('spinner/SHOW');
		GatewayService.saveSystemdJournalConfig(config)
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
