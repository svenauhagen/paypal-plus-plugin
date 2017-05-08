=== PayPal Plus for WooCommerce ===
Contributors: inpsyde, biont
Tags: paypal, paypal plus, woocommerce, payment, zahlungsarten, rechnung, lastschrift, kreditkarte
Requires at least: 4.4
Tested up to: 4.7.4
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 
The official WordPress Plugin for WooCommerce - adds payment via PaypPal, Direct debit, Credit card and Pay upon invoice to your WooCommerce Shop.
 
== Description ==

PayPal Plus für WooCommerce, das offizielle PayPal Plus Plugin, bringt ohne viel Aufwand alle PayPal Plus Zahlungsmöglichkeiten in deinen WooCommerce Shop: PayPal, Kreditkarte, Lastschrift und Rechnung. Das Plugin lädt auf der Kasse-Seite ein von PayPal bereitgestelltes iFrame, das dem Käufer die PayPal Plus Zahlungsarten anbietet. Je nach Auswahl wird der Käufer nach Klick auf den Kaufen-Button zum PayPal-Formular weitergeleitet, oder es werden Formulare für die Eingabe der für die Bezahlung per Lastschrift, per Kreditkarte oder per Rechnung benötigten Daten angezeigt.

= Vorteile = 

* Mehr Zahlunsarten: PayPal Plus bringt die vier beliebtesten Zahlungsarten in deinen Shop: PayPal, Kreditkarte, Rechnung, Lastschrift.
* Einfachere Integration: alle Zahlungsarten mit nur einem Plugin.
* Mehr Sicherheit: PayPal Verkäuferschutz schützt dich vor Zahlungsausfällen bei Transaktionen.
* Erreiche mehr Kunden: mit oder ohne PayPal Konto.
* Nutzerfreundlicher und mobil optimierter Checkout: von PayPal entwickelt.
* Transaktionsbetrag wird dir sofort nach Kaufabschluss auf dein PayPal Konto gutgeschrieben.
* Transparente und gleiche Gebühren für alle Zahlungsarten ohhe Star- oder Monatsgebühren.
* [PCI Konformität](https://de.wikipedia.org/wiki/Payment_Card_Industry_Data_Security_Standard): unser Plugin lädt auf der Checkout-Seite ein von PayPal selbt gehostetes iFrame, in dem alle Transaktionsdaten eingegeben werden.

= Länder = 

Derzeit ist *PayPal Plus* für Händler mit Firmensitz in Deutschland einsetzbar. Internationale Transaktionen funktionieren dennoch. Für Käufer außerhalb Deutschlands werden die Zahlungsarten auf PayPal und Kreditkarte eingeschränkt.

= Mehr Informationen zu PayPal Plus = 

Du möchtest dich genauer über PayPal Plus informieren? Alle Details fidest du auf den Seiten von [PayPal](https://www.paypal.com/de/webapps/mpp/paypal-plus).

= Support = 

You can find technical support for this plugin in the wordpress.org forum: https://wordpress.org/support/plugin/woo-paypalplus

Please read the FAQ (frequently asked questions) first and make sure you have installed the newest version of the plugin before contacting us.

**Made by [Inpsyde](https://inpsyde.com) &middot; We love WordPress**

== Installation ==

= Minimum Requirements = 

* WooCommerce >= 3.0

Weitere Mindestanforderungen richten sich nach den Voraussetzungen für WooCommerce:

* PHP 5.6 or greater
* MySQL 5.6 or greater
* WordPress 4.4+
* WP Memory limit of 64 MB or greater (128 MB or higher is preferred)

Darüber hinaus benötigst du ein PaypPal Gäscheftskonto Konto, das für PayPal Plus freigeschaltet ist. [Den Antrag stellst du hier bei PayPal]( https://www.paypal.com/de/webapps/mpp/paypal-plus).

= Automatic Installation = 

Dies ist der einfachste Weg, das PayPal Plus Plugin zu installieren. 
1. Logge dich in deine WordPress Installation ein.
2. Gehe zum Menüpunkt *Plugins* und dort auf *Installieren*.
3. Suche nach *PayPal Plus for WooCommerce*. Solltest du mehrere Plugins aufgelistet bekommen, achte darauf, dass der Pluginautor *Inpsyde* ist.
4. Klicke auf *Jetzt installieren* und warte bis WordPress die erfolgreiche Installation meldet. 
5. Aktiviere anschließend das Plugin. Die Einstellungen findest du unter *WooCommerce => Einstellungen => Kasse => PayPal Plus*.

**Achtung:** du benötigst WooCommerce 3.0 oder höher, um PayPal Plus nutzen zu können. Andernfalls ist die Settingsseite des Plugins nicht vorhanden. Eine Meldung in deinem WordPress Backend wird dich darauf hinweisen, sollte nicht die richtige WooCommerce Version aktiviert sein.
 
= Manual Installation = 

Falls die automatische Installation bei dir nicht funktioniert, lade das Plugin hier über den *Herunterladen*-Button herunter. Entpacke das Archiv und lade den Ordner per FTP in das Verzeichnis `wp-content\plugins` deiner WordPress Installation hoch. Gehe zu *Plugins => Installierte Plugins* und klicke bei *PayPal Plus für WooCommerce* auf *Aktivieren*.

= Einrichtung des Plugins = 

== Frequently Asked Questions ==
 
= Ich habe WooCommerce in einer kleineren Version installiert. Kann ich PayPal Plus für WooCommerce dennoch nutzen? =
 
Nein, das Plugin ist nur mit WooCommerce Versionen >= 3.0 kompatibel. Wir raten dir dringend ein Update durchzuführen. Denke aber daran, vorher ein Backup deiner Installation anzulegen. 

= Was muss ich beachten, wenn ich ein PayPal Konto für mehrere Shops verwende? =

Du musst zwingend in den Einstellungen von *PayPal Plus für WooCommerce* pro Shop einen eindeutigen Rechnungspräfix vergeben. Andernfalls wird PayPal keine Bestellungen mit identischer Rechnungsnummer akzeptieren.

= Für welche Länder kann ich PayPal Plus für WooCommerce nutzen? = 

Du kannst das Plugin derzeit für Deutschland einsetzen. 

= Welche Zahlungsarten kann ich mit PayPal Plus für WooCommerce in meinem Shop einbinden? = 

Du kannst mit unserem Plugin alle von PayPal Plus angebotenen Zahlungsarten einrichten: bezahlen mit deinem PayPal-Konto, per Lastschrift, per Kreditkarte und bezahlen per Rechnung.

= In meinem Shop wird die Zahlungsart Rechnung nicht angeboten. Was muss ich tun? = 

Dein PayPal Verkäufer-Konto muss von PayPal für den Kauf auf Rechnung freigeschaltet werden. Dies erfolgt erst nach Prüfung durch PayPal und kann ein paar Wochen dauern.
 
== Screenshots ==
 
1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot
 
== Changelog ==
 
= 1.0 =

Initial Release
 
== Upgrade Notice ==
 
= 1.0 =

Dies ist die erste Version. Aktualisiere dein System, wenn Updates verfügbar sind um sicherzustellen, dass das Plugin einwandfrei funktioniert.
