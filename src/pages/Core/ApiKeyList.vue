<template>
	<div>
		<h1>{{ $t('core.apiKey.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='success'
					size='sm'
					class='float-right'
					to='/api-key/add'
				>
					<CIcon :content='$options.icons.add' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:fields='fields'
					:items='keys'
					:striped='true'
					:pagination='true'
					:items-per-page='20'
					:column-filter='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #expiration='{item}'>
						<td v-if='item.expiration !== null'>
							{{ timeString(item) }}
						</td>
						<td v-else>
							never
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								color='info'
								size='sm'
								:to='"/api-key/edit/" + item.id'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='modals.key = item.id'
							>
								<CIcon :content='$options.icons.remove' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='modals.key !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('core.apiKey.messages.deleteTitle') }}
				</h5>
			</template>
			{{ $t('core.apiKey.messages.deletePrompt', {key: modals.key}) }}
			<template #footer>
				<CButton color='danger' @click='modals.key = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeKey'>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CDataTable, CIcon} from '@coreui/vue/src';
import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import ApiKeyService from '../../services/ApiKeyService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import {DateTime} from 'luxon';

export default {
	name: 'ApiKeyList',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CIcon
	},
	data() {
		return {
			keys: null,
			fields: [
				{
					key: 'id',
					label: this.$t('core.apiKey.form.id'),
				},
				{
					key: 'description',
					label: this.$t('core.apiKey.form.description'),
				},
				{
					key: 'expiration',
					label: this.$t('core.apiKey.form.expiration'),
				},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					filter: false,
					sorter: false,
				},
			],
			modals: {
				key: null,
			},
			dateFormat: {
				year: 'numeric',
				month: 'short',
				day: 'numeric',
				hour12: false,
				hour: 'numeric',
				minute: 'numeric',
				second: 'numeric',
			}
		};
	},
	created() {
		this.getKeys();
	},
	methods: {
		getKeys() {
			this.$store.commit('spinner/SHOW');
			return ApiKeyService.getApiKeys()
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.keys = response.data;
				})
				.catch((error) => FormErrorHandler.apiKeyError(error));
		},
		removeKey() {
			this.$store.commit('spinner/SHOW');
			const key = this.modals.key;
			this.modals.key = null;
			ApiKeyService.deleteApiKey(key)
				.then(() => {
					this.getKeys().then(() => {
						this.$toast.success(this.$t('core.apiKey.messages.deleteSuccess', {key: key}).toString());
					});
				})
				.catch((error) => FormErrorHandler.apiKeyError(error));
		},
		timeString(item) {
			return DateTime.fromISO(item.expiration).toLocaleString(this.dateFormat);
		}
	},
	icons: {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash
	},
	metaInfo: {
		title: 'core.apiKey.title'
	}
};
</script>

<style scoped>
.btn {
  margin: 0 3px 0 0;
}
</style>
