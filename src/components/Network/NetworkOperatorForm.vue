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
	<ValidationObserver v-slot='{invalid}'>
		<CModal
			:color='modalColor'
			size='lg'
			:show.sync='show'
			:close-on-backdrop='false'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ modalTitle }}
				</h5>
			</template>
			<CForm>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("network.operators.errors.nameRequired"),
					}'
				>
					<CInput
						v-model='operator.name'
						:label='$t("network.operators.form.name")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("network.operators.errors.apnRequired"),
					}'
				>
					<CInput
						v-model='operator.apn'
						:label='$t("network.operators.form.apn")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						required: operator.password.length > 0
					}'
					:custom-messages='{
						required: $t("forms.errors.credentials"),
					}'
				>
					<CInput
						v-model='operator.username'
						:label='$t("network.operators.form.username")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					:rules='{
						required: operator.username.length > 0
					}'
					:custom-messages='{
						required: $t("forms.errors.credentials"),
					}'
				>
					<CInput
						v-model='operator.password'
						:label='$t("network.operators.form.password")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
			</CForm>
			<template #footer>
				<CButton
					:color='modalColor'
					:disabled='invalid'
					@click='saveOperator'
				>
					{{ $t('forms.save') }}
				</CButton> <CButton
					color='secondary'
					@click='deactivateModal'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CForm, CIcon, CInput, CModal} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {digits, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOperator} from '@/interfaces/Network/Mobile';

@Component({
	components: {
		CButton,
		CForm,
		CIcon,
		CInput,
		CModal,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Network operator form component
 */
export default class NetworkOperatorForm extends Vue {

	/**
	 * @var {boolean} show Controls whether modal is showed
	 */
	private show = false;

	/**
	 * @var {number} id Network operator ID
	 */
	private id = 0;

	/**
	 * @var {IOperator} operator Network operator configuration
	 */
	private operator: IOperator = {
		name: '',
		apn: '',
		username: '',
		password: ''
	};

	/**
	 * Returns modal color depending on operator id
	 */
	get modalColor(): string {
		if (this.id === 0) {
			return 'success';
		}
		return 'primary';
	}

	/**
	 * Returns modal title depending on operator title
	 */
	get modalTitle(): string {
		if (this.id === 0) {
			return this.$t('network.operators.form.add').toString();
		}
		return this.$t('network.operators.form.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('digits', digits);
		extend('required', required);
	}

	/**
	 * Retrieves operator details
	 */
	private getOperator(): void {
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.getOperator(this.id)
			.then((rsp: AxiosResponse) => {
				this.operator = rsp.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((err: AxiosError) => {
				extendedErrorToast(err, 'network.operators.messages.getFailed');
				this.deactivateModal();
			});
	}

	/**
	 * Saves network operator configuration
	 */
	private saveOperator(): void {
		if (this.id === 0) {
			this.addOperator();
		} else {
			this.editOperator();
		}
	}

	/**
	 * Saves new network operator
	 */
	private addOperator(): void {
		const operator = this.filterOperator(this.operator);
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.addOperator(operator)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.deactivateModal();
				this.$toast.success(
					this.$t('network.operators.messages.addSuccess', {operator: operator.name}).toString()
				);
				this.$emit('closed');
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'network.operators.messages.addFailed'));
	}

	/**
	 * Edits existing network operator
	 */
	private editOperator(): void {
		const operator = this.filterOperator(this.operator);
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.editOperator(this.id, operator)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.deactivateModal();
				this.$toast.success(
					this.$t('network.operators.messages.editSuccess', {operator: operator.name}).toString()
				);
				this.$emit('closed');
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'network.operators.messages.editFailed'));
	}

	/**
	 * Filters operator configuration
	 * @param {IOperator} operator Operator
	 * @return {IOperator} Filtered operator
	 */
	private filterOperator(operator: IOperator): IOperator {
		delete operator.id;
		if (operator.username?.length === 0) {
			delete operator.username;
		}
		if (operator.password?.length === 0) {
			delete operator.password;
		}
		return operator;
	}

	/**
	 * Activates the modal and retrieves operator information
	 * @param {number|undefined} id Operator ID
	 */
	public activateModal(id: number|undefined = undefined): void {
		if (id !== undefined) {
			this.id = id;
			this.getOperator();
		}
		this.show = true;
	}

	/**
	 * Hides the modal
	 */
	public deactivateModal(): void {
		this.show = false;
		this.id = 0;
		this.operator = {
			name: '',
			apn: '',
			username: '',
			password: '',
		};
	}
}
</script>
