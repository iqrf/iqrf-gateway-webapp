<template>
	<CModal
		:show.sync='show'
		color='primary'
		size='xl'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('iqrfnet.product.title') }}
			</h5>
		</template>
		<CDataTable
			:loading='loading'
			:items='products'
			:fields='fields'
			:column-filter='true'
			:items-per-page='10'
			:pagination='true'
			:striped='true'
			:sorter='{external: false, resetable: true}'
		>
			<template #no-items-view='{}'>
				{{ $t('iqrfnet.product.noProduct') }}
			</template>
			<template #actions='{item}'>
				<td class='col-actions'>
					<CButton
						color='success'
						size='sm'
						@click='selectProduct(item)'
					>
						<CIcon :content='cilCheckAlt' size='sm' />
					</CButton>
				</td>
			</template>
		</CDataTable>
		<template #footer>
			<CButton
				color='secondary'
				@click='closeModal'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CDataTable, CIcon, CModal} from '@coreui/vue/src';

import {cilCheckAlt} from '@coreui/icons';

import ProductService from '@/services/IqrfRepository/ProductService';

import {AxiosResponse} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {IProduct} from '@/interfaces/Repository';
import ModalBase from '@/components/ModalBase.vue';

@Component({
	components: {
		CButton,
		CDataTable,
		CIcon,
		CModal,
	},
	data: () => ({
		cilCheckAlt,
	}),
})

/**
 * Product modal component
 */
export default class ProductModal extends ModalBase {
	/**
	 * @constant {Array<IField>} fields Array of coreui data table fields
	 */
	private readonly fields: Array<IField> = [
		{
			key: 'companyName',
			label: this.$t('iqrfnet.enumeration.manufacturer').toString(),
		},
		{
			key: 'name',
			label: this.$t('iqrfnet.enumeration.product').toString(),
		},
		{
			key: 'hwpid',
			label: this.$t('iqrfnet.enumeration.hwpid').toString(),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			sorter: false,
			filter: false,
		},
	];

	/**
	 * @var {boolean} loading Indicates that a request is in progress
	 */
	private loading = false;

	/**
	 * @var {Array<IProduct>} products Array of products from repository
	 */
	private products: Array<IProduct> = [];

	/**
	 * Retrieves products from repository
	 */
	private getProducts(): void {
		this.loading = true;
		ProductService.getAll()
			.then((response: AxiosResponse) => {
				this.products = response.data;
				this.loading = false;
			})
			.catch(() => this.loading = false);
	}

	/**
	 * Selects product from table and emits data to the parent component
	 */
	private selectProduct(product: IProduct): void {
		this.closeModal();
		this.$emit('selected-product', product);
	}

	/**
	 * Shows modal window
	 */
	public showModal(): void {
		this.openModal();
		if (this.products.length === 0) {
			this.getProducts();
		}
	}

	/**
	 * Hides modal window
	 */
	public hideModal(): void {
		this.closeModal();
	}
}
</script>
