import { bootstrapPayPalApp } from './plusFrameView'

(function (jQuery, infoToAjax) {
  window.addEventListener('load', () => {
    jQuery('#bannerLink').on('click', function (event) {
      event.preventDefault()
      jQuery.ajax({
        method: 'POST',
        data: {
          action: 'enable_banner'
        },
        url: infoToAjax.ajaxUrl
      })
      window.location = infoToAjax.urlBannerSettings
    })
  })
})(jQuery, window.infoToAjax)
