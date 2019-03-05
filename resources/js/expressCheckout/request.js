/**
 * Class Request
 *
 * @type {Request}
 */
const Request = class Request
{
    // TODO Make the fields private if possible.
    constructor(ajaxUrl, action)
    {
        this.ajaxUrl = ajaxUrl;
        this.action = action;
    }

    formDataObject(formData)
    {
        const formDataEntries = [...formData.entries(), ['action', this.action]];
        const dataObject = formDataEntries.reduce((obj, [key, value]) => {
            obj[key] = value;
            return obj;
        }, {});

        return dataObject;
    }

    submit(formData)
    {
        const dataObject = this.formDataObject(formData);

        if (_.isEmpty(dataObject)) {
            return Promise.reject('No formData to send to the server.');
        }

        return new Promise((resolve, reject) => {
            jQuery.ajax({
                url: this.ajaxUrl,
                method: 'POST',
                data: dataObject,
                error: reject,
                success: resolve,
            });
        });
    }
};

/**
 * Request Factory
 *
 * @param ajaxUrl
 * @param action
 * @returns {Request}
 */
export function RequestFactory(ajaxUrl, action)
{
    if (!ajaxUrl || !action) {
        throw new Error('Invalid parameters when construct Request instance');
    }

    let object = new Request(ajaxUrl, action);
    Object.freeze(object);

    return object;
}
