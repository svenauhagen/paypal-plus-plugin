import { formDataByElement, formDataForCart } from './form';
import { contextByElement } from './context';

const SINGLE_PRODUCT_BUTTON = 'paypalplus_ecs_single_product_button';
const CART_BUTTON = 'paypalplus_ecs_cart_button';

/**
 * Class Smart Payment Button Renderer
 *
 * @type {SmartPaymentButtonRenderer}
 */
const SmartPaymentButtonRenderer = class SmartPaymentButtonRenderer
{
    /**
     * Constructor
     *
     * @param validContexts
     * @param request
     */
    constructor(validContexts, request)
    {
        this.validContexts = Array.from(validContexts);
        this.request = request;
    }

    /**
     * Render button for single product
     */
    singleProductButtonRender()
    {
        const element = document.querySelector(`#${SINGLE_PRODUCT_BUTTON}`);
        element && this.render(element);
    }

    /**
     * Render Button for Cart
     */
    cartButtonRender()
    {
        const element = document.querySelector(`#${CART_BUTTON}`);
        element && this.render(element);
    }

    /**
     * Render Button for the Given Element
     *
     * @param element
     * @returns {*}
     */
    // TODO Make it private
    render(element)
    {
        if (_.isUndefined(paypal)) {
            return;
        }

        return paypal.Buttons({
            style: {
                layout: 'vertical',
            },
            createOrder: (data, actions) => {
                const formData = this.formDataByContext(element);

                this.request
                    .submit(formData)
                    .then(() => {
                        return 12; // TODO Implement correct value instead of dummy data
                    });
            }
        }).render(element);
    }

    /**
     * Retrieve context for FormData instance by the Given Element
     *
     * @param element
     * @returns {FormData}
     */
    // TODO Make it private
    formDataByContext(element)
    {
        let formData = new FormData();
        const context = contextByElement(element);

        if (!this.validContexts.includes(context)) {
            throw new Error(
                'Invalid context when try to retrieve the form data during express checkout request.'
            );
        }

        try {
            switch (context) {
                case 'cart':
                    formData = formDataForCart(element);
                    break;
                case 'product':
                    formData = formDataByElement(element);
                    break;
            }
        } catch (err) {
        }

        return formData;
    }
};

/**
 * Smart Payment Button Renderer Factory
 *
 * @param validContexts
 * @param request
 * @returns {SmartPaymentButtonRenderer}
 * @constructor
 */
export function SmartPaymentButtonRendererFactory(validContexts, request)
{
    const object = new SmartPaymentButtonRenderer(validContexts, request);

    Object.freeze(object);

    return object;
}
