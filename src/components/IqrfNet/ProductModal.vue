<template>
	<v-dialog
		v-model='render'
		width='66%'
		persistent
		no-click-animation
	>
		<template #activator='{ on, attrs }'>
			<v-btn
				style='float: right;'
				color='primary'
				v-bind='attrs'
				v-on='on'
			>
				{{ $t('iqrfnet.product.browse') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.product.title') }}
			</v-card-title>
			<v-card-text>
				<v-row>
					<v-col md='6'>
						<v-text-field
							v-model='filters.companyName'
							:label='$t("iqrfnet.enumeration.manufacturer")'
						/>
					</v-col>
					<v-col md='6'>
						<v-text-field
							v-model='filters.name'
							:label='$t("iqrfnet.enumeration.product")'
						/>
					</v-col>
				</v-row>
				<v-data-table
					:headers='header'
					:items='items'
					:loading='loading'
				>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='success'
							small
							@click='selectProduct(item)'
						>
							<v-icon small>
								mdi-check
							</v-icon>
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					color='secondary'
					@click='render = false'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import ProductService from '@/services/IqrfRepository/ProductService';

import {IProduct} from '@/interfaces/repository';
import {DataTableHeader} from 'vuetify';

/**
 * Product filters
 */
interface ProductFilters {
	companyName: string|null;
	name: string|null;
}

@Component({})

/**
 * Product modal component
 */
export default class ProductModal extends Vue {

	/**
	 * @var {boolean} show Controls whether the product modal is rendered
	 */
	private render = false;

	/**
	 * @var {boolean} loading Indicates whether the product list is being loaded
	 */
	private loading = false;

	/**
	 * @var {ProductFilters} filters Product filters
	 */
	private filters: ProductFilters = {
		companyName: null,
		name: null,
	};

	/**
	 * @var {Array<IProduct>} products Array of products from repository
	 */
	private products: Array<IProduct> = [];

	/**
	 * @var {Array<DataTableHeader>} header Data table header
   */
	private header: Array<DataTableHeader> = [
		{
			value: 'companyName',
			text: this.$t('iqrfnet.enumeration.manufacturer').toString(),
		},
		{
			value: 'name',
			text: this.$t('iqrfnet.enumeration.product').toString(),
		},
		{
			value: 'hwpid',
			text: this.$t('iqrfnet.enumeration.hwpid').toString(),
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end',
		},
	];

	/**
	 * Returns filtered products
	 * @return {Array<IProduct>} Filtered products
	 */
	get items(): Array<IProduct> {
		return this.products.filter((product: IProduct) => {
			return (
				(this.filters.companyName === null || product.companyName.toLowerCase().includes(this.filters.companyName.toLowerCase())) &&
				(this.filters.name === null || product.name.toLowerCase().includes(this.filters.name.toLowerCase()))
			);
		});
	}

	/**
	 * Retrieves products from repository
	 */
	private getProducts(): void {
		this.loading = true;
		ProductService.getAll()
			.then((products: Array<IProduct>) => {
				this.products = products;
				this.loading = false;
			})
			.catch(() => this.loading = false);
	}

	/**
	 * Selects product from table and emits data to the parent component
	 */
	private selectProduct(product: IProduct): void {
		this.render = false;
		this.$emit('selected-product', product);
	}

	/**
	 * Mounted lifecycle hook
	 */
	protected mounted(): void {
		this.getProducts();
	}

}
</script>
