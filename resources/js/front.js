import { bootstrapPayPalApp } from './plusFrameView';

(function (jQuery, _) {

    window.addEventListener('load', () => {
        // Isn't possible to listen on a jQuery event by using `addEventListener`
        jQuery(document.body).on('updated_checkout', () => {
            bootstrapPayPalApp();
        });
    });

})(jQuery, _);

