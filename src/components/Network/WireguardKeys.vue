<template>
	<div>
		<CCard>
			<CCardHeader class='border-0'>
				{{ $t('network.wireguard.keys.title') }}
				<CButton
					style='float: right;'
					color='success'
					size='sm'
					@click='modal.show = true'
				>
					<CIcon :content='icons.add' size='sm' />
					{{ $t('forms.add') }}
				</CButton>
			</CCardHeader>
		</CCard>
		<CModal
			:show.sync='modal.show'
			color='success'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('network.wireguard.keys.modal.title') }}
				</h5>
			</template>
			<div class='form-group'>
				<CInput
					v-model='modal.name'
					:label='$t("network.wireguard.keys.modal.name")'
				/>
				<p v-if='modal.name === ""' style='color: red;'>
					{{ $t('network.wireguard.keys.modal.errors.name') }}
				</p>
			</div>
			<template #footer>
				<CButton
					color='secondary'
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</CButton> <CButton
					color='success'
					:disabled='modal.name == ""'
					@click='createKeys'
				>
					{{ $t('forms.add') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CInput} from '@coreui/vue/src';
import {cilPlus, cilPencil, cilTrash} from '@coreui/icons';

import WireguardService from '../../services/WireguardService';

import {Dictionary} from 'vue-router/types/router';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CInput,
	},
})

/**
 * Wireguard keys component
 */
export default class WireguardKeys extends Vue {

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
	}

	/**
	 * @var {Dictionary<string|boolean>} modal Controls whether wireguard interface creation modal is shown
	 */
	private modal: Dictionary<string|boolean> = {
		name: '',
		show: false,
	}

	/**
	 * Hides wireguard keys modal and clears data structure
	 */
	private hideModal(): void {
		this.modal.name = '';
		this.modal.show = false;
	}

	/**
	 * Generates a key-pair under a specified name
	 */
	private createKeys(): void {
		let name = (this.modal.name as string);
		this.hideModal();
		this.$store.commit('spinner/SHOW');
		WireguardService.createKeys(name)
			.then(() => {
				this.$store.commit('spinner/HIDE');
			});
	}

}
</script>
