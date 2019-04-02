import { envOptionsInitialize } from './payPalEnvOptions'
import { AdminNoticeFactory } from './adminNotice'

window.addEventListener('load', () => {
  envOptionsInitialize()

  if (!paypalplus || (!'adminNotice' in paypalplus)) {
    return
  }

  const adminNoticeOptions = paypalplus.adminNotice
  const adminNotice = AdminNoticeFactory(window.jQuery, adminNoticeOptions)
  adminNotice && adminNotice.init()
})
