=== PayPal Plus for WooCommerce ===
Contributors: inpsyde, biont
Tags: paypal, paypal plus, woocommerce, payment, zahlungsarten, rechnung, lastschrift, kreditkarte
Requires at least: 4.4
Tested up to: 4.7.4
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 
The official WordPress Plugin for WooCommerce - adds payment via PayPal, Direct debit, Credit card and Pay upon invoice to your WooCommerce Shop.
 
== Description ==

PayPal Plus für WooCommerce, das offizielle [PayPal Plus](https://www.paypal.com/de/webapps/mpp/paypal-plus) Plugin, bringt ohne viel Aufwand alle PayPal Plus Zahlungsmöglichkeiten in deinen WooCommerce Shop: PayPal, Kreditkarte, Lastschrift und Rechnung. 

Das Plugin lädt auf der Kasse-Seite ein von PayPal bereitgestelltes iFrame, das dem Käufer die PayPal Plus Zahlungsarten anbietet. Je nach Auswahl wird der Käufer nach Klick auf den Kaufen-Button zum PayPal-Formular weitergeleitet, oder es werden Formulare für die Eingabe der für die Bezahlung per Lastschrift, per Kreditkarte oder per Rechnung benötigten Daten angezeigt.

= Vorteile = 

* Mehr Zahlungsarten: PayPal Plus bringt die vier beliebtesten Zahlungsarten in deinen Shop: PayPal, Kreditkarte, Rechnung, Lastschrift.
* Einfachere Integration: alle Zahlungsarten mit nur einem Plugin.
* Mehr Sicherheit: PayPal Verkäuferschutz schützt dich vor Zahlungsausfällen bei allen Transaktionen.
* Erreiche mehr Kunden: mit oder ohne PayPal Konto.
* Nutzerfreundlicher und mobil optimierter Checkout: von PayPal entwickelt.
* Transaktionsbetrag wird dir sofort nach Kaufabschluss auf dein PayPal Konto gutgeschrieben.
* Transparente und gleiche Gebühren für alle Zahlungsarten ohhe Start- oder Monatsgebühren.
* [PCI Konformität](https://de.wikipedia.org/wiki/Payment_Card_Industry_Data_Security_Standard): unser Plugin lädt auf der Checkout-Seite ein von PayPal selbt gehostetes iFrame, in dem alle Transaktionsdaten eingegeben werden.

= Länder = 
Derzeit ist **PayPal Plus** für Händler mit Firmensitz in Deutschland einsetzbar. Internationale Transaktionen funktionieren dennoch. Für Käufer außerhalb Deutschlands werden die Zahlungsarten auf PayPal und Kreditkarte eingeschränkt.

= Mehr Informationen zu PayPal Plus = 

Du möchtest dich genauer über PayPal Plus informieren? Alle Details findest du auf den Seiten von [PayPal](https://www.paypal.com/de/webapps/mpp/paypal-plus).

= Support = 

You can find technical support for this plugin in the wordpress.org forum: https://wordpress.org/support/plugin/woo-paypalplus

Please read the FAQ (frequently asked questions) first and make sure you have installed the newest version of the plugin before contacting us.

**Made by [Inpsyde](https://inpsyde.com) &middot; We love WordPress**

== Installation ==

= Minimum Requirements =

* WooCommerce >= 3.0
Further minimum requirements are determined by the requirements for WooCommerce:
* PHP 5.6 or greater
* MySQL 5.6 or greater
* WordPress 4.4+
* WP Memory limit of 64 MB or greater (128 MB or higher is preferred)

= Automatic Installation =

This is the easiest way to install the PayPal Plus plugin.
1. Log into your WordPress installation.
2. Go to the menu item *plugins* and then to *install*.
3. Search for *PayPal Plus for WooCommerce*. In case several plugins are listed, check if *Inpsyde* is the plugin author.
4. Click *install now* and wait until WordPress reports the successful installation.
5. Activate the plugin. You can find the settings here: *WooCommerce => settings => checkout => PayPal Plus*.
**Attention:** You need WooCommerce 3.0 or higher to use PayPal Plus. Otherwise, the setting page of the plugin is not available. You will get a notification in your WordPress backend if you didn’t activate the correct WooCommerce version.
 
= Manual Installation =
In case the automatic installation doesn’t work, download the plugin from here via the *Download*-button. Unpack the archive and load the folder via FTP into the directory `wp-content\plugins` of your WordPress installation. Go to *plugins => installed plugins* and click *activate* on *PayPal Plus für WooCommerce*.

= Setting the plugin =
== Frequently Asked Questions ==
 
= I installed WooCommerce in a lower version. Nevertheless, can I use PayPal Plus for WooCommerce? =
 
No, the plugin is only compatible with WooCommerce versions >= 3.0. We advise to make an update. But don’t forget to make a backup of your installation before. 

= What do I have to think of when I use a PayPal account for several shops? =

It’s mandatory to assign a unique invoice prefix for each shop in the *PayPal Plus for WooCommerce* settings. Otherwise, PayPal won’t accept orders with the same invoice number.

= In which countries can I use PayPal Plus for WooCommerce? =

At the moment, you can use PayPal Plus for WooCommerce only in Germany.

= With PayPal Plus for WooCommerce, which payment methods can I integrate into my shop? =

With our plugin, you can integrate all those payment methods offered by PayPal: paying with your PayPal account, via direct debit, via credit card or paying via invoice.

= In meinem Shop wird die Zahlungsart Rechnung nicht angeboten. Was muss ich tun? = 

Dein PayPal Verkäufer-Konto muss von PayPal für den Kauf auf Rechnung freigeschaltet werden. Dies erfolgt erst nach Prüfung durch PayPal und kann ein paar Wochen dauern.
 
== Screenshots ==
 
1. This screenshot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screenshot
 
== Changelog ==
 
= 1.0 =
Initial Release
 
== Upgrade Notice ==
 
= 1.0 =
Dies ist die erste Version. Aktualisiere dein System, wenn Updates verfügbar sind um sicherzustellen, dass das Plugin einwandfrei funktioniert.