<template>
	<CModal
		:show.sync='render'
		color='primary'
		size='xl'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('iqrfnet.product.title') }}
			</h5>
		</template>
		<CDataTable
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
						<CIcon :content='icon' size='sm' />
					</CButton>
				</td>
			</template>
		</CDataTable>
		<template #footer>
			<CButton
				color='secondary'
				@click='hide'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CDataTable, CIcon, CModal} from '@coreui/vue/src';
import {cilCheckAlt} from '@coreui/icons';

import ProductService from '@/services/IqrfRepository/ProductService';

import {AxiosResponse} from 'axios';
import {IField} from '@/interfaces/coreui';
import {IProduct} from '@/interfaces/repository';

@Component({
	components: {
		CButton,
		CDataTable,
		CIcon,
		CModal,
	},
})

/**
 * Product modal component
 */
export default class ProductModal extends Vue {

	/**
	 * @var {boolean} show Controls whether or not the product modal is rendered
	 */
	private render = false;

	/**
	 * @var {Array<IProduct>} products Array of products from repository
	 */
	private products: Array<IProduct> = [];

	/**
	 * @constant {Array<IField>} fields Array of coreui data table fields
	 */
	private fields: Array<IField> = [
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
	 * @constant {Array<string>} icon Check icon for select button
	 */
	private icon: Array<string> = cilCheckAlt;

	/**
	 * Retrieves products from repository
	 */
	private getProducts(): void {
		this.$store.commit('spinner/SHOW');
		ProductService.getAll()
			.then((response: AxiosResponse) => {
				this.products = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch(() => this.$store.commit('spinner/HIDE'));
	}

	/**
	 * Selects product from table and emits data to the parent component
	 */
	private selectProduct(product: IProduct): void {
		this.$emit('selected-product', product);
	}

	/**
	 * Shows modal window
	 */
	public show(): void {
		this.render = true;
		if (this.products.length === 0) {
			this.getProducts();
		}
	}

	/**
	 * Hides modal window
	 */
	public hide(): void {
		this.render = false;
	}
}
</script>
