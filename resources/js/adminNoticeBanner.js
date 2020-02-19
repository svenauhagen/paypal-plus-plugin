(function (jQuery, adminNoticeBannerData) {
  if (typeof adminNoticeBannerData === 'undefined') {
    return
  }
  if (!adminNoticeBannerData.urls) {
    console.warn('adminNoticeBannerData.urls not defined')
    return
  }
  if (!adminNoticeBannerData.urls.ajax) {
    console.warn('adminNoticeBannerData.urls.ajax not defined')
    return
  }
  if (!adminNoticeBannerData.urls.banner_settings_tab) {
    console.warn('adminNoticeBannerData.urls.banner_settings_tab not defined')
    return
  }
  window.addEventListener('load', () => {
    jQuery('#enable_pp_banner_feature').on('click', function (event) {
      event.preventDefault()
      event.stopImmediatePropagation()
      const nonce = event.currentTarget.dataset.nonce || ''
      jQuery.ajax({
        method: 'POST',
        data: {
          action: 'enable_paypal_banner_feature',
          enable_paypal_banner_feature_nonce: nonce
        },
        url: adminNoticeBannerData.urls.ajax,
        complete: () => {
          window.location = adminNoticeBannerData.urls.banner_settings_tab
        }
      })
    })
  })
})(jQuery, window.adminNoticeBannerData)
