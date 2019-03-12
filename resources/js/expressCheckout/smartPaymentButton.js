import { formDataByElement, formDataForCart } from './form';
import { contextByElement } from './context';

const SINGLE_PRODUCT_BUTTON = 'paypalplus_ecs_single_product_button';
const CART_BUTTON = 'paypalplus_ecs_cart_button';

const TASK_CREATE_ORDER = 'createOrder';
const TASK_STORE_PAYMENT_DATA = 'storePaymentData';

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
     * @param buttonConfiguration
     * @param validContexts
     * @param request
     */
    constructor(buttonConfiguration, validContexts, request)
    {
        this.buttonConfiguration = buttonConfiguration;
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

        paypal.Button.render({
            ...this.buttonConfiguration,

            payment: () => {
                const formData = this.formDataByElement(element);
                formData.append('task', TASK_CREATE_ORDER);

                return this.request
                    .submit(formData)
                    .then(response => {
                        const orderId = 'orderID' in response.data ? response.data.orderID : '';

                        if (!orderId) {
                            // TODO Do something to inform user about the problem and close the flow.
                        }

                        return orderId;
                    });
            },

            onAuthorize: (data, actions) => {
                // TODO Ensure return_url exists.
                const formData = this.formDataByElement(element);

                formData.append('task', TASK_STORE_PAYMENT_DATA);
                formData.append('orderID', data.orderID);
                formData.append('payerID', data.payerID);
                formData.append('paymentID', data.paymentID);
                formData.append('paymentToken', data.paymentToken);

                return this.request
                    .submit(formData)
                    .then((response) => {
                        if (response.success) {
                            window.location.href = data.returnUrl;
                            // TODO Block the form UI? Prevent to do stuffs.
                        }

                        // TODO Show alert to the user
                    });
            },

            onCancel: () => {
                console.log('ON CANCEL', arguments);
                // TODO Update the mini cart if context is product. Unless we want to do a redirect.
                // TODO Redirect the user to the page set in the options.
            },

            onError: () => {
                console.log('ON ERROR', arguments);
                // TODO Redirect to cart and show customizable notice with message.
            },

        }, element);
    }

    /**
     * Retrieve context for FormData instance by the Given Element
     *
     * @param element
     * @returns {FormData}
     */
    // TODO Make it private if not possible move it as closure within the render function.
    formDataByElement(element)
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
 * @param buttonConfiguration
 * @param validContexts
 * @param request
 * @returns {SmartPaymentButtonRenderer}
 * @constructor
 */
export function SmartPaymentButtonRendererFactory(buttonConfiguration, validContexts, request)
{
    const object = new SmartPaymentButtonRenderer(buttonConfiguration, validContexts, request);

    Object.freeze(object);

    return object;
}
