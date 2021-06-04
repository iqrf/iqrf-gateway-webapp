import axios, {AxiosResponse} from 'axios';

class ProductService {

	/**
	 * @constant {string} productUrl Repository products endpoint
	 */
	private productUrl = 'https://repository.iqrfalliance.org/api/products/';

	/**
	 * Retrieves the product by its HWPID
	 * @param hwpid Product HWPID
	 */
	get(hwpid: number): Promise<AxiosResponse> {
		return axios.get(this.productUrl + hwpid);
	}

	/**
	 * Retrieves all products in repository
	 */
	getAll(): Promise<AxiosResponse> {
		return axios.get(this.productUrl);
	}
}

export default new ProductService();
