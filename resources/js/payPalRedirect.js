function plusRedirect (message)
{
  jQuery.blockUI({
    message,
    baseZ: 99999,
    overlayCSS: {
      background: '#fff',
      opacity: 0.6,
    },
    css: {
      padding: '20px',
      zindex: '9999999',
      textAlign: 'center',
      color: '#555',
      border: '3px solid #aaa',
      backgroundColor: '#fff',
      cursor: 'wait',
      lineHeight: '24px',
    },
  })

  if (typeof PAYPAL !== 'undefined') {
    PAYPAL.apps.PPP.doCheckout()
    return
  }

  setTimeout(function () {
    PAYPAL.apps.PPP.doCheckout()
  }, 500)
}

window.addEventListener('load', () => {
  plusRedirect(window.payPalRedirect.message)
})
