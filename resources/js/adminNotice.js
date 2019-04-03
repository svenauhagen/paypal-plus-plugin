const NOTICE_SELECTOR = 'inpsyde-notice'

const AdminNotice = class AdminNotice
{
  /**
   * Constructor
   * @param jquery
   * @param options
   */
  constructor (jquery, options)
  {
    this.jquery = jquery
    this.options = options
    this.dismiss = (notice) => {
      const noticeName = notice.dataset.id

      if (!noticeName) {
        return
      }

      this.jquery.ajax({
        url: this.options.ajaxUrl,
        method: 'POST',
        data: {
          action: this.options.action,
          _ajax_nonce: this.options.ajaxNonce,
          noticeId: noticeName,
        },
      })
    }
  }

  /**
   * Initialize Events
   */
  init ()
  {
    const notices = document.querySelectorAll(`.${NOTICE_SELECTOR}`)

    notices && notices.forEach((notice) => {
      const dismisser = notice.querySelector('.notice-dismiss')
      dismisser && dismisser.addEventListener('click', () => {
        this.dismiss(notice)
      })
    })
  }
}

/**
 * Admin Notice Factory
 * @param jquery
 * @param options
 * @returns {AdminNotice}
 * @constructor
 */
export function AdminNoticeFactory (jquery, options)
{
  const object = new AdminNotice(jquery, options)

  Object.freeze(object)

  return object
}
